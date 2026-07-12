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
            <h3 class="text-themecolor">{{trans('lang.providers_plural')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('providers') !!}">{{trans('lang.providers_plural')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.providers_create')}}</li>
            </ol>
        </div>
    </div>    

        
            <div class="card-body">
                <div class="error_top"></div>

                <div class="row vendor_payout_create">
                    <div class="vendor_payout_create-inner">
                        <fieldset>
                            <legend>{{trans('lang.provider_info')}}</legend>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.first_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_first_name"
                                        onkeypress="return chkAlphabets(event,'error')" required>
                                    <div id="error" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_first_name_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.last_name')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_last_name"
                                        onkeypress="return chkAlphabets(event,'error1')">
                                    <div id="error1" class="err"></div>
                                    <div class="form-text text-muted">
                                        {{ trans("lang.user_last_name_help") }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row width-50">
                                <label class="col-3 control-label">{{trans('lang.email')}}</label>
                                <div class="col-7">
                                    <input type="text" class="form-control user_email">
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
											<input type="text" class="form-control user_phone" placeholder="Phone" onkeypress="return chkAlphabets2(event,'phone_error')">
											<div id="phone_error" class="err"></div>
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
                            <legend>{{trans('lang.active')}}</legend>
                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="user_active" id="user_active">
                                    <label class="col-3 control-label"
                                        for="user_active">{{trans('lang.active')}}</label>

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
                                    <label class="col-4 control-label">{{trans('lang.holder_name')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="holer_name" class="form-control" id="holderName">
                                    </div>
                                </div>

                                <div class="form-group row width-100">
                                    <label class="col-4 control-label">{{trans('lang.account_number')}}</label>
                                    <div class="col-7">
                                        <input type="text" name="account_number" class="form-control"
                                            onkeypress="return chkAlphabets2(event,'error5')" id="accountNumber">
                                        <div id="error5" class="err"></div>
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
                <button type="button" class="btn btn-primary  save-form-btn"><i class="fa fa-save"></i> {{
    trans('lang.save')}}</button>
                <a href="{!! route('providers') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{
    trans('lang.cancel')}}</a>
            </div>
        


@endsection

@section('style')
<style>
    #phone-box {
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        background: #fff;
    }

    #phone-box .select2-container {
        position: absolute !important;
        left: 0;
        top: 0;
        width: 120px !important;
        z-index: 2;
    }

    #phone-box .select2-container .select2-selection--single {
        height: 36px;
        border: 0;
        background: transparent;
    }

    #phone-box .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 8px !important;
        padding-right: 24px !important;
        max-width: none !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    #phone-box .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
        right: 4px;
    }

    #phone-box .select2-selection__clear {
        display: none !important;
    }

    #phone-box .phone-country-prefix {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        gap: 6px;
    }

    #phone-box .phone-country-prefix .img-flag {
        width: 22px;
        height: 16px;
        object-fit: cover;
        flex-shrink: 0;
    }

    #phone-box .phone-country-prefix .phone-dial-code {
        font-size: 14px;
        font-weight: 500;
        color: #67757c;
    }

    #phone-box .form-control.user_phone {
        padding-left: 125px;
        width: 100%;
        height: 38px;
        border: 0;
        box-shadow: none;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript">
    var section_id = getCookie('section_id') || '';
    var ownerphoto = '';
    var businessModelData = { subscription_model: false };
    var newcountriesjs = @json($newcountriesjs);

    function showError(msg) {
        $(".error_top").show().html("<p>" + msg + "</p>");
        window.scrollTo(0, 0);
    }

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

    function formatState(state) {
        if (!state.id) return state.text;
        var phoneCode = String(state.id);
        var code = (newcountriesjs[phoneCode] || '').toLowerCase();
        var baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
        var label = state.text || ('+' + phoneCode);
        if (!code) {
            return $('<span>' + label + '</span>');
        }
        return $('<span class="phone-country-prefix"><img src="' + baseUrl + '/' + code + '.svg" class="img-flag" alt="" /><span>' + label + '</span></span>');
    }

    function formatState2(state) {
        if (!state.id) return state.text;
        var phoneCode = String(state.id);
        var code = (newcountriesjs[phoneCode] || '').toLowerCase();
        var baseUrl = "{{ URL::to('/') }}/scss/icons/flag-icon-css/flags";
        if (!code) {
            return $('<span class="phone-dial-code">+' + phoneCode + '</span>');
        }
        return $('<span class="phone-country-prefix"><img src="' + baseUrl + '/' + code + '.svg" class="img-flag" alt="" /><span class="phone-dial-code">+' + phoneCode + '</span></span>');
    }

    function chkAlphabets(event, msg) {
        if (!(event.which >= 97 && event.which <= 122) && !(event.which >= 65 && event.which <= 90)) {
            document.getElementById(msg).innerHTML = "Accept only Alphabets";
            return false;
        }
        document.getElementById(msg).innerHTML = "";
        return true;
    }

    function chkAlphabets2(event, msg) {
        if (!(event.which >= 48 && event.which <= 57)) {
            document.getElementById(msg).innerHTML = "Accept only Number";
            return false;
        }
        document.getElementById(msg).innerHTML = "";
        return true;
    }

    $(document).ready(function () {
        jQuery("#country_selector").select2({
            templateResult: formatState,
            templateSelection: formatState2,
            placeholder: "Select Country",
            minimumResultsForSearch: 6,
            width: '120px',
            dropdownParent: $('#phone-box')
        });

        $.get("{{ route('providers.meta') }}", function (meta) {
            businessModelData.subscription_model = meta.subscription_model;
            if (meta.subscription_model) {
                $(".subscription-plans-wrapper").removeClass('d-none');
                $.get("{{ route('providers.subscription-plans') }}", { section_id: section_id }, function (res) {
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
                    $("#country_selector").val(defaultPhoneCode).trigger('change.select2');
                }
            }
        });
    });

    $(".save-form-btn").click(async function () {
        $(".error_top").hide();

        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var email = $(".user_email").val();
        var password = $(".user_password").val();
        var country_code = '+' + jQuery("#country_selector").val();
        var ccode = jQuery("#country_selector").val();
        var userPhone = $(".user_phone").val();
        var active = $(".user_active").is(":checked");
        var subscriptionPlanId = $('#subscription_plan').val();
        var user_id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('provider_' + Date.now());

        if (!userFirstName) {
            return showError("{{trans('lang.enter_owners_name_error')}}");
        }
        if (!email) {
            return showError("{{trans('lang.enter_owners_email')}}");
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            return showError("{{ trans('validation.email', ['attribute' => trans('lang.email')]) }}");
        }
        if (!password) {
            return showError("{{trans('lang.enter_owners_password_error')}}");
        }
        if (password.length < 6) {
            return showError("Password must be at least 6 characters.");
        }
        if (!ccode) {
            return showError("{{trans('lang.select_country_code')}}");
        }
        if (!userPhone) {
            return showError("{{trans('lang.enter_owners_phone')}}");
        }
        if (!subscriptionPlanId && businessModelData.subscription_model) {
            return showError("{{trans('lang.select_subscription_plan')}}");
        }

        jQuery("#data-table_processing").show();
        var subscriptionData = null;
        var subscriptionExpiryDate = null;

        try {
            if (subscriptionPlanId) {
                var planRes = await $.get("{{ route('providers.subscription-plan', ':id') }}".replace(':id', subscriptionPlanId));
                subscriptionData = planRes.data;
                subscriptionExpiryDate = planRes.data.expiryDate;
            }

            var userBankDetails = {
                bankName: $("#bankName").val(),
                branchName: $("#branchName").val(),
                holderName: $("#holderName").val(),
                accountNumber: $("#accountNumber").val(),
                otherDetails: $("#otherDetails").val()
            };

            $.ajax({
                url: "{{ route('providers.store-provider') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: user_id,
                    section_id: section_id,
                    sectionId: section_id,
                    firstName: userFirstName,
                    lastName: userLastName,
                    email: email,
                    phoneNumber: country_code + userPhone,
                    password: password,
                    profilePictureURL: ownerphoto || '',
                    location: { latitude: 0.01, longitude: 0.01 },
                    active: active,
                    userBankDetails: userBankDetails,
                    subscription_plan: subscriptionData,
                    subscriptionPlanId: subscriptionPlanId || null,
                    subscriptionExpiryDate: subscriptionExpiryDate
                },
                success: function (response) {
                    if (response.success) {
                        window.location.href = "{{ route('providers') }}";
                    } else {
                        jQuery("#data-table_processing").hide();
                        showError(response.error || "Failed to create provider.");
                    }
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var errMsg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Save failed';
                    showError(errMsg);
                }
            });
        } catch (err) {
            jQuery("#data-table_processing").hide();
            showError(String(err));
        }
    });
</script>
@endsection
