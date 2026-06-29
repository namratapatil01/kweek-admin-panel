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
                <li class="nav-item"><a class="nav-link active" href="{!! url('settings/payment/arropay-maya') !!}">{{trans('lang.arroPayMaya')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-maya-qr') !!}">{{trans('lang.arroPayMayaQr')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-instapay') !!}">{{trans('lang.arroPayInstapay')}}</a></li>
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-auth') !!}">{{trans('lang.arroPayAuth')}}</a></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_setting_arropay_maya')}}</legend>

                        <div class="form-check width-100">
                            <input type="checkbox" class="enable_arropay_maya" id="enable_arropay_maya">
                            <label class="col-3 control-label" for="enable_arropay_maya">{{trans('lang.app_setting_enable_arropay_maya')}}</label>
                        </div>

                        <div class="form-check width-100">
                            <input type="checkbox" class="prefer_checkout" id="prefer_checkout">
                            <label class="col-3 control-label" for="prefer_checkout">{{trans('lang.app_setting_arropay_maya_prefer_checkout')}}</label>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_maya_base_url')}}</label>
                            <div class="col-7"><input type="text" class="form-control arropay_maya_base_url"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_maya_api_key')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_maya_api_key"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_maya_api_secret')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_maya_api_secret"></div>
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
var database = kweekFirestore();
var ref = database.collection('settings').doc('arropay_maya_settings');

$(document).ready(function() {
    jQuery("#data-table_processing").show();

    ref.get().then(async function(snapshot) {
        var maya = snapshot.data();
        if (maya) {
            $("#enable_arropay_maya").prop('checked', !!maya.enable);
            $("#prefer_checkout").prop('checked', maya.preferCheckout !== false);
            $(".arropay_maya_base_url").val(maya.baseUrl || '');
            $(".arropay_maya_api_key").val(maya.apiKey || '');
            $(".arropay_maya_api_secret").val(maya.apiSecret || '');
        }
        jQuery("#data-table_processing").hide();
    });

    $(".edit-setting-btn").click(function() {
        ref.set({
            enable: $("#enable_arropay_maya").is(":checked"),
            preferCheckout: $("#prefer_checkout").is(":checked"),
            baseUrl: $(".arropay_maya_base_url").val(),
            apiKey: $(".arropay_maya_api_key").val(),
            apiSecret: $(".arropay_maya_api_secret").val()
        }, { merge: true }).then(function() {
            window.location.href = '{{ url("settings/payment/arropay-maya")}}';
        });
    });
});
</script>
@endsection
