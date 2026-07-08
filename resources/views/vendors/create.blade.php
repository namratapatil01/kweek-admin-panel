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
            <h3 class="text-themecolor">{{trans('lang.createe_vendor')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('vendors') !!}">{{trans('lang.vendors')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.createe_vendor')}}</li>
            </ol>
        </div>
        </div>
        
            <div class="card-body">
                <div class="error_top"></div>
                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.admin_area')}}</legend>

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
                                    <input type="email" class="form-control user_email" required onkeypress="return chkAlphabetsLower(event,'error1')">
                                    <div id="error1" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_email_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.password')}}</label>
                                <div class="col-7">
                                    <input type="password" class="form-control user_password" required>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_password_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
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
                                            <input type="text" class="form-control user_phone"
                                                onkeypress="return chkAlphabets2(event,'error1')">
                                            <div id="error1" class="err"></div>
                                            <div class="form-text text-muted w-50">
                                                {{ trans("lang.user_phone_help") }}
                                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.user_profile_picture')}}</label>
                                <input type="file" onChange="handleFileSelectowner(event)" class="col-7">
                                <div id="uploding_image_owner"></div>
                                <div class="uploaded_image_owner" style="display:none;"><img id="uploaded_image_owner"
                                        src="" width="150px" height="150px;"></div>
                            </div>

                        </fieldset>

                        <fieldset class="subscription-plans-wrapper d-none">
                            <legend>{{ trans('lang.subscription_details') }}</legend>
                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{ trans('lang.select_subscription_plan') }}</label>
                                <div class="col-7">
                                    <select class="form-control" id="subscription_plan">
                                        <option value="" selected> {{ trans('lang.select_subscription_plan') }}</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('vendor')}} {{trans('lang.active_deactive')}}</legend>
                            <div class="form-group row">

                                <div class="form-group row width-50">
                                    <div class="form-check width-100">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label"
                                            for="is_active">{{trans('lang.active')}}</label>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>{{trans('lang.bankdetails')}}</legend>
                            <div class="form-group row width-100" style="display: none;" id="companyDriverShowDiv">
                                <div class="col-12">
                                    <h6><a href="#">{{ trans("lang.driver_add_by_company_info") }}</a>
                                    </h6>
                                </div>
                            </div>
                            <div class="form-group row" id="companyDriverHideDiv">

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.bank_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="bank_name" class="form-control" id="bankName">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.branch_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="branch_name" class="form-control" id="branchName">
                                    </div>
                                </div>


                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.holer_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control"
                                            id="accountNumber">
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
            <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}
            </button>
            <a href="{!! route('vendors') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    
</div>


@endsection

