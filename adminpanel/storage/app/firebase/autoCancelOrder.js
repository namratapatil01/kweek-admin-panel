const admin = require('firebase-admin');
const serviceAccount = require('./credentials.json');
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: `https://${projectId}-default-rtdb.firebaseio.com`
});

const firestore = admin.firestore();
const messaging = admin.messaging();

async function autoCancelOrderCron() {
    try {
        const currentTimestamp = admin.firestore.Timestamp.now();

        const timingSnapshot = await firestore.collection('settings').doc("DriverNearBy").get();
        if (!timingSnapshot.exists) {
            console.log("No notification timing settings found.");
            return;
        }

        const timingData = timingSnapshot.data();
        const orderAutoCancelDuration = timingData.orderAutoCancelDuration || 5;
        const batch = firestore.batch();
        const refundPromises = [];

        const orderPlacedSnapshot = await firestore.collection('vendor_orders').where('status', '==', 'Order Placed').get();
        for (const doc of orderPlacedSnapshot.docs) {
            const order=doc.data();
            /* await firestore.collection('sections').doc(order.vendor.section_id).get().then(async function(snapshot) {
                service_type=snapshot.data().serviceTypeFlag;
                if(service_type=='ecommerce-service') {
                    continue;
                }
            }) */
            const sectionDoc = await firestore.collection('sections').doc(order.vendor.section_id).get();
            const service_type = sectionDoc.data()?.serviceTypeFlag;

            if (service_type === 'ecommerce-service') {
                continue; 
            }
            const createdAt = order.createdAt;
            console.log('order scheduleTime--->'+order.scheduleTime)
          
            const scheduleTime = order.scheduleTime?.toDate?.();
            const baseTime = scheduleTime || createdAt.toDate();
            const expirationTime = new Date(baseTime.getTime() + orderAutoCancelDuration * 60000);
            const expirationTimestamp = admin.firestore.Timestamp.fromDate(expirationTime);

            if (currentTimestamp.toMillis() > expirationTimestamp.toMillis()) {
                console.log(`Cancelling order: ${doc.id}`);
                batch.update(doc.ref, { status: "Order Cancelled" });
                refundPromises.push(getCustomerRefund({ ...order, id: doc.id }));
            }
        }

        await batch.commit();
        await Promise.all(refundPromises);

        const ordersSnapshot = await firestore
            .collection('vendor_orders')
            .where('status', '==', 'Order Accepted')
            .where('orderAutoCancelAt', '<', currentTimestamp)
            .get();

        for (const doc of ordersSnapshot.docs) {
            const orderData = doc.data();
            if(!orderData) continue;
            /* await firestore.collection('sections').doc(order.vendor.section_id).get().then(async function(snapshot) {
                service_type=snapshot.data().serviceTypeFlag;
                if(service_type=='ecommerce-service') {
                    continue;
                }
            }) */

            const sectionId = orderData?.vendor?.section_id;
            if (!sectionId || typeof sectionId !== "string") {
                console.error("Invalid section_id:", sectionId);
                return;
            }

            const sectionDoc = await firestore.collection('sections').doc(sectionId).get();
            const service_type = sectionDoc.data()?.serviceTypeFlag;

            if (service_type === 'ecommerce-service') {
                continue; 
            }
            console.log(`Cancelling accepted order: ${orderData.id}`);
            await doc.ref.update({ status: "Order Cancelled" });
            await getRefund({ ...orderData, id: doc.id });
        }

    } catch (error) {
        console.error("Error in autoCancelOrderCron:", error);
    }
}

async function getRefund(orderData) {
    try {
        const vendorId = orderData.vendor?.author;
        const customerId = orderData.author?.id;
        let vendorAmount = 0, deliveryCharge = 0, tipAmount = 0, customerAmount = 0;
        let vendorFcm = '', customerFcm = '';
        let vendorTaxAmount=0;
        let vendorBaseAmount=0;
        const walletSnapshot = await firestore.collection('wallet')
            .where('user_id', '==', vendorId)
            .where('order_id', '==', orderData.id)
            .where('isTopUp', '==', true)
            .get();

        for (const doc of walletSnapshot.docs) {
            const data=doc.data();
            if(data.payment_method=='tax') {
                vendorTaxAmount=parseFloat(data.amount);
            } else {
                vendorBaseAmount=parseFloat(data.amount);
            }
            vendorAmount += parseFloat(data.amount || 0);
        }

        if (vendorAmount) {
            const vendorDoc = await firestore.collection('users').doc(vendorId).get();
            if (vendorDoc.exists) {
                const vendorData = vendorDoc.data();
                vendorFcm = vendorData.fcmToken || '';
                const vendorWallet = parseFloat(vendorData.wallet_amount || 0);
                await vendorDoc.ref.update({ wallet_amount: vendorWallet - vendorAmount });
            }

            const walletId = firestore.collection("tmp").doc().id;
            await firestore.collection('wallet').doc(walletId).set({
                amount: vendorBaseAmount,
                date: admin.firestore.Timestamp.now(),
                id: walletId,
                isTopUp: false,
                order_id: orderData.id,
                payment_method: "Wallet",
                payment_status: 'success',
                user_id: vendorId,
                transactionUser: 'vendor',
                note: 'Order amount refunded to customer'
            });
            const walletTaxId = firestore.collection("tmp").doc().id;
             await firestore.collection('wallet').doc(walletTaxId).set({
                amount: vendorTaxAmount,
                date: admin.firestore.Timestamp.now(),
                id: walletTaxId,
                isTopUp: false,
                order_id: orderData.id,
                payment_method: "tax",
                payment_status: 'success',
                user_id: vendorId,
                transactionUser: 'vendor',
                note: 'Order tax refunded to customer'
            });
        }

        if (orderData.payment_method != 'cod') {
            getCustomerRefund(orderData);
        } else {
            const customerDoc = await firestore.collection('users').doc(customerId).get();
            if (customerDoc.exists) {
                customerFcm = customerDoc.data().fcmToken || '';
            }
        }
        await removeRedeemCashback(orderData);
        await sendNotification(vendorFcm, customerFcm, orderData, 'no driver found');

    } catch (error) {
        console.error("Error in getRefund:", error);
    }
}

