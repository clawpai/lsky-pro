<?php

declare(strict_types=1);

return [
    'model_label' => '支付驱动器',
    'plural_model_label' => '支付',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'options_provider' => '支付驱动',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => '驱动名称',
            'placeholder' => '输入支付驱动名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options' => [
            'provider' => [
                'label' => '支付驱动',
                'placeholder' => '请选择支付驱动'
            ],
            'notify_url' => [
                'label' => '支付结果异步回调通知地址',
            ]
        ],
        'alipay_options' => [
            'mode' => [
                'label' => '模式',
                'placeholder' => '请选择模式',
                'options' => [
                    'normal' => '正常模式',
                    'sandbox' => '沙箱模式',
                    'service' => '服务商模式',
                ],
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => '请输入 App ID',
            ],
            'app_secret_cert' => [
                'label' => '应用私钥证书',
                'placeholder' => '请输入应用私钥证书内容',
            ],
            'app_public_cert_path' => [
                'label' => '应用公钥证书',
                'placeholder' => '请上传应用公钥证书',
                'helper_text' => '例如：appCertPublicKey_2016082000295641.crt',
            ],
            'alipay_public_cert_path' => [
                'label' => '支付宝公钥证书',
                'placeholder' => '请上传支付宝公钥证书',
                'helper_text' => '例如：alipayCertPublicKey_RSA2.crt',
            ],
            'alipay_root_cert_path' => [
                'label' => '支付宝根证书',
                'placeholder' => '请上传支付宝根证书',
                'helper_text' => '例如：alipayRootCert.crt',
            ],
            'app_auth_token' => [
                'label' => '第三方应用授权 token',
                'placeholder' => '选填，第三方应用授权 token',
            ],
            'service_provider_id' => [
                'label' => '服务提供者 ID',
                'placeholder' => '选填，服务商 ID，服务商模式时必填',
            ],
        ],
        'wechat_options' => [
            'mode' => [
                'label' => '模式',
                'placeholder' => '请选择模式',
                'options' => [
                    'normal' => '正常模式',
                    'service' => '服务商模式',
                ],
            ],
            'mch_id' => [
                'label' => '商户号',
                'placeholder' => '请输入商户号，服务商模式下为服务商商户号',
            ],
            'mch_secret_key_v2' => [
                'label' => '商户密钥（V2）',
                'placeholder' => '选填，商户密钥（V2）',
            ],
            'mch_secret_key' => [
                'label' => '商户密钥（V3）',
                'placeholder' => '请输入商户密钥（V3），可在 账户中心->API安全 中设置',
            ],
            'mch_secret_cert' => [
                'label' => '商户私钥证书',
                'placeholder' => '请上传商户私钥证书',
                'helper_text' => '可在 账户中心->API安全->申请API证书 里获得，例如：apiclient_key.pem',
            ],
            'mch_public_cert_path' => [
                'label' => '商户公钥证书',
                'placeholder' => '请上传商户公钥证书',
                'helper_text' => '可在 账户中心->API安全->申请API证书 里获得，例如：apiclient_cert.pem',
            ],
            'mp_app_id' => [
                'label' => '公众号 App ID',
                'placeholder' => '选填，公众号 App ID',
            ],
            'mini_app_id' => [
                'label' => '小程序 App ID',
                'placeholder' => '选填，小程序 App ID',
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => '选填，App 的 App ID',
            ],
            'sub_mp_app_id' => [
                'label' => '子公众号 App ID',
                'placeholder' => '选填，服务商模式子公众号 App ID',
            ],
            'sub_app_id' => [
                'label' => '子 APP 的 App ID',
                'placeholder' => '选填，服务商模式子 APP 的 App ID',
            ],
            'sub_mini_app_id' => [
                'label' => '子小程序 App ID',
                'placeholder' => '选填，服务商模式子小程序 App ID',
            ],
            'sub_mch_id' => [
                'label' => '子商户号',
                'placeholder' => '选填，服务商模式子商户号',
            ],
            'wechat_public_cert_id' => [
                'label' => '微信平台公钥证书 ID',
                'placeholder' => '微信平台公钥证书 ID',
                'helper_text' => '登录商户平台【API安全】->【API证书】->【查看证书】，可查看商户 API 证书 ID。例如：PUB_KEY_ID_0000000000000024101100397200000006',
            ],
            'wechat_public_cert_path' => [
                'label' => '微信平台公钥证书',
                'placeholder' => '微信平台公钥证书',
                'helper_text' => '例如：wechatPublicKey.crt',
            ],
        ],
        'unipay_options' => [
            'mch_id' => [
                'label' => '商户号',
                'placeholder' => '请输入商户号',
            ],
            'mch_secret_key' => [
                'label' => '商户密钥',
                'placeholder' => '请输入商户密钥',
                'helper_text' => '银联条码支付综合前置平台配置：https://up.95516.com/open/openapi?code=unionpay',
            ],
            'mch_cert_path' => [
                'label' => '商户公私钥',
                'placeholder' => '请上传商户公私钥',
                'helper_text' => '例如：unipayAppCert.pfx'
            ],
            'mch_cert_password' => [
                'label' => '商户公私钥密码',
                'placeholder' => '请输入商户公私钥密码',
            ],
            'unipay_public_cert_path' => [
                'label' => '银联公钥证书',
                'placeholder' => '请上传银联公钥证书',
                'helper_text' => '例如：unipayCertPublicKey.cer',
            ],
        ],
        'paypal_options' => [
            'mode' => [
                'label' => '模式',
                'placeholder' => '请选择模式',
                'options' => [
                    'live' => '正常模式',
                    'sandbox' => '沙箱模式',
                ],
            ],
            'client_id' => [
                'label' => 'Client ID',
                'placeholder' => '请输入 Client ID',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => '请输入 Client Secret',
            ],
            'currency' => [
                'label' => '货币代码',
                'placeholder' => '请输入货币代码',
                'helper_text' => '访问 https://developer.paypal.com/api/rest/reference/currency-codes 查看所有货币代码。',
            ],
            'webhook_id' => [
                'label' => 'Webhook ID',
                'placeholder' => '请输入 Webhook ID',
                'helper_text' => '访问 https://developer.paypal.com/dashboard/applications 配置指定应用的 Webhook。',
            ]
        ],
        'epay_options' => [
            'api_url' => [
                'label' => '支付接口地址',
                'placeholder' => '请输入完整的支付接口地址，e.g：https://pay.example.com',
            ],
            'pid' => [
                'label' => '商户 ID',
                'placeholder' => '请输入商户ID，e.g：1000',
            ],
            'platform_public_key' => [
                'label' => '平台公钥',
                'placeholder' => '请输入平台公钥字符串',
            ],
            'merchant_private_key' => [
                'label' => '商户私钥',
                'placeholder' => '请输入商户私钥字符串',
            ],
            'channels' => [
                'label' => '支付通道',
                'helper_text' => '选择该平台支持的支付通道',
            ],
        ]
    ],
];