@section('scripts')
<script type="text/javascript">
    var section_id = getCookie('section_id') || '';
    var ownerphoto = '';
    var ownerFileName = '';
    var businessModelData = { subscription_model: false };
    var newcountriesjs = JSON.parse(@json($newcountriesjs));

    window.chkAlphabetsLower = window.chkAlphabetsLower || function (event, msg) {
        var char = event.which || event.keyCode;
        if (!(char >= 97 && char <= 122) && !(char >= 48 && char <= 57) && char !== 46 && char !== 64) {
            if (msg && document.getElementById(msg)) {
                document.getElementById(msg).innerHTML = 'Not Accept Upper case letters';
            }
            return false;
        }
        if (msg && document.getElementById(msg)) {
            document.getElementById(msg).innerHTML = '';
        }
        return true;
    };

    function handleFileSelectowner(evt) {
        var f = evt.target.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            ownerphoto = e.target.result;
            $("#uploaded_image_owner").attr('src', ownerphoto);
            $(".uploaded_image_owner").show();
        };
        reader.readAsDataURL(f);
    }
    window.handleFileSelectowner = handleFileSelectowner;

    async function storeImageData() {
        return { ownerImage: ownerphoto || '' };
    }

    function formatState(state) {
        if (!state.id) return state.text;
        var baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
        return $('<span><img src="' + baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg" class="img-flag" /> ' + state.text + '</span>');
    }
    function formatState2(state) {
        if (!state.id) return state.text;
        var baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
        var $state = $('<span><img class="img-flag" /><span></span></span>');
        $state.find('span').text(state.text);
        $state.find('img').attr('src', baseUrl + '/' + newcountriesjs[state.element.value].toLowerCase() + '.svg');
        return $state;
    }

    $(document).ready(function () {
        $.get("{{ route('vendors.meta') }}", function (meta) {
            businessModelData.subscription_model = meta.subscription_model;
            if (meta.subscription_model) {
                $(".subscription-plans-wrapper").removeClass('d-none');
                $.get("{{ route('vendors.subscription-plans') }}", { section_id: section_id }, function (res) {
                    (res.plans || []).forEach(function (plan) {
                        $('#subscription_plan').append($('<option></option>').attr('value', plan.id).text(plan.name));
                    });
                });
            }
            if (meta.defaultCountryCode) {
                var defaultPhoneCode = String(meta.defaultCountryCode).replace('+', '').trim();
                var $option = $("#country_selector option").filter(function () {
                    return $(this).val() === defaultPhoneCode;
                });
                if ($option.length) {
                    $("#country_selector").val(defaultPhoneCode).trigger('change');
                }
            }
        });

        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: 'Select Country',
            allowClear: true
        });
    });

    $(".save-form-btn").click(async function () {
        $(".error_top").hide();
        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var userPhone = $(".user_phone").val();
        var country_code = '+' + jQuery("#country_selector").val();
        var ccode = jQuery("#country_selector").val();
        var vendor_active = $("#is_active").is(':checked');
        var subscriptionPlanId = $('#subscription_plan').val();
        var user_id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('vendor_' + Date.now());

        if (!userFirstName) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_name_error') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!userLastName) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_lastname_error') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!email) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_email') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!password) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_password_error') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!ccode) {
            $(".error_top").show().html("<p>{{ trans('lang.select_country_code') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!userPhone) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_phone') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!subscriptionPlanId && businessModelData.subscription_model) {
            $(".error_top").show().html("<p>{{ trans('lang.select_subscription_plan') }}</p>");
            return window.scrollTo(0, 0);
        }

        jQuery("#data-table_processing").show();
        var subscriptionData = null;
        var subscriptionExpiryDate = null;

        try {
            if (subscriptionPlanId) {
                var planRes = await $.get("{{ route('vendors.subscription-plan', ':id') }}".replace(':id', subscriptionPlanId));
                subscriptionData = planRes.data;
                subscriptionExpiryDate = planRes.data.expiryDate;
            }

            var IMG = await storeImageData();
            var userBankDetails = {
                bankName: $("#bankName").val(),
                branchName: $("#branchName").val(),
                holderName: $("#holderName").val(),
                accountNumber: $("#accountNumber").val(),
                otherDetails: $("#otherDetails").val()
            };

            $.ajax({
                url: "{{ route('vendors.store-vendor') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: user_id,
                    firstName: userFirstName,
                    lastName: userLastName,
                    email: email,
                    password: password,
                    phoneNumber: country_code + userPhone,
                    active: vendor_active,
                    profilePictureURL: IMG.ownerImage,
                    userBankDetails: userBankDetails,
                    sectionId: section_id,
                    subscription_plan: subscriptionData,
                    subscriptionPlanId: subscriptionPlanId || null,
                    subscriptionExpiryDate: subscriptionExpiryDate
                },
                success: function () {
                    window.location.href = '{{ route('vendors') }}';
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Save failed';
                    $(".error_top").show().html('<p>' + msg + '</p>');
                    window.scrollTo(0, 0);
                }
            });
        } catch (err) {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html('<p>' + err + '</p>');
            window.scrollTo(0, 0);
        }
    });
</script>
@endsection
