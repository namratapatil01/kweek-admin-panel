<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'arropay_maya' => [
        'base_url' => env('ARROPAY_MAYA_BASE_URL', ''),
        'api_key' => env('ARROPAY_MAYA_API_KEY', ''),
        'api_secret' => env('ARROPAY_MAYA_API_SECRET', ''),
        'checkout_endpoint' => env('ARROPAY_MAYA_CHECKOUT_ENDPOINT', '/api/v1/maya/checkout'),
        'intent_endpoint' => env('ARROPAY_MAYA_INTENT_ENDPOINT', '/api/v1/maya/paymentintent'),
        'check_endpoint' => env('ARROPAY_MAYA_CHECK_ENDPOINT', '/api/v1/maya/paymentcheck'),
        'request_timeout' => (int) env('ARROPAY_MAYA_TIMEOUT', 30),
    ],
     'arropay_instapay' => [
        'base_url' => env('ARROPAY_INSTAPAY_BASE_URL', 'https://qa.arropay.biz/api/v1/'),
        'api_key' => env('ARROPAY_INSTAPAY_API_KEY', ''),
        'api_secret' => env('ARROPAY_INSTAPAY_API_SECRET', ''),
        'process_endpoint' => env('ARROPAY_INSTAPAY_PROCESS_ENDPOINT', '/api/v1/payment/process/instapay'),
        'callback_url' => env('ARROPAY_INSTAPAY_CALLBACK_URL', ''),
        'sender_account_name' => env('ARROPAY_INSTAPAY_SENDER_ACCOUNT_NAME', ''),
        'sender_account_number' => env('ARROPAY_INSTAPAY_SENDER_ACCOUNT_NUMBER', ''),
        'sender_mobile_number' => env('ARROPAY_INSTAPAY_SENDER_MOBILE_NUMBER', ''),
        'sender_email' => env('ARROPAY_INSTAPAY_SENDER_EMAIL', ''),
        'sender_address' => env('ARROPAY_INSTAPAY_SENDER_ADDRESS', ''),
        'sender_barangay' => env('ARROPAY_INSTAPAY_SENDER_BARANGAY', ''),
        'sender_city' => env('ARROPAY_INSTAPAY_SENDER_CITY', ''),
        'sender_zipcode' => env('ARROPAY_INSTAPAY_SENDER_ZIPCODE', ''),
        'request_timeout' => (int) env('ARROPAY_INSTAPAY_TIMEOUT', 30),
    ],
      'arropay_maya_qr' => [
        'base_url' => env('ARROPAY_MAYA_QR_BASE_URL', ''),
        'api_key' => env('ARROPAY_MAYA_QR_API_KEY', ''),
        'api_secret' => env('ARROPAY_MAYA_QR_API_SECRET', ''),
        'qr_endpoint' => env('ARROPAY_MAYA_QR_ENDPOINT', '/api/v1/maya/qr'),
        'check_endpoint' => env('ARROPAY_MAYA_QR_CHECK_ENDPOINT', '/api/v1/maya/paymentcheck'),
        'request_timeout' => (int) env('ARROPAY_MAYA_QR_TIMEOUT', 30),
    ],
     'arropay_auth' => [
        'base_url' => env('ARROPAY_AUTH_BASE_URL') ?: 'https://arropay.app',
        'api_key' => env('ARROPAY_AUTH_API_KEY', ''),
        'api_secret' => env('ARROPAY_AUTH_API_SECRET', ''),
        'login_endpoint' => env('ARROPAY_AUTH_LOGIN_ENDPOINT') ?: '/api/v2/auth/login',
        'gateway_secret' => env('ARROPAY_GATEWAY_SECRET') ?: '1234',
        'request_timeout' => (int) env('ARROPAY_AUTH_TIMEOUT', 30),
        'payments_table' => env('ARROPAY_PAYMENTS_TABLE', 'arropay_v2_payments'),
    ],
    'arropay_disbursement' => [
        'mode' => env('ARROPAY_DISBURSEMENT_MODE', 'local'),
        'base_url' => env('ARROPAY_DISBURSEMENT_BASE_URL')
            ?: (env('ARROPAY_AUTH_BASE_URL') ?: 'https://arropay.app'),
        'api_key' => env('ARROPAY_DISBURSEMENT_API_KEY', env('ARROPAY_AUTH_API_KEY', '')),
        'api_secret' => env('ARROPAY_DISBURSEMENT_API_SECRET', env('ARROPAY_AUTH_API_SECRET', '')),
        'banks_endpoint' => env('ARROPAY_DISBURSEMENT_BANKS_ENDPOINT', 'api/v1/disbursement/banks'),
        'initiate_endpoint' => env('ARROPAY_DISBURSEMENT_INITIATE_ENDPOINT', 'api/v1/disbursement/initiatebankwithdraw'),
        'process_endpoint' => env('ARROPAY_DISBURSEMENT_PROCESS_ENDPOINT', 'api/v1/disbursement/processbankwithdraw'),
        'request_timeout' => (int) env('ARROPAY_DISBURSEMENT_TIMEOUT', 30),
        'source_wallet_balance' => (float) env('ARROPAY_DISBURSEMENT_SOURCE_WALLET_BALANCE', 0),
        'test_otp' => env('ARROPAY_DISBURSEMENT_TEST_OTP', ''),
        'mysql_table' => env('ARROPAY_DISBURSEMENT_TABLE', 'arropay_disbursement_withdrawals'),
        'banks' => [
            'INSTAPAY' => [
                ['code' => 'GXCHPHM2', 'name' => 'GCash'],
            ],
            'PESONET' => [
                ['code' => 'GXCHPHM2', 'name' => 'GCash'],
            ],
        ],
    ],

];
