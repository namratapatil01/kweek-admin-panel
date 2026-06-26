@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="card">
        <div class="payment-top-tab mt-3 mb-3">
            <ul class="nav nav-tabs card-header-tabs align-items-end">
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/stripe') !!}">{{trans('lang.app_setting_stripe')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/cod') !!}">{{trans('lang.app_setting_cod_short')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/razorpay') !!}">{{trans('lang.app_setting_razorpay')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/paypal') !!}">{{trans('lang.app_setting_paypal')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/wallet') !!}">{{trans('lang.app_setting_wallet')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/orangepay') !!}">{{trans('lang.orangePay')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-maya') !!}">{{trans('lang.arroPayMaya')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-maya-qr') !!}">{{trans('lang.arroPayMayaQr')}}</a></li>
                <li class="nav-item"><a class="nav-link active" href="{!! url('settings/payment/arropay-instapay') !!}">{{trans('lang.arroPayInstapay')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-auth') !!}">{{trans('lang.arroPayAuth')}}</a></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_setting_arropay_instapay')}}</legend>
                        <p class="text-muted">{{trans('lang.app_setting_arropay_instapay_help')}}</p>

                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_arropay_instapay" id="enable_arropay_instapay">
                            <label class="col-3 control-label" for="enable_arropay_instapay">{{trans('lang.app_setting_enable_arropay_instapay')}}</label>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_base_url')}}</label>
                            <div class="col-7"><input type="text" class="form-control arropay_instapay_base_url" placeholder="https://arropay.biz"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_process_endpoint')}}</label>
                            <div class="col-7"><input type="text" class="form-control arropay_instapay_process_endpoint" placeholder="/api/v1/payment/process/instapay"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_api_key')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_instapay_api_key"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_api_secret')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_instapay_api_secret"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_callback_url')}}</label>
                            <div class="col-7"><input type="text" class="form-control arropay_instapay_callback_url"></div>
                            <div class="form-text text-muted col-7 offset-3">{{trans('lang.app_setting_arropay_instapay_callback_url_help')}}</div>
                        </div>

                        <hr>
                        <h5>{{trans('lang.app_setting_arropay_instapay_casa_account')}}</h5>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_account_name')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_account_name"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_account_number')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_account_number"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_mobile_number')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_mobile_number"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_email')}}</label>
                            <div class="col-7"><input type="email" class="form-control sender_email"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_address')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_address"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_barangay')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_barangay"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_city')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_city"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_instapay_sender_zipcode')}}</label>
                            <div class="col-7"><input type="text" class="form-control sender_zipcode"></div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn" style="margin-bottom:inherit;">
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var database = firebase.firestore();
var ref = database.collection('settings').doc('arropay_instapay_settings');

$(document).ready(function() {
    jQuery("#data-table_processing").show();

    ref.get().then(async function(snapshot) {
        var instapay = snapshot.data();
        if (instapay) {
            $("#enable_arropay_instapay").prop('checked', !!instapay.enable);
            $(".arropay_instapay_base_url").val(instapay.baseUrl || 'https://arropay.biz');
            $(".arropay_instapay_process_endpoint").val(instapay.processEndpoint || '/api/v1/payment/process/instapay');
            $(".arropay_instapay_api_key").val(instapay.apiKey || '');
            $(".arropay_instapay_api_secret").val(instapay.apiSecret || '');
            $(".arropay_instapay_callback_url").val(instapay.callbackUrl || '');
            $(".sender_account_name").val(instapay.senderAccountName || '');
            $(".sender_account_number").val(instapay.senderAccountNumber || '');
            $(".sender_mobile_number").val(instapay.senderMobileNumber || '');
            $(".sender_email").val(instapay.senderEmail || '');
            $(".sender_address").val(instapay.senderAddress || '');
            $(".sender_barangay").val(instapay.senderBarangay || '');
            $(".sender_city").val(instapay.senderCity || '');
            $(".sender_zipcode").val(instapay.senderZipcode || '');
        } else {
            $(".arropay_instapay_base_url").val('https://arropay.biz');
            $(".arropay_instapay_process_endpoint").val('/api/v1/payment/process/instapay');
        }
        jQuery("#data-table_processing").hide();
    });

    $(".edit-setting-btn").click(function() {
        ref.set({
            enable: $("#enable_arropay_instapay").is(":checked"),
            baseUrl: $(".arropay_instapay_base_url").val(),
            processEndpoint: $(".arropay_instapay_process_endpoint").val(),
            apiKey: $(".arropay_instapay_api_key").val(),
            apiSecret: $(".arropay_instapay_api_secret").val(),
            callbackUrl: $(".arropay_instapay_callback_url").val(),
            senderAccountName: $(".sender_account_name").val(),
            senderAccountNumber: $(".sender_account_number").val(),
            senderMobileNumber: $(".sender_mobile_number").val(),
            senderEmail: $(".sender_email").val(),
            senderAddress: $(".sender_address").val(),
            senderBarangay: $(".sender_barangay").val(),
            senderCity: $(".sender_city").val(),
            senderZipcode: $(".sender_zipcode").val(),
            maxAmount: 50000
        }, { merge: true }).then(function() {
            window.location.href = '{{ url("settings/payment/arropay-instapay")}}';
        });
    });
});
</script>
@endsection
