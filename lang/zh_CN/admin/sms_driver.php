<?php

declare(strict_types=1);

return [
    'model_label' => '短信驱动器',
    'plural_model_label' => '短信',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'options_provider' => '发信网关',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => '驱动名称',
            'placeholder' => '输入驱动名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options' => [
            'provider' => [
                'label' => '发信网关',
                'placeholder' => '请选择发信网关'
            ],
            'templates' => [
                'label' => '短信模板',
                'event' => [
                    'label' => '事件',
                ],
                'template' => [
                    'label' => '短信模板',
                    'placeholder' => '可为空，输入短信模板，例如：SMS_001',
                ],
                'content' => [
                    'label' => '短信内容',
                    'placeholder' => '必填，输入短信内容，例如：您的验证码是：{code}，请勿泄露给他人',
                ]
            ],
        ],
        'aliyun_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => '输入 Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => '输入 Access Key Secret',
            ],
            'sign_name' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'aliyunrest_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
            'app_secret_key' => [
                'label' => 'App Secret Key',
                'placeholder' => '输入 App Secret Key',
            ],
            'sign_name' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'aliyunintl_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => '输入 Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => '输入 Access Key Secret',
            ],
            'sign_name' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'yunpian_options' => [
            'api_key' => [
                'label' => 'Api Key',
                'placeholder' => '输入 Api Key',
            ],
            'signature' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'submail_options' => [
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => '输入 App ID',
            ],
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
            'project' => [
                'label' => 'Project',
                'placeholder' => '输入 Project',
            ],
        ],
        'luosimao_options' => [
            'api_key' => [
                'label' => 'Api Key',
                'placeholder' => '输入 Api Key',
            ],
        ],
        'yuntongxun_options' => [
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => '输入 App ID',
            ],
            'account_sid' => [
                'label' => 'Account SID',
                'placeholder' => '输入 Account SID',
            ],
            'account_token' => [
                'label' => 'Account Token',
                'placeholder' => '输入 Account Token',
            ],
            'is_sub_account' => [
                'label' => '是否子账号',
            ],
        ],
        'huyi_options' => [
            'api_id' => [
                'label' => 'Api ID',
                'placeholder' => '输入 Api ID',
            ],
            'api_key' => [
                'label' => 'Api Key',
                'placeholder' => '输入 Api Key',
            ],
            'signature' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'juhe_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
        ],
        'sendcloud_options' => [
            'sms_user' => [
                'label' => 'SMS User',
                'placeholder' => '输入 SMS User',
            ],
            'sms_key' => [
                'label' => 'SMS Key',
                'placeholder' => '输入 SMS Key',
            ],
            'timestamp' => [
                'label' => '是否启用时间戳',
            ],
        ],
        'baidu_options' => [
            'ak' => [
                'label' => 'AK',
                'placeholder' => '输入 AK',
            ],
            'sk' => [
                'label' => 'SK',
                'placeholder' => '输入 SK',
            ],
            'invoke_id' => [
                'label' => 'Invoke ID',
                'placeholder' => '输入 Invoke ID',
            ],
            'domain' => [
                'label' => 'Domain',
                'placeholder' => '输入 Domain',
            ],
        ],
        'huaxin_options' => [
            'user_id' => [
                'label' => 'User ID',
                'placeholder' => '输入 User ID',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => '输入 Password',
            ],
            'account' => [
                'label' => 'Account',
                'placeholder' => '输入 Account',
            ],
            'ip' => [
                'label' => 'IP',
                'placeholder' => '输入 IP',
            ],
            'ext_no' => [
                'label' => 'Ext No',
                'placeholder' => '输入 Ext No',
            ],
        ],
        'chuanglan_options' => [
            'account' => [
                'label' => 'Account',
                'placeholder' => '输入 Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => '输入 Password',
            ],
            'intel_account' => [
                'label' => 'Intel Account',
                'placeholder' => '输入 Intel Account',
            ],
            'intel_password' => [
                'label' => 'Intel Password',
                'placeholder' => '输入 Intel Password',
            ],
            'channel' => [
                'label' => '发信通道',
                'placeholder' => '选择发信通道',
                'options' => [
                    'validate' => '验证码通道',
                    'promotion' => '会员营销通道',
                ],
            ],
            'sign' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
            'unsubscribe' => [
                'label' => '短信退订',
                'placeholder' => '输入短信退订',
            ],
        ],
        'chuanglanv1_options' => [
            'account' => [
                'label' => 'Account',
                'placeholder' => '输入 Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => '输入 Password',
            ],
            'needstatus' => [
                'label' => 'Need status',
            ],
            'channel' => [
                'label' => '发信通道',
                'placeholder' => '选择发信通道',
                'options' => [
                    'validate' => '验证码通道',
                    'promotion' => '会员营销通道',
                ],
            ],
        ],
        'rongcloud_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => '输入 App Secret',
            ],
        ],
        'tianyiwuxian_options' => [
            'username' => [
                'label' => '用户名',
                'placeholder' => '输入用户名',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
            'gwid' => [
                'label' => '网关 ID',
                'placeholder' => '输入网关 ID',
            ]
        ],
        'twilio_options' => [
            'account_sid' => [
                'label' => 'Account SID',
                'placeholder' => '输入 Account SID',
            ],
            'from' => [
                'label' => '发送的号码',
                'placeholder' => '输入发送的号码',
            ],
            'token' => [
                'label' => 'Token',
                'placeholder' => '输入 Token',
            ],
        ],
        'tiniyo_options' => [
            'account_sid' => [
                'label' => 'Auth ID',
                'placeholder' => '输入 Auth ID',
            ],
            'from' => [
                'label' => '发送的号码',
                'placeholder' => '输入发送的号码',
            ],
            'token' => [
                'label' => 'Auth Secret',
                'placeholder' => '输入 Auth Secret',
            ],
        ],
        'qcloud_options' => [
            'sdk_app_id' => [
                'label' => 'SDK App ID',
                'placeholder' => '输入 SDK App ID',
            ],
            'secret_id' => [
                'label' => 'Secret ID',
                'placeholder' => '输入 Secret ID',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => '输入 Secret Key',
            ],
            'sign_name' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'huawei_options' => [
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => '输入 Endpoint',
            ],
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => '输入 App Secret',
            ],
            'from_default' => [
                'label' => '签名通道号',
                'placeholder' => '输入签名通道号',
            ],
        ],
        'yunxin_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => '输入 App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => '输入 App Secret',
            ],
            'need_up' => [
                'label' => '是否需要支持短信上行',
                'placeholder' => '输入是否需要支持短信上行',
            ],
        ],
        'yunzhixun_options' => [
            'sid' => [
                'label' => 'SID',
                'placeholder' => '输入 SID',
            ],
            'token' => [
                'label' => 'Token',
                'placeholder' => '输入 Token',
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => '输入 App ID',
            ],
        ],
        'kingtto_options' => [
            'userid' => [
                'label' => 'User ID',
                'placeholder' => '输入 User ID',
            ],
            'account' => [
                'label' => 'Account',
                'placeholder' => '输入 Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => '输入 Password',
            ],
        ],
        'qiniu_options' => [
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => '输入 Secret Key',
            ],
            'access_key' => [
                'label' => 'Access Key',
                'placeholder' => '输入 Access Key',
            ],
        ],
        'ucloud_options' => [
            'private_key' => [
                'label' => 'Private Key',
                'placeholder' => '输入 Private Key',
            ],
            'public_key' => [
                'label' => 'Public Key',
                'placeholder' => '输入 Public Key',
            ],
            'sig_content' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
            'project_id' => [
                'label' => '项目 ID',
                'placeholder' => '输入项目 ID',
            ],
        ],
        'smsbao_options' => [
            'user' => [
                'label' => '账号',
                'placeholder' => '输入账号',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ]
        ],
        'moduyun_options' => [
            'accesskey' => [
                'label' => 'Access Key',
                'placeholder' => '输入 Access Key',
            ],
            'secretkey' => [
                'label' => 'Secret Key',
                'placeholder' => '输入 Secret Key',
            ],
            'sign_id'    => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
            'type' => [
                'label' => '短信类型',
                'placeholder' => '选择短信类型',
                'options' => [
                    'normal' => '普通短信',
                    'marketing' => '营销短信',
                ]
            ],
        ],
        'rongheyun_options' => [
            'username' => [
                'label' => '用户名',
                'placeholder' => '输入用户名',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
            'signature'=> [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'zzyun_options' => [
            'user_id' => [
                'label' => '会员 ID',
                'placeholder' => '输入会员 ID',
            ],
            'secret' => [
                'label' => '密钥',
                'placeholder' => '输入密钥',
            ],
            'sign_name'=> [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
        ],
        'maap_options' => [
            'cpcode' => [
                'label' => '商户编码',
                'placeholder' => '输入商户编码',
            ],
            'key' => [
                'label' => '密钥',
                'placeholder' => '输入密钥',
            ],
            'excode'=> [
                'label' => '扩展名',
                'placeholder' => '输入扩展名',
            ],
        ],
        'tinree_options' => [
            'accesskey' => [
                'label' => 'Access Key',
                'placeholder' => '输入 Access Key',
            ],
            'secret' => [
                'label' => 'Secret',
                'placeholder' => '输入 Secret',
            ],
            'sign' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名或签名 ID',
            ],
        ],
        'nowcn_options' => [
            'key'  => [
                'label' => '用户 ID',
                'placeholder' => '输入用户 ID',
            ],
            'secret'   => [
                'label' => '开发密钥',
                'placeholder' => '输入开发密钥',
            ],
            'api_type'  => [
                'label' => '短信通道',
                'placeholder' => '输入短信通道',
            ],
        ],
        'volcengine_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => '输入 Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => '输入 Access Key Secret',
            ],
            'region_id' => [
                'label' => 'Region ID',
                'placeholder' => '输入 Region ID',
            ],
            'sign_name' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名或签名 ID',
            ],
            'sms_account' => [
                'label' => '消息组账号',
                'placeholder' => '输入消息组账号，火山短信页面右上角，短信应用括号中的字符串',
            ],
        ],
        'yidongmasblack_options' => [
            'ec_name' => [
                'label' => '机构名称',
                'placeholder' => '输入机构名称',
            ],
            'secret_key' => [
                'label' => '密钥',
                'placeholder' => '输入密钥',
            ],
            'ap_id' => [
                'label' => '应用 ID',
                'placeholder' => '输入应用 ID',
            ],
            'sign' => [
                'label' => '短信签名',
                'placeholder' => '输入短信签名',
            ],
            'add_serial' => [
                'label' => '通道号',
                'placeholder' => '输入通道号',
            ],
        ],
    ],
    'events' => [
        'register' => '注册',
        'login' => '登录',
        'forget_password' => '找回密码',
        'bind' => '绑定手机号',
        'verify' => '验证手机号',
    ]
];
