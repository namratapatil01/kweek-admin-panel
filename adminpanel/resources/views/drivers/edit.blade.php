@extends('layouts.app')
@section('content')
    <div class="page-wrapper">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h3 class="text-themecolor">{{trans('lang.driver_plural')}}</h3>
            </div>
            <div class="col-md-7 align-self-center">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{!! route('drivers') !!}">{{trans('lang.driver_plural')}}</a>
                    </li>
                    <li class="breadcrumb-item active">{{trans('lang.driver_edit')}}</li>
                </ol>
            </div>
        </div>
        <div>
            <div class="card-body">
                <div class="row daes-top-sec mb-3">
                    <div class="col-lg-6 col-md-6">
                        <a href="Javascript:void(0)" class="driver_orders_url">
                            <div class="card">
                                <div class="flex-row">
                                    <div class="p-10 bg-info col-md-12 text-center">
                                        <h3 class="text-white box m-b-0"><i class="mdi mdi-cart"></i></h3>
                                    </div>
                                    <div class="align-self-center pt-3 col-md-12 text-center">
                                        <h3 class="m-b-0 text-info" id="total_orders">0</h3>
                                        <h5 class="text-muted m-b-0 driver_order_text">
                                            {{trans('lang.dashboard_total_orders')}}</h5>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <a href="{{route('payoutRequests.drivers.view',$id)}}">
                            <div class="card">
                                <div class="flex-row">
                                    <div class="p-10 bg-info col-md-12 text-center">
                                        <h3 class="text-white box m-b-0"><i class="mdi mdi-bank"></i></h3>
                                    </div>
                                    <div class="align-self-center pt-3 col-md-12 text-center">
                                        <h3 class="m-b-0 text-info" id="wallet_amount"></h3>
                                        <h5 class="text-muted m-b-0">{{trans('lang.wallet_Balance')}}</h5>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="error_top"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.driver_details')}}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name"
                                           onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    <div class="form-text text-muted">{{trans('lang.first_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name"
                                           onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    <div class="form-text text-muted">{{trans('lang.last_name_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_email">
                                    <div class="form-text text-muted">{{trans('lang.user_email_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_phone"
                                        onkeypress="return chkAlphabets2(event,'error2')" readonly>
                                    <div id="error2" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_phone_help") }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_latitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_latitude"
                                           onkeypress="return chkAlphabets3(event,'error2')">
                                    <div id="error2" class="err"></div>
                                    <div class="form-text text-muted">{{trans('lang.user_latitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_longitude')}}</label>
                                <div class="col-7">
                                    <input type="number" class="form-control user_longitude"
                                           onkeypress="return chkAlphabets3(event,'error3')">
                                    <div id="error3" class="err"></div>
                                    <div class="form-text text-muted">{{trans('lang.user_longitude_help')}}</div>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label ">{{trans('lang.service_type')}}</label>
                                <div class="col-12">
                                    <select name="service_type" id="service_type" class="form-control service_type">
                                        <option value="">{{trans('lang.select')}} {{trans('lang.service_type')}}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.zone') }}<span class="required-field"></span></label>
                                <div class="col-7">
                                    <select id='zone' class="form-control">
                                        <option value="">{{ trans('lang.select_zone') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.profile_image')}}</label>
                                <div class="col-7">
                                    <input type="file" onChange="handleFileSelect(event)" class="">
                                    <div class="form-text text-muted">{{trans('lang.profile_image_help')}}</div>
                                </div>
                                <div class="placeholder_img_thumb user_image">
                                </div>
                                <div id="uploding_image"></div>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="col-7 form-check-inline user_active" id="user_active">
                                <label class="col-3 control-label" for="user_active">{{trans('lang.active')}}</label>
                            </div>
                            <div class="form-check width-100">
                                <input type="checkbox" class="col-7 form-check-inline" id="reset_password">
                                <label class="col-3 control-label"
                                       for="reset_password">{{trans('lang.reset_driver_password')}}</label>
                            </div>
                            <div class="form-group row width-100">
                                <div class="form-text text-muted w-100 col-12">
                                    {{ trans("lang.note_reset_driver_password_email") }}
                                </div>
                            </div>
                            <div class="form-group row width-50">
                                <div class="col-3 control-label" style="margin-top: 16px;">
                                    <button type="button" class="btn btn-primary"
                                            id="send_mail">{{trans('lang.send_mail')}}
                                    </button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="vehicle-details" style="display: none">
                        
                            <legend>{{trans('lang.car_details')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.select_section')}}</label>
                                <div class="col-12">
                                    <select name="vehicle_section_id" id="vehicle_section_id" class="form-control" disabled>
                                        <option value="">{{trans('lang.select_section')}}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="ride-service" style="display:none;">

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.car_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control car_number">
                                        <div class="form-text text-muted">{{trans('lang.car_number_help')}}</div>
                                    </div>
                                </div>
                                
                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.vehicle_type')}}</label>
                                    <div class="col-7">
                                        <select name="vehicle_type" class="form-control vehicle_type">
                                            <option value="">{{trans('lang.select')}} {{trans('lang.vehicle_type')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row width-50"> 
                                    <label class="col-3 control-label">{{trans('lang.car_make')}}</label>
                                    <div class="col-7">
                                        <select name="car_make" class="form-control car_make">
                                            <option value="">{{trans('lang.select')}} {{trans('lang.car_make')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.car_model')}}</label>
                                    <div class="col-7">
                                        <select name="car_model" class="form-control car_model">
                                            <option value="">{{trans('lang.select')}} {{trans('lang.car_model')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                              
                                 <div class="form-group row width-100" id="div_ride_type" style="display: none">
                                    <label class="col-3 control-label" for="user_active">{{ trans('lang.choose_ride_type') }}</label>
                                    <div class="col-7">
                                        <div id="type_ride" style="display: none">
                                            <input type="radio" class="form-check-inline" name="ride_type" id="ride" value="ride">
                                            <label for="ride">{{ trans('lang.ride') }}</label>
                                        </div>
                                        <div id="type_intercity" style="display: none">
                                            <input type="radio" class="form-check-inline" name="ride_type" id="intercity" value="intercity">
                                            <label for="intercity">{{ trans('lang.intercity') }}</label>
                                        </div>
                                        <div id="type_both" style="display: none">
                                            <input type="radio" class="form-check-inline" name="ride_type" id="both" value="both">
                                            <label for="both">{{ trans('lang.both') }}</label>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>

                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.bankdetails')}}</legend>
                            <div class="form-group row" id="companyDriverHideDiv">
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.bank_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="bank_name" class="form-control" id="bankName"
                                               onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.branch_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="branch_name" class="form-control" id="branchName"
                                               onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.holer_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName"
                                               onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode < 123) || (event.charCode == 32)">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control" id="accountNumber"
                                               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    </div>
                                </div>
                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.other_information')}}</label>
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
            <div class="form-group col-12 text-center btm-btn">
                <button type="button" class="btn btn-primary edit-form-btn"><i class="fa fa-save"></i> {{
                trans('lang.save')}}
                </button>
                <a href="{!! route('drivers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
                trans('lang.cancel')}}</a>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script type="text/javascript">

        var id = "{{ $id }}";
        
        var section_id = getCookie('section_id') || '';
        var service_type = getCookie('service_type') || '';

        if (service_type == "cab-service" || service_type == "rental-service") {
            $('.vehicle-details').show();
            $('.ride-service').show();
        }else if (service_type == "parcel_delivery") {
            $('.vehicle-details').show();
        } 
        
        // Zone, CarMake, CarModel, VehicleType are now from MySQL


        var photo = "";
        var fileName = '';
        var oldProfileFile = '';
        // var storage = firebase.storage();
        // var storageRef = firebase.storage().ref('images');

        // Settings, currencies, services, sections — from MySQL
        var placeholderImage = '';
        var currentCurrency = '';
        var currencyAtRight = false;
        var decimal_degits = 0;
        $.get("{{ route('drivers.meta') }}", function (meta) {
            placeholderImage = meta.placeholderImage || '';
            currentCurrency  = meta.currencySymbol   || '';
            currencyAtRight  = meta.symbolAtRight    || false;
            decimal_degits   = meta.decimal_degits   || 2;
        });
        
        $("#send_mail").click(function () {
            if ($("#reset_password").is(":checked")) {
                var email = $(".user_email").val();
                alert('Password reset functionality is disabled pending migration to local mail server.');
                // firebase.auth().sendPasswordResetEmail(email)
                    .then((res) => {
                        alert('{{trans("lang.driver_mail_sent")}}');
                    })
                    .catch((error) => {
                        console.log('Error password reset: ', error);
                    });
            } else {
                alert('{{trans("lang.error_reset_driver_password")}}');
            }
        });

        $(document).ready(function () {

            jQuery("#data-table_processing").show();

          // Section rideType + sections dropdown — from MySQL
        $.get("{{ route('drivers.sections') }}", { serviceTypeFlag: service_type }, function (res) {
            var matchedSection = res.sections ? res.sections.find(function(s){ return s.id == section_id; }) : null;
            if (matchedSection && service_type == "cab-service" && matchedSection.rideType && matchedSection.rideType != '') {
                $("#div_ride_type").show();
                if (matchedSection.rideType == "ride") {
                    $("#div_ride_type #type_ride").show();
                } else if (matchedSection.rideType == "intercity") {
                    $("#div_ride_type #type_intercity").show();
                } else if (matchedSection.rideType == "both") {
                    $("#div_ride_type #type_ride").show();
                    $("#div_ride_type #type_intercity").show();
                    $("#div_ride_type #type_both").show();
                }
            }
            res.sections && res.sections.forEach(function(data) {
                let option = $('<option></option>').attr('value', data.id).text(data.name);
                if (data.id == section_id) option.prop('selected', true);
                $('#vehicle_section_id').append(option);
            });
            
            // Fetch vehicle types if cab or rental service
            if (service_type == "cab-service" || service_type == "rental-service") {
                $.get("{{ route('drivers.vehicle-types') }}", { service_type: service_type, sectionId: section_id }, function(vRes) {
                    vRes.vehicleTypes && vRes.vehicleTypes.forEach(function(data) {
                        $('.vehicle_type').append($('<option></option>').attr("value", data.name).attr("data-id", data.id).text(data.name));
                    });
                });
            }
        });

        // Zone dropdown — from MySQL
        $('#zone').empty().append(
            $('<option></option>').attr("value", "").attr("disabled", true).attr("selected", 'selected').text("{{ trans('lang.select_zone') }}")
        );
        $.get("{{ route('drivers.zones') }}", function (res) {
            res.zones && res.zones.forEach(function(data) {
                $('#zone').append($('<option></option>').attr("value", data.id).text(data.name));
            });
        });

        if (service_type == "cab-service" || service_type == "rental-service") {
            // Car Makes — from MySQL
            $.get("{{ route('drivers.car-makes') }}", function (res) {
                res.carMakes && res.carMakes.forEach(function(data) {
                    $('.car_make').append($('<option></option>').attr("value", data.name).text(data.name));
                });
            });
        }

            // Services dropdown — from MySQL
            $.get("{{ route('drivers.services') }}", function (res) {
                res.services && res.services.forEach(function(data) {
                    let option = $('<option></option>').attr('value', data.flag).text(data.name);
                    if (data.flag == service_type) option.prop('selected', true);
                    $('.service_type').append(option);
                });
            });

            // When user manually changes the service type, load corresponding dropdowns dynamically without reloading
            $('.service_type').on('change', function() {
                var selectedType = $(this).val();
                service_type = selectedType;
                document.cookie = "service_type=" + selectedType + "; path=/";
                
                // Reset and hide dynamic sections
                $('.vehicle-details').hide();
                $('.ride-service').hide();
                $("#div_ride_type").hide();
                $("#div_ride_type #type_ride").hide();
                $("#div_ride_type #type_intercity").hide();
                $("#div_ride_type #type_both").hide();
                
                $('#vehicle_section_id').empty();
                $('.vehicle_type').empty();
                $('.car_make').empty();

                if (service_type == "cab-service" || service_type == "rental-service") {
                    $('.vehicle-details').show();
                    $('.ride-service').show();
                } else if (service_type == "parcel_delivery") {
                    $('.vehicle-details').show();
                }
                
                jQuery("#data-table_processing").show();

                $.get("{{ route('drivers.sections') }}", { serviceTypeFlag: service_type }, function (res) {
                    $('#vehicle_section_id').append('<option value="">{{trans("lang.select")}} {{trans("lang.vehicle_section")}}</option>');
                    res.sections && res.sections.forEach(function(data) {
                        let option = $('<option></option>').attr('value', data.id).text(data.name);
                        $('#vehicle_section_id').append(option);
                    });
                    
                    if (service_type == "cab-service" || service_type == "rental-service") {
                        $('.vehicle_type').append('<option value="">{{trans("lang.select")}} {{trans("lang.vehicle_type")}}</option>');
                        $.get("{{ route('drivers.vehicle-types') }}", { service_type: service_type }, function(vRes) {
                            vRes.vehicleTypes && vRes.vehicleTypes.forEach(function(data) {
                                $('.vehicle_type').append($('<option></option>').attr("value", data.name).attr("data-id", data.id).text(data.name));
                            });
                        });
                        
                        $('.car_make').append('<option value="">{{trans("lang.select")}} {{trans("lang.car_make")}}</option>');
                        $.get("{{ route('drivers.car-makes') }}", function (cRes) {
                            cRes.carMakes && cRes.carMakes.forEach(function(data) {
                                $('.car_make').append($('<option></option>').attr("value", data.name).text(data.name));
                            });
                            jQuery("#data-table_processing").hide();
                        });
                    } else {
                        jQuery("#data-table_processing").hide();
                    }
                });
            });


            var getDriverUrl = "{{ route('drivers.get-driver', ':id') }}".replace(':id', id);
            $.get(getDriverUrl, function (response) {
                var user = response.data;
                
                $(".user_first_name").val(user.firstName);
                $(".user_last_name").val(user.lastName);
                $(".user_email").val(shortEmail(user.email)).prop('disabled',true);
                
                $(".car_number").val(user.carNumber);

                if (user.hasOwnProperty('zoneId') && user.zoneId != '') {
                    $("#zone").val(user.zoneId);
                }
                let = phoneNumber = user.countryCode + user.phoneNumber;
                if(phoneNumber){
                    $(".user_phone").val('+' + EditPhoneNumber(phoneNumber.slice(1))).prop('disabled',true);
                }else{
                    $(".user_phone").val(EditPhoneNumber(phoneNumber)).prop('disabled',true);
                }
                if (user.hasOwnProperty('carMakes')) {
                    $('.car_make').val(user.carMakes).trigger('change');
                }
                if (user.hasOwnProperty('carName')) {
                    setTimeout(function(){
                        $('.car_model').val(user.carName);
                    },500);
                }
                if (user.hasOwnProperty('carColor')) {
                    $('.car_color').val(user.carColor);
                }
                if (user.hasOwnProperty('serviceType')) {
                    $('.service_type').val(user.serviceType);
                }
                if (user.hasOwnProperty('vehicleType')) {
                    $('.vehicle_type').val(user.vehicleType);
                }
                if (user.hasOwnProperty('sectionId') && user.sectionId != '') {
                    $('#vehicle_section_id').val(user.sectionId);
                }
                if (user.hasOwnProperty('rideType')) {
                    if(user.rideType == "ride"){
                        $("#div_ride_type #type_ride").show();
                        $("#div_ride_type #type_ride input").prop('checked',true);
                    }else if(user.rideType == "intercity"){
                        $("#div_ride_type #type_intercity").show();
                        $("#div_ride_type #type_intercity input").prop('checked',true);
                    }else if(user.rideType == "both"){
                        $("#div_ride_type #type_ride").show();
                        $("#div_ride_type #type_intercity").show();
                        $("#div_ride_type #type_both").show();
                        $("#div_ride_type #type_both input").prop('checked',true);
                    }
                }
                if (user.hasOwnProperty('vehicleType')) {
                    $('.vehicle_type').val(user.vehicleType).trigger('change');
                }
                if (user.hasOwnProperty('location')) {
                    $(".user_latitude").val(user.location.latitude);
                    $(".user_longitude").val(user.location.longitude);
                }
                oldProfileFile = user.profilePictureURL;
                
                if (user.active) {
                    $(".user_active").prop('checked', true);
                }
                
                if (oldProfileFile != '' && oldProfileFile != null) {
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + oldProfileFile + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" alt="image">');
                } else {
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + placeholderImage + '" alt="image">');
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

                $("#wallet_amount").text(wallet);
                
                getTotalOrders(id, user.serviceType);
                
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
                jQuery("#data-table_processing").hide();
            });

            $(".edit-form-btn").click(function () {

                var userFirstName = $(".user_first_name").val();
                var userLastName = $(".user_last_name").val();
                var email = $(".user_email").val();
                var userPhone = $(".user_phone").val();
                var active = $(".user_active").is(":checked");
                var zoneId = $('#zone option:selected').val();
                
                var latitude = parseFloat($(".user_latitude").val());
                var longitude = parseFloat($(".user_longitude").val());
                var location = { 'latitude': latitude, 'longitude': longitude };
                var vehicleSectionId = $('#vehicle_section_id').val();

                var carNumber = $(".car_number").val() || null;
                var carMakeName = $('.car_make').val() || null;
                var carName = $('.car_model').val() || null;
                
                var vehicleType = $('.vehicle_type').val() || null;
                var vehicleTypeName = $('.vehicle_type option:selected').text() || null;
                var vehicleTypeId = $('.vehicle_type option:selected').data('id') || null;
                var rideType = $("input[name='ride_type']:checked").val() || null;
                
                if (userFirstName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.user_firstname_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (userLastName == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.user_lastname_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if(isNaN(latitude)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_lattitude_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (latitude < -90 || latitude > 90) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_lattitude_limit_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (isNaN(longitude)) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_longitude_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (longitude < -180 || longitude > 180) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.driver_longitude_limit_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if (zoneId == '') {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{ trans('lang.select_zone_help') }}</p>");
                    window.scrollTo(0, 0);
                } else if ((carNumber == '' || carNumber == null) && (service_type == "rental-service" || service_type == "cab-service")) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.car_number_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if ((vehicleType == '' || vehicleType == null) && (service_type === "rental-service" || service_type === "cab-service")){
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.vehicle_type_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if ((carMakeName == '' || carMakeName == null) && (service_type == "rental-service" || service_type == "cab-service")) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.car_make_error')}}</p>");
                    window.scrollTo(0, 0);
                } else if ((carName == '' || carName == null) && (service_type == "rental-service" || service_type == "cab-service")) {
                    $(".error_top").show();
                    $(".error_top").html("");
                    $(".error_top").append("<p>{{trans('lang.car_model_error')}}</p>");
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
                    storeImageData().then(IMG => {
                        var payload = {
                            _token: "{{ csrf_token() }}",
                            firstName: userFirstName,
                            lastName: userLastName,
                            active: active,
                            profilePictureURL: IMG.profile,
                            carNumber: carNumber,
                            carMakes: carMakeName,
                            carName: carName,
                            vehicleId: vehicleTypeId,
                            rideType: rideType,
                            location: location,
                            vehicleType: vehicleType,
                            userBankDetails: userBankDetails,
                            zoneId: zoneId
                        };

                        var updateUrl = "{{ route('drivers.update-driver', ':id') }}".replace(':id', id);
                        $.ajax({
                            url: updateUrl,
                            type: "POST",
                            data: payload,
                            success: function (response) {
                                jQuery("#data-table_processing").hide();
                                window.location.href = '{{ route("drivers")}}';
                            },
                            error: function (xhr, status, error) {
                                jQuery("#data-table_processing").hide();
                                var errMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : error;
                                $(".error_top").show().html("<p>" + errMessage + "</p>");
                                window.scrollTo(0, 0);
                            }
                        });
                    }).catch(function (error) {
                        jQuery("#data-table_processing").hide();
                        $(".error_top").show();
                        $(".error_top").html("");
                        $(".error_top").append("<p>" + error + "</p>");
                        window.scrollTo(0, 0);
                    });
                }
            })
        })

        $('.car_make').on('change', function () {
            var cab_make_name = $(this).val();
            var options = '<option value="">{{trans("lang.select")}} {{trans("lang.car_model")}}</option>';
            
            $.get("{{ route('drivers.car-models') }}", { car_make_name: cab_make_name }, function (res) {
                res.carModels && res.carModels.forEach(function(data) {
                    options += '<option value="' + data.name + '" data-id="' + data.id + '">' + data.name + '</option>';
                });
                $(".car_model").html(options);
            });
        });
        
        function getTotalOrders(id, type) {
            var url = "Javascript:void(0)";
            var order_text = '';
            if (type == "cab-service") {
                url = "{{route('drivers.rides','driverId')}}".replace('driverId', id);
                order_text = "{{trans('lang.rides')}}";
            } else if (type == "rental-service") {
                url = "{{route('rental_orders.driver','id')}}".replace("id", id);
                order_text = "{{trans('lang.rental_orders')}}";
            } else if (type == "delivery-service" || type == "ecommerce-service") {
                url = "{{route('orders','id')}}".replace("id", 'driverId=' + id);
                order_text = "{{trans('lang.order_plural')}}";
            } else if (type == "parcel_delivery") {
                url = "{{route('parcel_orders.driver','id')}}".replace("id", id);
                order_text = "{{trans('lang.parcel_orders')}}";
            }
            // total_orders is injected from MySQL via getDriver endpoint
            var getDriverUrl = "{{ route('drivers.get-driver', ':id') }}".replace(':id', id);
            $.get(getDriverUrl, function (response) {
                var count = response.data && response.data.total_orders ? response.data.total_orders : 0;
                $("#total_orders").text(count);
            });
            $('.driver_order_text').html(order_text);
            $('.driver_orders_url').attr('href', url);
        }


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
                    $(".user_image").append('<img class="rounded" style="width:50px" src="' + photo + '" alt="image" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'">');
                };
            })(f);
            reader.readAsDataURL(f);
        }

        async function storeImageData() {
            var newPhoto = [];
            newPhoto['profile'] = '';
            if (photo != '' && photo != oldProfileFile) {
                try {
                    // Bypass Firebase Storage completely due to missing API keys
                    // and just return the base64 string directly to backend
                    newPhoto['profile'] = photo;
                } catch (error) {
                    console.log("Error handling image: ", error);
                }
            } else {
                newPhoto['profile'] = photo;
            }
            return newPhoto;
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
