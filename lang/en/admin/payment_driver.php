<?php

declare(strict_types=1);

return [
    'model_label' => 'Payment Driver',
    'plural_model_label' => 'Payments',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'options_provider' => 'Payment Provider',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter Payment Driver Name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter Introduction',
        ],
        'options' => [
            'provider' => [
                'label' => 'Payment Driver',
                'placeholder' => 'Please select payment driver',
            ],
            'notify_url' => [
                'label' => 'Asynchronous Callback URL',
            ],
        ],
        'alipay_options' => [
            'mode' => [
                'label' => 'Mode',
                'placeholder' => 'Please select mode',
                'options' => [
                    'normal' => 'Normal Mode',
                    'sandbox' => 'Sandbox Mode',
                    'service' => 'Service Provider Mode',
                ],
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
            'app_secret_cert' => [
                'label' => 'App Private Key Certificate',
                'placeholder' => 'Enter App Private Key Certificate Content',
            ],
            'app_public_cert_path' => [
                'label' => 'App Public Key Certificate',
                'placeholder' => 'Please upload App Public Key Certificate',
                'helper_text' => 'For example: appCertPublicKey_2016082000295641.crt',
            ],
            'alipay_public_cert_path' => [
                'label' => 'Alipay Public Key Certificate',
                'placeholder' => 'Please upload Alipay Public Key Certificate',
                'helper_text' => 'For example: alipayCertPublicKey_RSA2.crt',
            ],
            'alipay_root_cert_path' => [
                'label' => 'Alipay Root Certificate',
                'placeholder' => 'Please upload Alipay Root Certificate',
                'helper_text' => 'For example: alipayRootCert.crt',
            ],
            'app_auth_token' => [
                'label' => 'Third-Party App Authorization Token',
                'placeholder' => 'Optional, third-party app authorization token',
            ],
            'service_provider_id' => [
                'label' => 'Service Provider ID',
                'placeholder' => 'Optional, service provider ID. Required in service provider mode',
            ],
        ],
        'wechat_options' => [
            'mode' => [
                'label' => 'Mode',
                'placeholder' => 'Please select mode',
                'options' => [
                    'normal' => 'Normal Mode',
                    'service' => 'Service Provider Mode',
                ],
            ],
            'mch_id' => [
                'label' => 'Merchant ID',
                'placeholder' => 'Enter Merchant ID. In service provider mode, enter the service provider’s Merchant ID',
            ],
            'mch_secret_key_v2' => [
                'label' => 'Merchant Secret Key (V2)',
                'placeholder' => 'Optional, Merchant Secret Key (V2)',
            ],
            'mch_secret_key' => [
                'label' => 'Merchant Secret Key (V3)',
                'placeholder' => 'Enter Merchant Secret Key (V3). Can be set in Account Center -> API Security',
            ],
            'mch_secret_cert' => [
                'label' => 'Merchant Private Key Certificate',
                'placeholder' => 'Please upload Merchant Private Key Certificate',
                'helper_text' => 'Can be obtained in Account Center -> API Security -> Apply for API Certificate, for example: apiclient_key.pem',
            ],
            'mch_public_cert_path' => [
                'label' => 'Merchant Public Key Certificate',
                'placeholder' => 'Please upload Merchant Public Key Certificate',
                'helper_text' => 'Can be obtained in Account Center -> API Security -> Apply for API Certificate, for example: apiclient_cert.pem',
            ],
            'mp_app_id' => [
                'label' => 'Public Account App ID',
                'placeholder' => 'Optional, Public Account App ID',
            ],
            'mini_app_id' => [
                'label' => 'Mini Program App ID',
                'placeholder' => 'Optional, Mini Program App ID',
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Optional, App’s App ID',
            ],
            'sub_mp_app_id' => [
                'label' => 'Sub Public Account App ID',
                'placeholder' => 'Optional, Service Provider Mode Sub Public Account App ID',
            ],
            'sub_app_id' => [
                'label' => 'Sub App\'s App ID',
                'placeholder' => 'Optional, Service Provider Mode Sub App\'s App ID',
            ],
            'sub_mini_app_id' => [
                'label' => 'Sub Mini Program App ID',
                'placeholder' => 'Optional, Service Provider Mode Sub Mini Program App ID',
            ],
            'sub_mch_id' => [
                'label' => 'Sub Merchant ID',
                'placeholder' => 'Optional, Service Provider Mode Sub Merchant ID',
            ],
            'wechat_public_cert_id' => [
                'label' => 'WeChat platform public key certificate ID',
                'placeholder' => 'WeChat platform public key certificate ID',
                'helper_text' => 'Log in to the merchant platform under [API Security] -> [API Certificate] -> [View Certificate] to view the merchant\'s API certificate ID. For example: PUB_KEY_ID_0000000000000024101100397200000006',
            ],
            'wechat_public_cert_path' => [
                'label' => 'WeChat Platform Public Key Certificate',
                'placeholder' => 'WeChat Platform Public Key Certificate',
                'helper_text' => 'For example: wechatPublicKey.crt',
            ],
        ],
        'unipay_options' => [
            'mch_id' => [
                'label' => 'Merchant ID',
                'placeholder' => 'Enter Merchant ID',
            ],
            'mch_secret_key' => [
                'label' => 'Merchant Secret Key',
                'placeholder' => 'Enter Merchant Secret Key',
                'helper_text' => 'UnionPay Barcode Payment Comprehensive Frontend Platform Configuration: https://up.95516.com/open/openapi?code=unionpay',
            ],
            'mch_cert_path' => [
                'label' => 'Merchant Public/Private Key',
                'placeholder' => 'Please upload Merchant Public/Private Key',
                'helper_text' => 'For example: unipayAppCert.pfx',
            ],
            'mch_cert_password' => [
                'label' => 'Merchant Public/Private Key Password',
                'placeholder' => 'Enter Merchant Public/Private Key Password',
            ],
            'unipay_public_cert_path' => [
                'label' => 'UnionPay Public Key Certificate',
                'placeholder' => 'Please upload UnionPay Public Key Certificate',
                'helper_text' => 'For example: unipayCertPublicKey.cer',
            ],
        ],
        'paypal_options' => [
            'mode' => [
                'label' => 'Mode',
                'placeholder' => 'Please select mode',
                'options' => [
                    'live' => 'Live Mode',
                    'sandbox' => 'Sandbox Mode',
                ],
            ],
            'client_id' => [
                'label' => 'Client ID',
                'placeholder' => 'Enter Client ID',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => 'Enter Client Secret',
            ],
            'currency' => [
                'label' => 'Currency Code',
                'placeholder' => 'Enter Currency Code',
                'helper_text' => 'Visit https://developer.paypal.com/api/rest/reference/currency-codes to view all currency codes.',
            ],
            'webhook_id' => [
                'label' => 'Webhook ID',
                'placeholder' => 'Enter Webhook ID',
                'helper_text' => 'Visit https://developer.paypal.com/dashboard/applications to configure Webhooks for the specified application.',
            ],
        ],
        'epay_options' => [
            'api_url' => [
                'label' => 'Payment API URL',
                'placeholder' => 'Enter the complete payment API URL, e.g.: https://pay.example.com',
            ],
            'pid' => [
                'label' => 'Merchant ID',
                'placeholder' => 'Enter the Merchant ID, e.g.: 1000',
            ],
            'platform_public_key' => [
                'label' => 'Platform Public Key',
                'placeholder' => 'Enter the platform public key string',
            ],
            'merchant_private_key' => [
                'label' => 'Merchant Private Key',
                'placeholder' => 'Enter the merchant private key string',
            ],
            'channels' => [
                'label' => 'Payment Channels',
                'helper_text' => 'Select the payment channels supported by this platform',
            ],
        ],
    ],
];