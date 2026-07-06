

    
    var database = kweekFirestore();
    var geoFirestore = new GeoFirestore(database);
    var autoAprroveVendor = database.collection('settings').doc("vendor");
    var photo = "";
    var vendorOwnerId = "";
    var vendorOwnerOnline = false;
    var photocount = 0;
    var restaurnt_photos = [];
    var ownerphoto = '';
    var id = "1";
    var database = kweekFirestore();
    var ref = database.collection('users').where("id", "==", id);
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var photo = "";
    var fileName = "";
    var oldImageFile = '';
    var storageRef = kweekStorage().ref('images');

    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })
    var currency = database.collection('settings');

    var currentCurrency = '';
    var currencyAtRight = false;
    var decimal_degits = 0;

    var refCurrency = database.collection('currencies').where('isActive', '==', true);
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        currencyAtRight = currencyData.symbolAtRight;

        if (currencyData.decimal_degits) {
            decimal_degits = currencyData.decimal_degits;
        }
    });

    var createdAt = kweekFirestore.FieldValue.serverTimestamp();

    jQuery("#data-table_processing").show();

    ref.get().then(async function (snapshots) {
        jQuery("#data-table_processing").hide();
        if (!snapshots.empty) {
            var user = snapshots.docs[0].data();
            $(".user_first_name").val(user.firstName);
            $(".user_last_name").val(user.lastName);
            $(".user_email").val(shortEmail(user.email));
            $(".vendor_latitude").val(user.location.latitude);
            $(".vendor_longitude").val(user.location.longitude);
           
            $(".user_phone").val(user.phoneNumber);
            $(".user_phone").attr('disabled',true);
            if (user.adminCommission) { 
                $("#commission_type").val(user.adminCommission.type);
                $(".commission_fix").val(user.adminCommission.commission);
            }
            if(user.hasOwnProperty('subscriptionPlanId')&&user.subscriptionPlanId!=null) {
                $('#subscriptionPlanId').val(user.subscriptionPlanId);
            }
            if (user.subscriptionExpiryDate) {
                const expiresAt = new Date(user.subscriptionExpiryDate.toDate());
                const [year, month, day] = expiresAt.toISOString().slice(0, 10).split("-");
                const formattedDate = `${year}-${month}-${day}`;
                $('#change_expiry_date').val(formattedDate);
            }

            photo = user.profilePictureURL;
            if (photo != '') {
                oldImageFile = user.profilePictureURL;
                $(".uploaded_image_owner").append('<img class="rounded" style="width:50px" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');
            } else {

                $(".uploaded_image_owner").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
            }

            if (user.active) {
                $(".user_active").prop('checked', true);
            }

            var wallet = 0;

            if (user.wallet_amount) {
                wallet = user.wallet_amount;
            }
            if (currencyAtRight) {
                wallet = parseFloat(wallet).toFixed(decimal_degits) + "" + currentCurrency;
            } else {
                wallet = currentCurrency + "" + parseFloat(wallet).toFixed(decimal_degits);
            }
            if (user.userBankDetails) {
                if (user.userBankDetails.bankName != undefined) {
                    $("#bankName").val(user.userBankDetails.bankName);
                }
                if (user.userBankDetails.branchName != undefined) {
                    $("#branchName").val(user.userBankDetails.branchName);
                }
                if (user.userBankDetails.holderName != undefined) {
                    $("#holderName").val(user.userBankDetails.holderName);
                }
                if (user.userBankDetails.accountNumber != undefined) {
                    $("#accountNumber").val(user.userBankDetails.accountNumber);
                }
                if (user.userBankDetails.otherDetails != undefined) {
                    $("#otherDetails").val(user.userBankDetails.otherDetails);
                }
            }
            $("#wallet_amount").text(wallet);



            jQuery("#data-table_processing").hide();
        }
    })
    $(".edit-form-btn").click(function () {
        $(".error_top").hide();
        var latitude = parseFloat($(".vendor_latitude").val());
        var longitude = parseFloat($(".vendor_longitude").val());

        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var userPhone = $(".user_phone").val();
        var active = $(".user_active").is(":checked");
        var location = { 'latitude': latitude, 'longitude': longitude };
        var user_name = userFirstName + " " + userLastName;
        var subscriptionPlanId=$('#subscriptionPlanId').val();
        var change_expiry_date = $('#change_expiry_date').val();


        if (change_expiry_date != '' && change_expiry_date != null) {
            var subscriptionPlanExpiryDate = kweekFirestore.Timestamp.fromDate(new Date(
                change_expiry_date));
        } else {
            var subscriptionPlanExpiryDate=null;

        }


        var commissionType = $("#commission_type").val();
        var fixCommission = $(".commission_fix").val();
        const adminCommission = {
                "type": commissionType,
                "commission": parseInt(fixCommission),
                "enable": true
        };




        if (userFirstName == '') {

            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>1</p>");
            window.scrollTo(0, 0);
        } else if (email == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>1</p>");
            window.scrollTo(0, 0);
        } else if (password == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>1</p>");
            window.scrollTo(0, 0);
        } else if (userPhone == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>1</p>");
            window.scrollTo(0, 0);

        } else {
            var bankName = $("#bankName").val();
            var branchName = $("#branchName").val();
            var holderName = $("#holderName").val();
            var accountNumber = $("#accountNumber").val();
            var otherDetails = $("#otherDetails").val();
            var userBankDetails = {
                'bankName': bankName,
                'branchName': branchName,
                'holderName': holderName,
                'accountNumber': accountNumber,
                'accountNumber': accountNumber,
                'otherDetails': otherDetails,
            };
            storeImageData().then(IMG => {
                updateSubscriptionHistory(id, subscriptionPlanId,
                subscriptionPlanExpiryDate).then(
                    async function() {
                await database.collection('users').doc(id).update({

                    'firstName': userFirstName, 
                    'lastName': userLastName,
                    'email': email,
                    'phoneNumber': userPhone,
                    'profilePictureURL': IMG,
                    'active': active,
                    'isActive': active,
                    'userBankDetails': userBankDetails,
                    'adminCommission':adminCommission,
                    'subscriptionExpiryDate': subscriptionPlanExpiryDate,
                   

                }).then(function (result) {
                    geoFirestore.collection('providers_services')
                    .where('author', '==', id)
                    .get() // Retrieve documents matching the query
                    .then(querySnapshot => {
                        querySnapshot.forEach(doc => {
                            // For each matching document, update it
                            geoFirestore.collection('providers_services').doc(doc.id).update({
                                'subscriptionExpiryDate': subscriptionPlanExpiryDate
                            });
                        });
                    }).catch(error => {
                        console.error('Error updating provider services:', error);
                    });
                });
                    
                    setTimeout(function () {
                        window.location.href = '1';
                    }, 5000);
                })
            }).catch(err => {
                jQuery("#data-table_processing").hide();
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>" + err + "</p>");
                window.scrollTo(0, 0);
            });
        }
    });


    async function updateSubscriptionHistory(id, subscriptionPlanId, subscriptionPlanExpiryDate) {
        try {
            
            const lastSubscriptionHistory = await database.collection('subscription_history').where('user_id','==',id).orderBy('createdAt','desc').get();
            if(lastSubscriptionHistory && lastSubscriptionHistory.docs && lastSubscriptionHistory.docs.length > 0){
                const subscriptionData = lastSubscriptionHistory.docs[0].data();
                database.collection('subscription_history').doc(subscriptionData.id).update({
                    'expiry_date': subscriptionPlanExpiryDate,
                });
            }
            
        } catch (error) {
            console.error("Error updating subscription history:", error);
        }
    }


    var storageRef = kweekStorage().ref('images');
    var storage = kweekStorage();
    function handleFileSelectowner(evt) {
        var f = evt.target.files[0];
        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {

                var filePayload = e.target.result;
                var hash = CryptoJS.SHA256(Math.random() + CryptoJS.SHA256(filePayload));
                var val = f.name;
                var ext = val.split('.')[1];
                var docName = val.split('fakepath')[1];
                var filename = (f.name).replace(/C:\\fakepath\\/i, '')

                var timestamp = Number(new Date());
                var filename = filename.split('.')[0] + "_" + timestamp + '.' + ext;
                photo = filePayload;
                fileName = filename;
                $(".uploaded_image_owner").html('<img class="rounded" style="width:50px" src="' + photo + '"  onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');
                $(".uploaded_image_owner").show();

            };
        })(f);
        reader.readAsDataURL(f);
    }
    async function storeImageData() {
        var newPhoto = '';
        try {
            if (oldImageFile != "" && photo != oldImageFile) {
                try {
                            await storage.storage.refFromURL(oldImageFile).delete();
                        } catch (error) {
                            console.log("ERR File delete ===", error);
                        }
}
            if (photo != oldImageFile) {
                photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                var downloadURL = await uploadTask.ref.getDownloadURL();
                newPhoto = downloadURL;
                photo = downloadURL;

            } else {
                newPhoto = photo;
            }
        } catch (error) {
            console.log("ERR ===", error);
        }
        return newPhoto;
    }

    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)
        ) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    function chkAlphabets3(event, msg) {
        if (!((event.which >= 48 && event.which <= 57) || (event.which >= 97 && event.which <= 122))) {
            document.getElementById(msg).innerHTML = "Special characters not accepted ";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

