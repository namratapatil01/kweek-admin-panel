const admin = require ("firebase-admin");
const serviceAccount = require('./credentials.json'); // Adjust the path if needed
const projectId = serviceAccount.project_id;

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: "https://${projectId}-default-rtdb.firebaseio.com" // Replace with your database URL
});

const firestore = admin.firestore();
/*
** Schedule ride orders to vendors and drivers
*/
async function scheduleRide() {

    var driverNearByData = await getDriverNearByData();
    var minimumDepositToRideAccept = 0;
    var ownerMinimumDepositToRideAccept = 0;
    var orderAcceptRejectDuration = 0;
    var kDistanceRadiusForDispatchInMiles = 50;

    if(driverNearByData !== undefined){
        if(driverNearByData.minimumDepositToRideAccept !== undefined){
            minimumDepositToRideAccept = parseInt(driverNearByData.minimumDepositToRideAccept);
        }
        if(driverNearByData.ownerMinimumDepositToRideAccept !== undefined){
            ownerMinimumDepositToRideAccept = parseInt(driverNearByData.ownerMinimumDepositToRideAccept);
        }
        if(driverNearByData.driverOrderAcceptRejectDuration !== undefined){
            orderAcceptRejectDuration = parseInt(driverNearByData.driverOrderAcceptRejectDuration);
        }
        if(driverNearByData.driverRadios !== undefined){
            kDistanceRadiusForDispatchInMiles = parseInt(driverNearByData.driverRadios);
        }
    }

    console.log('minimumDepositToRideAccept',minimumDepositToRideAccept);
    console.log('ownerMinimumDepositToRideAccept',ownerMinimumDepositToRideAccept);
    console.log('orderAcceptRejectDuration',orderAcceptRejectDuration);
    console.log('currentTimestamp',admin.firestore.Timestamp.now());
    
	const querySnapshot = await firestore.collection('rides').where('scheduleDateTime', '<=', admin.firestore.Timestamp.now()).get();
    
    if(querySnapshot.size > 0) {

        querySnapshot.forEach(function(doc) {

            const orderData = doc.data();

            if (!orderData) {
                console.log("No order data");
                return;
            }

            if (orderData.status === "Order Cancelled") {
                console.log("Order #" + orderData.id + " was cancelled.")
                return null;
            }

            if (orderData.status === "Order Placed" || orderData.status === "Order Accepted" || orderData.status === "Driver Rejected") {
            // the vendor accepted the order, so we need to find an available driver
            console.log("Finding a driver for order #" + orderData.id)

            const rejectedByDrivers = orderData.rejectedByDrivers ? orderData.rejectedByDrivers : []

            return firestore
                .collection("users")
                .where('role', '==', "driver")
                .where('serviceType', '==', "cab-service")
                .where('sectionId', '==', orderData.sectionId)
                .where('vehicleId', '==', orderData.vehicleId)
                .where('isActive', '==', true)
                .get()
                .then(snapshot => {
                    var found = false
                    snapshot.forEach(async doc => {
                        if (!found) {
                            // We simply assign the first available driver who's within a reasonable distance from the vendor and who did not reject the order and who is not delivering already
                            const driver = doc.data();

                            // Check minimum wallet for individual driver & owner
                            let walletAmountToCheck = driver.wallet_amount || 0;
                            if (driver.ownerId !== undefined && driver.ownerId !== null && driver.ownerId.toString().trim() !== "") {
                                const ownerDoc = await firestore.collection("users").doc(driver.ownerId).get();
                                if (ownerDoc.exists) {
                                    const ownerData = ownerDoc.data();
                                    walletAmountToCheck = ownerData.wallet_amount || 0;
                                }
                                //Apply minimum wallet condition for owner
                                if (walletAmountToCheck < ownerMinimumDepositToRideAccept) {
                                    console.log(`Driver ${driver.id} skipped (wallet too low: ${walletAmountToCheck})`);
                                    return;
                                }
                            }else{
                                //Apply minimum wallet condition for individual driver
                                if (walletAmountToCheck < minimumDepositToRideAccept) {
                                    console.log(`Driver ${driver.id} skipped (wallet too low: ${walletAmountToCheck})`);
                                    return;
                                }
                            }
                            
                            console.log(`Driver Ride Type: ${driver.rideType}`, ` :: Driver ID: ${driver.id}`, ` :: Driver Email: ${driver.email}`);

                            if(driver.rideType === "both" || driver.rideType === orderData.rideType){

                                if (driver.location
                                && rejectedByDrivers.indexOf(driver.id) === -1
                                && (driver.inProgressOrderID === undefined || driver.inProgressOrderID === null || (Array.isArray(driver.inProgressOrderID) && driver.inProgressOrderID.length === 0) || driver.inProgressOrderID === "")
                                && (driver.ordercabRequestData === undefined || driver.ordercabRequestData === null)) {

                                /*const vendor = orderData.vendor*/
                                if (orderData.sourceLocation) {
                                    // const distance = distanceRadiusride(driver.location.latitude, driver.location.longitude, orderData.sourceLocation.latitude, orderData.sourceLocation.longitude)

                                    // console.log(`distance: ${distance}`, `kDistanceRadiusForDispatchInMiles: ${kDistanceRadiusForDispatchInMiles}`);

                                    if (driver.zoneId && driver.zoneId !== null && driver.zoneId !== '') {
                                        const driverZoneId = driver.zoneId;

                                        // get zone data from Firestore
                                        const zoneDoc = await firestore.collection('zone').doc(driverZoneId).get();

                                        if (!zoneDoc.exists) {
                                            console.log(`Zone ${driverZoneId} not found, skipping driver ${driver.id}`);
                                            return;
                                        }

                                        const zoneData = zoneDoc.data();
                                        const vertices_x = zoneData.area.map(p => p.longitude);
                                        const vertices_y = zoneData.area.map(p => p.latitude);

                                        // check if driver's current location is inside their zone
                                        const isDriverInsideZone = is_in_polygon(vertices_x, vertices_y, driver.location.longitude, driver.location.latitude);

                                        // check if order source location is also inside this zone
                                        const isOrderInsideZone = is_in_polygon(vertices_x, vertices_y, orderData.sourceLocation.longitude, orderData.sourceLocation.latitude);

                                        if (!isDriverInsideZone || !isOrderInsideZone) {
                                            console.log(`Driver ${driver.id} skipped (zone mismatch or out of zone)`);
                                            return;
                                        }
                                    }

                                    const distance = distanceRadiusride(
                                        driver.location.latitude,
                                        driver.location.longitude,
                                        orderData.sourceLocation.latitude,
                                        orderData.sourceLocation.longitude
                                    );

                                    console.log(`distance: ${distance}`, `kDistanceRadiusForDispatchInMiles: ${kDistanceRadiusForDispatchInMiles}`);


                                    if (distance < kDistanceRadiusForDispatchInMiles) {

                                            found = true

                                            //set data for notification
                                            if(driver.fcmToken !== "" && driver.fcmToken !== null){
                                                var seconds = orderAcceptRejectDuration % 60;
                                                var time = Math.floor(orderAcceptRejectDuration / 60) + ":" + (seconds < 10 ? '0' + seconds : seconds);
                                                var message = {
                                                    notification: {
                                                        title: 'New ride request received',
                                                        body: 'You have a new ride request, please accept the ride request in ' + time + ' mins'
                                                    },
                                                    token: driver.fcmToken
                                                };
                                                //send notification to driver
                                                admin.messaging().send(message)
                                                .then((response) => {
                                                    console.log('Notification Success:', response);
                                                    return null;
                                                })
                                                .catch((error) => {
                                                    console.log('Notification Error:', error);
                                                    return null;
                                                });
                                            }

                                            // We update the order status
                                            firestore.collection('rides').doc(orderData.id).update({
                                                status: "Driver Pending"
                                            }).then(async function (result) {
                                                // After update the order status get new updated status
                                                 firestore.collection("rides").doc(orderData.id).get().then((querySnapshot) => {	
                                                    var newOrderData = querySnapshot.data();
                                                    // Check if driver is accepting the order within defined time or not
                                                    if(orderAcceptRejectDuration > 0 && newOrderData.status === "Driver Pending"){
                                                        setTimeout(function(){ 
                                                            // Re-check order status after time limit exceed before find out other driver
                                                            firestore.collection("rides").doc(orderData.id).get().then((querySnapshot) => {
                                                                var newOrderData2 = querySnapshot.data();
                                                                // If order status is driver pending then and only we will find new driver and current driver will add to rejected list
                                                                if(newOrderData2.status === "Driver Pending"){
                                                                    firestore.collection('users').doc(driver.id).update({
                                                                        'ordercabRequestData': null,
                                                                    });
                                                                    // Current driver is adding to rejected list so they will not receive order again and update status to find new driver
                                                                    rejectedByDrivers.push(driver.id);
                                                                    firestore.collection('rides').doc(orderData.id).update({
                                                                        'status': 'Order Accepted',
                                                                        'rejectedByDrivers': rejectedByDrivers
                                                                    })
                                                                    console.log("Order not accepted by driver #" + driver.id + " for order #" + orderData.id + " within " + orderAcceptRejectDuration + " seconds, searching for next driver.")
                                                                    return null
                                                                }
                                                                return null
                                                            })
                                                            .catch(error => {
                                                                console.log(error)
                                                            })
                                                        },orderAcceptRejectDuration*1000);
                                                    }
                                                    return null
                                                })
                                                .catch(error => {
                                                    console.log(error)
                                                })
                                                return null
                                            })
                                            .catch(error => {
                                                console.log(error)
                                            })

                                            // We send the order to the driver, by appending ordercabRequestData to the driver's user model in the users table
                                            firestore.collection('users').doc(driver.id).update({
                                                ordercabRequestData: orderData,
                                            });
                                            console.log("Order sent to driver #" + driver.id + " for order #" + orderData.id + " with distance at " + distance)
                                        }
                                    }
                                }
                            }
                            
                        }
                    })
                    if (!found) {
                        // We did not find an available driver
                        console.log("Could not find an available driver for order #" + orderData.id)
                    }
                    return null
                })
                .catch(error => {
                    console.log(error)
                })
            }
        });
    }else{
        console.log("No results found");
    }
    return null
};

const distanceRadiusride = (lat1, lon1, lat2, lon2) => {
	if ((lat1 === lat2) && (lon1 === lon2)) {
		return 0;
	}
	else {
		var radlat1 = Math.PI * lat1/180;
		var radlat2 = Math.PI * lat2/180;
		var theta = lon1-lon2;
		var radtheta = Math.PI * theta/180;
		var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
		if (dist > 1) {
			dist = 1;
		}
		dist = Math.acos(dist);
		dist = dist * 180/Math.PI;
		dist = dist * 60 * 1.1515;
		return dist;
	}
}

async function getDriverNearByData(){
    var snapshot =  await firestore.collection("settings").doc('DriverNearBy').get();
    return snapshot.data();
}

function is_in_polygon(vertx, verty, testx, testy) {
    let c = false;
    let j = vertx.length - 1;
    for (let i = 0; i < vertx.length; i++) {
        if (
            (verty[i] > testy) != (verty[j] > testy) &&
            testx < ((vertx[j] - vertx[i]) * (testy - verty[i])) / (verty[j] - verty[i]) + vertx[i]
        ) {
            c = !c;
        }
        j = i;
    }
    return c;
}

scheduleRide();