async function getCustomerRefund(orderData) {
    try {
        const customerId=orderData.author.id;
        const vendorId = orderData.vendor?.author;
        let totalPrice = 0;
        let customerFcm = '';
        const products = orderData.products || [];

        for (const product of products) {
            const quantity = parseInt(product.quantity || 0);
            const price = parseFloat((product.discountPrice && parseFloat(product.discountPrice) != 0) ? product.discountPrice : product.price || 0);
            const extras = parseFloat(product.extras_price  || 0);
            totalPrice += (price + extras) * quantity;
        }
        if (!isNaN(orderData.discount)) totalPrice -= parseFloat(orderData.discount);
        if (orderData.specialDiscount?.special_discount) {
            totalPrice -= parseFloat(orderData.specialDiscount.special_discount);
        }
        var totalTax=0;
        if (orderData.taxSetting?.length) {
            for (const tax of orderData.taxSetting) {
                const taxVal = tax.type === 'percentage'
                    ? (parseFloat(tax.tax) * totalPrice) / 100
                    : parseFloat(tax.tax);
                totalTax+=taxVal;
            }
        }
        totalPrice+=parseFloat(totalTax);
        totalPrice += parseFloat(orderData.deliveryCharge || 0);
        totalPrice+=parseFloat(orderData.tip_amount||0);
        const customerDoc = await firestore.collection('users').doc(customerId).get();
        if (customerDoc.exists) {
            const customerData = customerDoc.data();
            customerFcm = customerData.fcmToken || '';
               if (orderData.payment_method != 'cod') {
                const customerWallet = parseFloat(customerData.wallet_amount || 0);
                await customerDoc.ref.update({ wallet_amount: customerWallet + totalPrice });

                const walletId = firestore.collection("tmp").doc().id;
                await firestore.collection('wallet').doc(walletId).set({
                    amount: totalPrice,
                    date: admin.firestore.Timestamp.now(),
                    id: walletId,
                    isTopUp: true,
                    order_id: orderData.id,
                    payment_method: "Wallet",
                    payment_status: 'success',
                    user_id: customerId,
                    transactionUser: 'customer',
                    note: 'Order amount refund'
                });
            }
        }

 
        const vendorDoc = await firestore.collection('users').doc(vendorId).get();
        if(vendorDoc.exists) {
            const vendorData=vendorDoc.data();
            vendorFcm=vendorData.fcmToken||'';
        }
        await removeRedeemCashback(orderData);
        await sendNotification(vendorFcm, customerFcm, orderData, 'not accepted');

    } catch (error) {
        console.error("Error in getCustomerRefund:", error);
    }
}

async function sendNotification(vendorFcm,customerFcm,orderData,type) {
    const shortOrderId = orderData.id.slice(-10);
    const vendorMessage = {
        title: 'Order Cancelled',
        body: type === 'no driver found'
            ? `Order #${shortOrderId} has been cancelled due to no driver available.`
            : `Order #${shortOrderId} has been cancelled due to delay in accepting the order.`
    };

    const customerMessage = {
        title: 'Order Cancelled',
        body: type === 'no driver found'
            ? `Order #${shortOrderId} has been cancelled due to no driver available.`
            : `Order #${shortOrderId} has been cancelled due to restaurant did not accept the order.`
    };

    try {
        if (vendorFcm) {
            await messaging.send({ notification: vendorMessage, token: vendorFcm });
            console.log("Notification sent to vendor");
        }

        if (customerFcm) {
            await messaging.send({ notification: customerMessage, token: customerFcm });
            console.log("Notification sent to customer");
        }

    } catch (error) {
        console.error("Error sending notifications:", error);
    }
}
async function removeRedeemCashback(orderData) {
    const snapshot = await firestore.collection('cashback_redeem')
        .where('orderId', '==', orderData.id)
        .limit(1)
        .get();
    if (!snapshot.empty) {
        const docId = snapshot.docs[0].id;
        await firestore.collection('cashback_redeem').doc(docId).delete();
    }
}
autoCancelOrderCron();
