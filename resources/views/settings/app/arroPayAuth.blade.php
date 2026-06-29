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
                <li class="nav-item"><a class="nav-link" href="{!! url('settings/payment/arropay-instapay') !!}">{{trans('lang.arroPayInstapay')}}</a></li>
                <li class="nav-item"><a class="nav-link active" href="{!! url('settings/payment/arropay-auth') !!}">{{trans('lang.arroPayAuth')}}</a></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="row vendor_payout_create">
                <div class="vendor_payout_create-inner">
                    <fieldset>
                        <legend>{{trans('lang.app_setting_arropay_auth')}}</legend>
                        <p class="text-muted">{{trans('lang.app_setting_arropay_auth_help')}}</p>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_base_url')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control arropay_auth_base_url" placeholder="https://arropay.app">
                                <div class="form-text text-muted">{{trans('lang.app_setting_arropay_auth_base_url_help')}}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_login_endpoint')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control arropay_auth_login_endpoint" placeholder="/api/v2/auth/login">
                                <div class="form-text text-muted">{{trans('lang.app_setting_arropay_auth_login_endpoint_help')}}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_api_key')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_auth_api_key"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_api_secret')}}</label>
                            <div class="col-7"><input type="password" class="form-control arropay_auth_api_secret"></div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_token')}}</label>
                            <div class="col-7">
                                <textarea class="form-control arropay_auth_token" rows="3" readonly></textarea>
                                <div class="form-text text-muted">{{trans('lang.app_setting_arropay_auth_token_help')}}</div>
                            </div>
                        </div>

                        <div class="form-group row width-100">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_last_login')}}</label>
                            <div class="col-7">
                                <input type="text" class="form-control arropay_auth_last_login" readonly>
                            </div>
                        </div>

                        <div class="form-group row width-100 d-none" id="arropay_auth_status_wrap">
                            <label class="col-3 control-label">{{trans('lang.app_setting_arropay_auth_status')}}</label>
                            <div class="col-7">
                                <div class="alert mb-0" id="arropay_auth_status" role="alert"></div>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

        <div class="form-group col-12 text-center btm-btn" style="margin-bottom:inherit;">
            <button type="button" class="btn btn-success arropay-auth-login-btn"><i class="fa fa-sign-in"></i> {{trans('lang.app_setting_arropay_auth_login')}}</button>
            <button type="button" class="btn btn-primary edit-setting-btn"><i class="fa fa-save"></i> {{trans('lang.save')}}</button>
            <a href="{{url('/dashboard')}}" class="btn btn-default"><i class="fa fa-undo"></i>{{trans('lang.cancel')}}</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
var database = firebase.firestore();
var ref = database.collection('settings').doc('arropay_auth_settings');
var loginUrl = '{{ url("settings/payment/arropay-auth/login") }}';

function showAuthStatus(message, type) {
    $("#arropay_auth_status_wrap").removeClass('d-none');
    $("#arropay_auth_status")
        .removeClass('alert-success alert-danger alert-info')
        .addClass('alert-' + type)
        .text(message);
}

function saveToFirestore(extra) {
    var payload = {
        baseUrl: $(".arropay_auth_base_url").val(),
        loginEndpoint: $(".arropay_auth_login_endpoint").val(),
        apiKey: $(".arropay_auth_api_key").val(),
        apiSecret: $(".arropay_auth_api_secret").val(),
        token: $(".arropay_auth_token").val(),
        lastLoginAt: $(".arropay_auth_last_login").val()
    };

    if (extra) {
        payload = Object.assign(payload, extra);
    }

    return ref.set(payload, { merge: true });
}

$(document).ready(function() {
    jQuery("#data-table_processing").show();

    ref.get().then(async function(snapshot) {
        var auth = snapshot.data();
        if (auth) {
            $(".arropay_auth_base_url").val(auth.baseUrl || 'https://arropay.app');
            $(".arropay_auth_login_endpoint").val(auth.loginEndpoint || '/api/v2/auth/login');
            $(".arropay_auth_api_key").val(auth.apiKey || '');
            $(".arropay_auth_api_secret").val(auth.apiSecret || '');
            $(".arropay_auth_token").val(auth.token || '');
            $(".arropay_auth_last_login").val(auth.lastLoginAt || '');
        } else {
            $(".arropay_auth_base_url").val('https://arropay.app');
            $(".arropay_auth_login_endpoint").val('/api/v2/auth/login');
        }
        jQuery("#data-table_processing").hide();
    });

    $(".arropay-auth-login-btn").click(function() {
        var baseUrl = $(".arropay_auth_base_url").val();
        var loginEndpoint = $(".arropay_auth_login_endpoint").val();
        var apiKey = $(".arropay_auth_api_key").val();
        var apiSecret = $(".arropay_auth_api_secret").val();

        if (!baseUrl || !apiKey || !apiSecret) {
            showAuthStatus('{{ trans("lang.app_setting_arropay_auth_required_fields") }}', 'danger');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);
        showAuthStatus('{{ trans("lang.app_setting_arropay_auth_logging_in") }}', 'info');

        $.ajax({
            url: loginUrl,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: {
                baseUrl: baseUrl,
                loginEndpoint: loginEndpoint,
                apiKey: apiKey,
                apiSecret: apiSecret
            },
            success: function(response) {
                if (!response.success || !response.token) {
                    showAuthStatus('{{ trans("lang.app_setting_arropay_auth_login_failed") }}', 'danger');
                    return;
                }

                var lastLoginAt = new Date().toISOString();
                $(".arropay_auth_token").val(response.token);
                $(".arropay_auth_last_login").val(lastLoginAt);

                saveToFirestore({
                    tokenExpiresAt: response.expires_at || null,
                    lastLoginAt: lastLoginAt
                }).then(function() {
                    showAuthStatus('{{ trans("lang.app_setting_arropay_auth_login_success") }}', 'success');
                }).catch(function() {
                    showAuthStatus('{{ trans("lang.app_setting_arropay_auth_save_failed") }}', 'danger');
                });
            },
            error: function(xhr) {
                var message = '{{ trans("lang.app_setting_arropay_auth_login_failed") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAuthStatus(message, 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    $(".edit-setting-btn").click(function() {
        saveToFirestore().then(function() {
            window.location.href = '{{ url("settings/payment/arropay-auth") }}';
        });
    });
});
</script>
@endsection
