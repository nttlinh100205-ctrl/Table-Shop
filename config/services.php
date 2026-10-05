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

    'ghn' => [
        'base_url'          => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api'),
        'token'             => env('GHN_TOKEN', '391561a1-aa86-11f1-a973-aee5264794df'),
        'shop_id'           => env('GHN_SHOP_ID', 217485),
        'verify_ssl'        => env('GHN_VERIFY_SSL', false),
        'from_name'         => env('GHN_FROM_NAME', 'Table-Store'),
        'from_phone'        => env('GHN_FROM_PHONE', '0346222645'),
        'from_address'      => env('GHN_FROM_ADDRESS', '43/58 Trần Bình, Phường Mai Dịch, Quận Cầu Giấy, Hà Nội'),
        'from_district_id'  => env('GHN_FROM_DISTRICT_ID', 1485),
        'from_ward_code'    => env('GHN_FROM_WARD_CODE', '1A0603'),
        'default_weight'    => env('GHN_DEFAULT_WEIGHT', 15000),
    ],
 'momo' => [
        'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMOBKUN20180529'),
        'access_key'   => env('MOMO_ACCESS_KEY', 'klm05TvNBzhg7h7j'),
        'secret_key'   => env('MOMO_SECRET_KEY', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'),
        'verify_ssl'   => env('MOMO_VERIFY_SSL', false),
        'redirect_url' => env('MOMO_REDIRECT_URL'), // null → route user.payment.momo.callback
        'ipn_url'      => env('MOMO_IPN_URL'),      // null → route payment.momo.ipn
    ],

    'cloudinary' => [
        'url'        => env('CLOUDINARY_URL'),
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

];
