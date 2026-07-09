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
            <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('users') !!}">{{trans('lang.user_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.user_create')}}</li>
            </ol>
        </div>        
    </div>

    <div class="container-fluid">
            <div class="card-body">
            
                <div class="error_top"></div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.user_details')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name">
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
                                    <input type="text" class="form-control user_email" onkeypress="return chkAlphabetsLower(event,'error1')">
                                    <div id="error1" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_email_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                <div class="col-7">
                                    <input type="password" class="form-control user_password">
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_password_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-md-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-7">
                                    <div class="phone-box position-relative" id="phone-box">
                                            <select name="country" id="country_selector">
                                                <?php foreach ($newcountries as $keycy => $valuecy) { ?>
                                                <?php $selected = ""; ?>
                                                <option <?php echo $selected; ?> code="<?php echo $valuecy->code; ?>"
                                                        value="<?php echo $keycy; ?>">
                                                    +<?php echo $valuecy->phoneCode; ?> {{$valuecy->countryName}}</option>
                                                <?php } ?>
                                            </select>
                                            <input type="text" class="form-control user_phone"  onkeypress="return chkAlphabets2(event,'error1')">
                                            <div id="error1" class="err"></div>
                                    </div>
                                </div>
                                    <div class="form-text text-muted w-50">
                                        {{ trans("lang.user_phone_help") }}
                                    </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.vendor_image')}}</label>
                                <input type="file" onChange="handleFileSelect(event)" class="col-7" id="userImage">
                                <div class="placeholder_img_thumb user_image"></div>
                                <div id="uploding_image"></div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.user_active_deactive')}}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="user_active" id="user_active">
                                    <label class="col-3 control-label"
                                        for="user_active">{{trans('lang.active')}}</label>

                                </div>
                            </div>

                        </fieldset>

                    </div>
                </div>
            </div>

            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i> {{
                    trans('lang.save')}}</button>
                <a href="{!! route('users') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                    trans('lang.cancel')}}</a>
            </div>

            </div>
    </div>

</div>

@endsection

@section('scripts')

<script type="text/javascript">

    $(document).ready(function() {
        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            allowClear: true
        });
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

    var database = kweekDb();
    var photo = "";
    var fileName="";
    var storageRef = kweekFileStore().ref('images');
    
    $(".save-form-btn").click(function () {

        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var country_code = '+' +$("#country_selector").val();
        var ccode = jQuery("#country_selector").val();
        var userPhone = $(".user_phone").val();
        var active = $(".user_active").is(":checked");

        if (userFirstName == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_firstname_error')}}</p>");
            window.scrollTo(0, 0);
        }else if (userLastName == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_lastname_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (email == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_email_error')}}</p>");
            window.scrollTo(0, 0);
        } else if (password == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_password_error')}}</p>");
            window.scrollTo(0, 0);
        } else if(!ccode) {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.select_country_code')}}</p>");
            window.scrollTo(0,0);
        } else if (userPhone == '') {
            $(".error_top").show();
            $(".error_top").html("");
            $(".error_top").append("<p>{{trans('lang.user_phone_error')}}</p>");
            window.scrollTo(0, 0);
        } else {
            jQuery("#data-table_processing").show();

            storeImageData().then(function (IMG) {
                var formData = new FormData();
                formData.append('firstName', userFirstName);
                formData.append('lastName', userLastName);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('countryCode', country_code);
                formData.append('phoneNumber', userPhone);
                formData.append('profilePictureURL', IMG || '');
                formData.append('active', active ? '1' : '0');

                fetch('{{ route('users.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                }).then(async function (response) {
                    jQuery("#data-table_processing").hide();
                    if (response.ok) {
                        window.location.href = '{{ route('users') }}';
                        return;
                    }
                    var data = await response.json().catch(function () { return {}; });
                    var message = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : '{{ trans('lang.error') }}');
                    $(".error_top").show().html("<p>" + message + "</p>");
                    window.scrollTo(0, 0);
                }).catch(function (error) {
                    jQuery("#data-table_processing").hide();
                    $(".error_top").show().html("<p>" + error + "</p>");
                    window.scrollTo(0, 0);
                });
            }).catch(function (err) {
                jQuery("#data-table_processing").hide();
                $(".error_top").show().html("<p>" + err + "</p>");
                window.scrollTo(0, 0);
            });
        }
    })

    function handleFileSelect(evt) {
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
                $(".user_image").empty();
                $(".user_image").append('<span class="image-item" id="photo_user"><span class="remove-btn" data-id="user-remove" data-img="' + photo + '"><i class="fa fa-remove"></i></span><img class="rounded" style="width:50px" src="' + photo + '" alt="image"></span>');

            };
        })(f);
        reader.readAsDataURL(f);
    }
    function chkAlphabetsLower(event, msg) {
        let char = event.which || event.keyCode;
        if (!(char >= 97 && char <= 122) && !(char >= 48 && char <= 57) && !(char == 46) && !(char == 64)){
            document.getElementById(msg).innerHTML = "Not Accept Upper case letters";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }

    async function storeImageData() {
        if (!photo || photo.startsWith('http://') || photo.startsWith('https://')) {
            return photo;
        }
        
        try {
            const responseBlob = await fetch(photo);
            const blob = await responseBlob.blob();
            
            const formData = new FormData();
            formData.append('file', blob, fileName || 'user-image.jpg');
            formData.append('directory', 'users');
            
            const response = await fetch('{{ url("admin-data/upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw result.message || 'Image upload failed.';
            }
            
            return result.url;
        } catch (error) {
            console.log("ERR ===", error);
            return '';
        }
    }
    $(document).on("click", ".remove-btn", function () {
        $(".image-item").remove();
        $('#userImage').val('');
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
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            document.getElementById(msg).innerHTML = "Accept only Number and Dot(.)";
            return false;
        } else {
            document.getElementById(msg).innerHTML = "";
            return true;
        }
    }
</script>
@endsection