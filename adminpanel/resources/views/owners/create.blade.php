@extends('layouts.app')

@section('content')

<?php
$countries = file_get_contents(public_path('countriesdata.json'));
$countries = json_decode($countries);
$countries = (array)$countries;
$newcountries = array();
$newcountriesjs = array();
foreach ($countries as $keycountry => $valuecountry) {
    $newcountries[$valuecountry->phoneCode] = $valuecountry;
    $newcountriesjs[$valuecountry->phoneCode] = $valuecountry->code;
} 
?>

<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.owners')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('owners') !!}">{{trans('lang.owners')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.create_owner')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="resttab-sec">
                    <div class="error_top"></div>
                    <div class="row vendor_payout_create">
                        <div class="vendor_payout_create-inner">

                            <fieldset>
                                <legend>{{trans('lang.basic_details')}}</legend>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_first_name" required>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_first_name_help") }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_last_name">
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_last_name_help") }}
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                    <div class="col-7">
                                        <input type="email" class="form-control user_email" required>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_email_help") }}
                                        </div>
                                    </div>
                                </div>
    
                                 <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                    <div class="col-7">
                                        <input type="password" class="form-control user_password">
                                        <div class="form-text text-muted">{{trans('lang.user_password_help')}}</div>
                                    </div>
                                </div>

                                <div class="form-group row"> 
                                    <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                    <div class="col-md-6">
                                        <div class="phone-box position-relative" id="phone-box">
                                            <select name="country" id="country_selector">
                                                <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                    <?php $selected = ""; ?>
                                                    <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>" value="<?php echo $keycy; ?>">+<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                                <?php } ?>
                                            </select>
                                            <input type="text" class="form-control user_phone"  onkeypress="return chkAlphabets2(event,'error2')">
                                            <div id="error2" class="err"></div>
                                        </div>
                                    </div>
                                    <div class="form-text text-muted">
                                        {{trans('lang.user_phone_help')}}
                                    </div>
                                </div>       

                                 <div class="form-group row width-100">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label"
                                            for="is_active">{{trans('lang.active')}}</label>
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-3 control-label">{{trans('lang.user_profile_picture')}}</label>
                                    <input type="file" onChange="handleFileSelectowner(event,'vendor')" class="col-7">
                                    <div id="uploding_image_owner"></div>
                                    <div class="uploaded_image_owner" style="display:none;">
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset>
                                <legend>{{trans('lang.bankdetails')}}</legend>

                                <div class="form-group row">

                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{
                                            trans('lang.bank_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" name="bank_name" class="form-control" id="bankName">
                                        </div>
                                    </div>

                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{
                                            trans('lang.branch_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" name="branch_name" class="form-control" id="branchName">
                                        </div>
                                    </div>

                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{
                                            trans('lang.holer_name')}}</label>
                                        <div class="col-7">
                                            <input type="text" name="holer_name" class="form-control" id="holderName">
                                        </div>
                                    </div>

                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{
                                            trans('lang.account_number')}}</label>
                                        <div class="col-7">
                                            <input type="text" name="account_number" class="form-control"
                                                id="accountNumber">
                                        </div>
                                    </div>

                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{
                                            trans('lang.other_information')}}</label>
                                        <div class="col-7">
                                            <input type="text" name="other_information" class="form-control"
                                                id="otherDetails">
                                        </div>
                                    </div>

                                </div>
                            </fieldset>
                          
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="form-group col-12 text-center btm-btn">
            <button type="button" class="btn btn-primary save-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}
            </button>
            <a href="{!! route('owners') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>

    </div>
</div>


@endsection

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.26.0/moment.min.js"></script>

<script type="text/javascript">

    var section_id = getCookie('section_id') || '';
    var service_type = getCookie('service_type') || '';
    var database = firebase.firestore();  
    
    var photo = "";
    var ownerPhoto = '';
    var ownerFileName = '';
    var ownerOldImageFile = '';

    var createdAt = firebase.firestore.FieldValue.serverTimestamp();
    var placeholderImage = '';
    var placeholder = database.collection('settings').doc('placeHolderImage');
    var storageRef = firebase.storage().ref('images');
    var storage = firebase.storage();

    placeholder.get().then(async function (snapshotsimage) {
        var placeholderImageData = snapshotsimage.data();
        placeholderImage = placeholderImageData.image;
    })

    $(document).ready(function () {

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

        $(".save-form-btn").click(async function () {

            var userFirstName = $(".user_first_name").val();
            var userLastName = $(".user_last_name").val();
            var email = $(".user_email").val();
            var password = $(".user_password").val();
            var userPhone = $(".user_phone").val();
            var country_code = '+' + jQuery("#country_selector").val();
            var ccode = jQuery("#country_selector").val();
            var is_active = $("#is_active").is(':checked');
            var owner_id = database.collection('temp').doc().id;

            if (userFirstName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_owners_name_error')}}</p>");
                window.scrollTo(0, 0);
            }else if (userLastName == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_owners_lastname_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (email == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_owners_email')}}</p>");
                window.scrollTo(0, 0);
            } else if (password == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_owners_password_error')}}</p>");
                window.scrollTo(0, 0);
            } else if(!ccode) {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.select_country_code')}}</p>");
                window.scrollTo(0,0); 
            } else if (userPhone == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.enter_owners_phone')}}</p>");
                window.scrollTo(0, 0);

            } else {

                jQuery("#data-table_processing").show();

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

                firebase.auth().createUserWithEmailAndPassword(email, password).then(async function (firebaseUser) {

                    user_id = firebaseUser.user.uid;
                    
                    await storeImageData().then(async (IMG) => {
                        database.collection('users').doc(user_id).set({ 
                            'firstName': userFirstName,
                            'lastName': userLastName,
                            'email': email,
                            'provider': 'email',
                            'phoneNumber': country_code+userPhone,
                            'profilePictureURL': IMG.ownerImage,
                            'role': 'vendor',
                            'id': user_id,
                            'active': is_active,
                            'isActive': false,
                            'isOwner': true,
                            'vendorID': null,
                            'ownerId': null,
                            'role': 'driver',
                            'createdAt': createdAt,
                            'userBankDetails': userBankDetails,
                            'isDocumentVerify': false,
                            'sectionId' : section_id,
                            'serviceType': service_type,
                            'wallet_amount' : 0,
                        }).then(async function (result) { 
                            
                            window.location.href = '{{ route("owners")}}';
                        });

                    }).catch(err => {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + err + "</p>");
                        window.scrollTo(0, 0);
                    });
                });
             }
        })
    });

    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>'
        );
        return $state;
    }

    function formatState2(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = "<?php echo URL::to('/');?>/scss/icons/flag-icon-css/flags";
        var $state = $(
            '<span><img class="img-flag" /> <span></span></span>'
        );
        $state.find("span").text(state.text);
        $state.find("img").attr("src", baseUrl + "/" + newcountriesjs[state.element.value].toLowerCase() + ".svg");
        return $state;
    }
    var newcountriesjs = '<?php echo json_encode($newcountriesjs); ?>';
    var newcountriesjs = JSON.parse(newcountriesjs);

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
                ownerPhoto = filePayload;
                ownerFileName = filename;
                if(ownerPhoto){
                    photo=ownerPhoto;
                }else{
                    photo=placeholderImage;
                }
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + photo + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" width="150px" height="150px;">');
                $(".uploaded_image_owner").show();
            };
        })(f);
        reader.readAsDataURL(f);
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

    async function storeImageData() {
        var newPhoto = [];
        newPhoto['ownerImage'] = ownerPhoto;
        try {
            if (ownerPhoto != '') {
                if (ownerOldImageFile != "" && ownerPhoto != ownerOldImageFile) {
                    var ownerOldImageUrlRef = await storage.refFromURL(ownerOldImageFile);
                    imageBucket = ownerOldImageUrlRef.bucket;
                    var envBucket = "<?php echo env('FIREBASE_STORAGE_BUCKET'); ?>";

                    if (imageBucket == envBucket) {
                        await ownerOldImageUrlRef.delete().then(() => {
                            console.log("Old file deleted!")
                        }).catch((error) => {
                            console.log("ERR File delete ===", error);
                        });
                    } else {
                        console.log('Bucket not matched');
                    }
                }

                if (ownerPhoto != ownerOldImageFile) {

                    ownerPhoto = ownerPhoto.replace(/^data:image\/[a-z]+;base64,/, "")
                    var uploadTask = await storageRef.child(ownerFileName).putString(ownerPhoto, 'base64', { contentType: 'image/jpg' });
                    var downloadURL = await uploadTask.ref.getDownloadURL();
                    newPhoto['ownerImage'] = downloadURL;
                    ownerPhoto = downloadURL;
                }
            }
           
        } catch (error) {
            console.log("ERR ===", error);
        }

        return newPhoto;
    }

</script>
@endsection
