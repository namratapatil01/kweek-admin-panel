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
            <h3 class="text-themecolor">{{trans('lang.edit_vendor')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('vendors') !!}">{{trans('lang.vendors')}}</a>
                </li>
                <li class="breadcrumb-item active">{{trans('lang.edit_vendor')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="resttab-sec">
                    <div class="menu-tab">
                        <ul>
                            <li class="active vendorRouteLi" style="display:none;">
                                <a href="{{ route('vendors.edit', $id) }}"> <i class="ti-user"></i> {{ trans('lang.profile') }}</a>
                            </li>
                            <li class="vendorRouteLi" style="display:none;">
                                <a class="vendorRoute"> <i class="ri-shopping-bag-2-fill"></i> {{ trans('lang.vendor') }}</a>
                            </li>
                        </ul>
                    </div>
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
                                        <input type="email" class="form-control user_email" required>
                                        <div class="form-text text-muted">
                                            {{ trans("lang.user_email_help") }}
                                        </div>
                                    </div>
                                </div>
 

                                <div class="form-group row width-50">
                                    <label class="col-3 control-label">{{trans('lang.user_phone')}}</label>
                                    <div class="col-7">
                                        <input type="text" class="form-control user_phone"
                                            onkeypress="return chkAlphabets2(event,'error1')">
                                        <div id="error1" class="err"></div>
                                        <div class="form-text text-muted w-50">
                                            {{ trans("lang.user_phone_help") }}
                                        </div>
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

                            <fieldset class="change_expiry_date_div" style="display:none;">
                                    <legend>{{ trans('lang.store_subscription_model') }}</legend>
                                   
                                    <div class="form-group row width-100">
                                        <label class="col-4 control-label">{{ trans('lang.change_expiry_date') }}</label>
                                        <div class="col-7">
                                            <input type="date" name="change_expiry_date" class="form-control"
                                                id="change_expiry_date" value="">
                                        </div>
                                    </div>
                                </fieldset>

                            

                            <fieldset>
                                <legend>{{trans('lang.vendor')}} {{trans('lang.active_deactive')}}</legend>

                                <div class="form-group row width-100">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_active">
                                        <label class="col-3 control-label"
                                            for="is_active">{{trans('lang.active')}}</label>
                                    </div>

                                    <div class="form-check">
                                        <input type="checkbox" id="reset_password">
                                        <label
                                            class="col-3 control-label">{{trans('lang.reset_store_password')}}</label>

                                            <div class="form-text text-muted w-100 col-12">
                                            {{ trans("lang.note_reset_store_password_email") }}
                                        </div>
                                    </div>
                                    <div class="form-button" style="margin-top: 16px;margin-left: 20px;">
                                        <button type="button" class="btn btn-primary"
                                            id="send_mail">{{trans('lang.send_mail')}}
                                        </button>
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
            <button type="button" class="btn btn-primary  edit-form-btn"><i class="fa fa-save"></i>
                {{trans('lang.save')}}
            </button>
            <a href="{!! route('vendors') !!}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>

    </div>
</div>


@endsection
@section('scripts')
<script type="text/javascript">
    var id = "{{ $id }}";
    var store_id = null;
    var ownerPhoto = '';
    var ownerOldImageFile = '';
    var placeholderImage = '';

    function handleFileSelectowner(evt) {
        var f = evt.target.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            ownerPhoto = e.target.result;
            $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + ownerPhoto + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" width="150px" height="150px;">').show();
        };
        reader.readAsDataURL(f);
    }
    window.handleFileSelectowner = handleFileSelectowner;

    async function storeImageData() {
        if (ownerPhoto && ownerPhoto !== ownerOldImageFile) {
            return { ownerImage: ownerPhoto };
        }
        return { ownerImage: ownerOldImageFile || ownerPhoto || '' };
    }

    $("#send_mail").click(function () {
        if ($("#reset_password").is(":checked")) {
            alert('Password reset functionality is disabled pending migration to local mail server.');
        } else {
            alert('{{ trans('lang.error_reset_store_password') }}');
        }
    });

    $(document).ready(function () {
        jQuery("#data-table_processing").show();

        $.get("{{ route('vendors.meta') }}", function (meta) {
            placeholderImage = meta.placeholderImage || '';
        });

        $.get("{{ route('vendors.get-vendor', ':id') }}".replace(':id', id), function (response) {
            var user = response.data || {};
            store_id = user.vendorID || null;

            if (user.subscriptionPlanId) {
                $(".change_expiry_date_div").show();
            }

            $(".user_first_name").val(user.firstName || '');
            $(".user_last_name").val(user.lastName || '');
            $(".user_email").val(typeof shortEmail === 'function' ? shortEmail(user.email) : (user.email || '')).prop('disabled', true);
            $(".user_phone").val(user.phoneNumber || '').prop('disabled', true);

            if (user.profilePictureURL) {
                ownerPhoto = user.profilePictureURL;
                ownerOldImageFile = user.profilePictureURL;
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + ownerPhoto + '" onerror="this.onerror=null;this.src=\'' + placeholderImage + '\'" width="150px" height="150px;">').show();
            } else {
                $(".uploaded_image_owner").html('<img id="uploaded_image_owner" src="' + placeholderImage + '" width="150px" height="150px;">').show();
            }

            if (user.active) {
                $("#is_active").prop('checked', true);
            }

            if (store_id) {
                $('.vendorRouteLi').show();
                $('.vendorRoute').attr('href', "{{ route('stores.edit', ':id') }}".replace(':id', store_id));
            }

            var bank = user.userBankDetails || {};
            $("#bankName").val(bank.bankName || '');
            $("#branchName").val(bank.branchName || '');
            $("#holderName").val(bank.holderName || '');
            $("#accountNumber").val(bank.accountNumber || '');
            $("#otherDetails").val(bank.otherDetails || '');

            if (user.subscriptionExpiryDate) {
                try {
                    var expiresAt = new Date(user.subscriptionExpiryDate);
                    $('#change_expiry_date').val(expiresAt.toISOString().slice(0, 10));
                } catch (e) {}
            }

            jQuery("#data-table_processing").hide();
        }).fail(function () {
            jQuery("#data-table_processing").hide();
            $(".error_top").show().html('<p>Vendor not found.</p>');
        });
    });

    $(".edit-form-btn").click(async function () {
        var userFirstName = $(".user_first_name").val();
        var userLastName = $(".user_last_name").val();
        var change_expiry_date = $('#change_expiry_date').val();
        var vendor_active = $("#is_active").is(':checked');

        if (!userFirstName) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_name_error') }}</p>");
            return window.scrollTo(0, 0);
        }
        if (!userLastName) {
            $(".error_top").show().html("<p>{{ trans('lang.enter_owners_lastname_error') }}</p>");
            return window.scrollTo(0, 0);
        }

        jQuery("#data-table_processing").show();
        var userBankDetails = {
            bankName: $("#bankName").val(),
            branchName: $("#branchName").val(),
            holderName: $("#holderName").val(),
            accountNumber: $("#accountNumber").val(),
            otherDetails: $("#otherDetails").val()
        };

        try {
            var IMG = await storeImageData();
            $.ajax({
                url: "{{ route('vendors.update-vendor', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    firstName: userFirstName,
                    lastName: userLastName,
                    active: vendor_active,
                    profilePictureURL: IMG.ownerImage,
                    userBankDetails: userBankDetails,
                    subscriptionExpiryDate: change_expiry_date || null
                },
                success: function () {
                    window.location.href = '{{ route('vendors') }}';
                },
                error: function (xhr) {
                    jQuery("#data-table_processing").hide();
                    var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Update failed';
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
