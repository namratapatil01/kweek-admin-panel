

            var section_id = getCookie('section_id') || '';

            var database = kweekFirestore();
            var geoFirestore = new GeoFirestore(database);
            var autoAprroveVendor = database.collection('settings').doc("vendor");
            var photo = "";
            var vendorOwnerId = "";
            var vendorOwnerOnline = false;
            var photocount = 0;
            var restaurnt_photos = [];
            var ownerphoto = '';
            var photo = "";
            var fileName = "";
            var storageRef = kweekStorage().ref('images');

            var createdAt = kweekFirestore.FieldValue.serverTimestamp();
            var adminCommission = '';
            
            $(document).ready(async function () {

                let businessModelRef = await database.collection('settings').doc("vendor").get();
                businessModelData = businessModelRef.data();
                if(businessModelData.subscription_model){
                    $(".subscription-plans-wrapper").removeClass('d-none');
                    database.collection('subscription_plans').where('isEnable','==',true).where('sectionId','==',section_id).get().then(async function(snapshots) {
                        snapshots.docs.forEach((listval) => {
                            var data=listval.data();
                            $('#subscription_plan').append($("<option></option>")
                                .attr("value",data.id)
                                .text(data.name));
                        });
                    });
                }
                
                jQuery("#country_selector").select2({
                    templateResult: formatState,
                    templateSelection: formatState2,
                    placeholder: "Select Country",
                    allowClear: true
                });

                // --- ADD THIS BLOCK TO SET DEFAULT COUNTRY CODE ---
                var globalSettingsRef = database.collection('settings').doc('globalSettings');
                globalSettingsRef.get().then(async function (snapshot) {
                    var globalSettings = snapshot.data();
                    if (globalSettings && globalSettings.defaultCountryCode) {
                        var defaultPhoneCode = globalSettings.defaultCountryCode.replace('+', '').trim();

                        // Find the option with matching phoneCode
                        var $option = $("#country_selector option").filter(function() {
                            return $(this).val() === defaultPhoneCode;
                        });

                        if ($option.length > 0) {
                            $("#country_selector").val(defaultPhoneCode).trigger('change');
                        } else {
                            console.warn("Default country code not found in list:", defaultPhoneCode);
                        }
                    }
                }).catch(function (error) {
                    console.error("Error fetching global settings: ", error);
                });
                // --- END OF DEFAULT COUNTRY LOGIC ---

                var adminCommissionData = await database.collection('sections').where('serviceTypeFlag', '==', "ondemand-service").get();

                if (!adminCommissionData.empty) {
                    var commissionData = adminCommissionData.docs[0].data(); 
                    adminCommission = commissionData.adminCommision; 
                }
                else
                {
                    adminCommission = '';
                }
            });

            $(".save-form-btn").click(async function () {

                $(".error_top").hide();
                var latitude = parseFloat(0.01);
                var longitude = parseFloat(0.01);

                var userFirstName = $(".user_first_name").val();
                var userLastName = $(".user_last_name").val();
                var email = $(".user_email").val();
                var password = $(".user_password").val();
                var country_code = '+' + jQuery("#country_selector").val();
                var ccode = jQuery("#country_selector").val();
                var userPhone = $(".user_phone").val();
                var active = $(".user_active").is(":checked");
                var location = { 'latitude': latitude, 'longitude': longitude };
                var user_name = userFirstName + " " + userLastName;
                var user_id = "1";
                var subscriptionPlanId=$('#subscription_plan').val();

                if (userFirstName == '') {
                    showError("1");
                } else if (email == '') {
                    showError("1");
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showError("1");
                } else if (password == '') {
                    showError("1");
                } else if (password.length < 6) {
                    showError("Password must be at least 6 characters.");
                } else if(!ccode) {
                    showError("1");
                } else if (userPhone == '') {
                    showError("1");
                } else if (subscriptionPlanId == '' && businessModelData.subscription_model) {
                    showError("1");
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

                    jQuery("#data-table_processing").show();

                    if(subscriptionPlanId && subscriptionPlanId !='') {
                        var subscriptionData=await getSubscriptionDetails(subscriptionPlanId);
                    } else {
                        var subscriptionData=null;
                    }

                    user_id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('user_' + Date.now());
storeImageData().then(IMG => {
                                database.collection('users').doc(user_id).set({
                                    'section_id': section_id,
                                    'firstName': userFirstName,
                                    'lastName': userLastName,
                                    'email': email,
                                    'phoneNumber': country_code+userPhone,
                                    'profilePictureURL': IMG,
                                    'role': 'provider',
                                    'id': user_id,
                                    'location': location,
                                    'active': active,
                                    'isActive': active,
                                    'createdAt': createdAt,
                                    'userBankDetails': userBankDetails,
                                    'adminCommission':adminCommission,
                                    'wallet_amount': 0,
                                    'reviewsCount': 0,
                                    'reviewsSum': 0,
                                    'subscription_plan': subscriptionData!=null? subscriptionData:null,
                                    'subscriptionPlanId': subscriptionData!=null? subscriptionData.id:null,
                                    'subscriptionExpiryDate': subscriptionData!=null? subscriptionData.expiryDate:null

                                }).then(async function (result) {

                                    if(subscriptionData!=null) {
                                        historyData={'subscriptionData': subscriptionData,'userId': user_id,'expire_date': subscriptionData.expiryDate}
                                        await addSubscriptionHistory(historyData);
                                    }
                                   
                                    window.location.href = '1';

                                }).catch(function (error) {
                                    jQuery("#data-table_processing").hide();
                                    showError(getProviderErrorMessage(error));
                                });
                            }).catch(function (error) {
                                jQuery("#data-table_processing").hide();
                                showError(getProviderErrorMessage(error));
                            });

                }
            });


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
                        $("#uploaded_image_owner").attr('src', photo);
                        $(".uploaded_image_owner").show();
                    };
                })(f);
                reader.readAsDataURL(f);
            }
            async function storeImageData() {
                var newPhoto = '';
                try {
                    if (photo != "") {
                        photo = photo.replace(/^data:image\/[a-z]+;base64,/, "")
                        var uploadTask = await storageRef.child(fileName).putString(photo, 'base64', { contentType: 'image/jpg' });
                        var downloadURL = await uploadTask.ref.getDownloadURL();
                        newPhoto = downloadURL;
                        photo = downloadURL;
                    }
                } catch (error) {
                    console.log("ERR ===", error);
                }
                return newPhoto;
            }
            function formatState(state) {
                if (!state.id) {
                    return state.text;
                }
                var baseUrl = "1/scss/icons/flag-icon-css/flags";
                var $state = $(
                    '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
                );
                return $state;
            }
            function formatState2(state) {
                if (!state.id) {
                    return state.text;
                }
                var baseUrl = "1/scss/icons/flag-icon-css/flags";
                var $state = $(
                    '<span><img class="img-flag" /> <span></span></span>'
                );
                $state.find("span").text(state.text);
                $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
                return $state;
            }
            var newcountriesjs = '1';
            var newcountriesjs = JSON.parse(newcountriesjs);

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

            function getProviderErrorMessage(error) {
                if (!error) {
                    return 'Something went wrong. Please try again.';
                }

                var messages = {
                    'auth/email-already-in-use': 'This email is already registered.',
                    'auth/invalid-email': 'Please enter a valid email address.',
                    'auth/weak-password': 'Password must be at least 6 characters.',
                    'auth/operation-not-allowed': 'Email/password sign-in is not enabled.',
                    'auth/network-request-failed': 'Network error. Please check your connection and try again.',
                };

                if (error.code && messages[error.code]) {
                    return messages[error.code];
                }

                if (error.message) {
                    return error.message;
                }

                return String(error);
            }

            async function getSubscriptionDetails(subscriptionId) {
                var data='';
                await database.collection('subscription_plans').where('id','==',subscriptionId).get().then(async function(
                    snapshot) {
                    data=snapshot.docs[0].data();
                    var currentDate=new Date();
                    if(data.expiryDay!='-1') {
                        currentDate.setDate(currentDate.getDate()+parseInt(data.expiryDay));
                        data.expiryDate=kweekFirestore.Timestamp.fromDate(currentDate);
                    } else {
                        data.expiryDate=null;
                    }

                })
                return data;
            }
            async function addSubscriptionHistory(historyData) {
                var id_order=database.collection('tmp').doc().id;
                var createdAt=kweekFirestore.FieldValue.serverTimestamp();

                var userId=historyData.userId;
                await database.collection('subscription_history').doc(id_order).set({
                    'id': id_order,
                    'user_id': historyData.userId,
                    'expiry_date': historyData.expire_date,
                    'createdAt': createdAt,
                    'subscription_plan': historyData.subscriptionData,
                    'payment_type': 'cod'
                })
            }

        
