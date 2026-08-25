<?php

declare(strict_types=1);

return [
    'model_label' => 'SMS Driver',
    'plural_model_label' => 'SMS',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'options_provider' => 'SMS Gateway',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter driver name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'options' => [
            'provider' => [
                'label' => 'SMS Gateway',
                'placeholder' => 'Please select SMS gateway',
            ],
            'templates' => [
                'label' => 'SMS Templates',
                'event' => [
                    'label' => 'Event',
                ],
                'template' => [
                    'label' => 'SMS Template',
                    'placeholder' => 'Can be empty, enter SMS template, e.g. SMS_001',
                ],
                'content' => [
                    'label' => 'SMS Content',
                    'placeholder' => 'Required, enter the content of the SMS, e.g. your verification code is: {code}, please do not disclose to others',
                ],
            ],
        ],
        'aliyun_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => 'Enter Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => 'Enter Access Key Secret',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'aliyunrest_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
            'app_secret_key' => [
                'label' => 'App Secret Key',
                'placeholder' => 'Enter App Secret Key',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'aliyunintl_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => 'Enter Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => 'Enter Access Key Secret',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'yunpian_options' => [
            'api_key' => [
                'label' => 'API Key',
                'placeholder' => 'Enter API Key',
            ],
            'signature' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'submail_options' => [
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
            'project' => [
                'label' => 'Project',
                'placeholder' => 'Enter Project',
            ],
        ],
        'luosimao_options' => [
            'api_key' => [
                'label' => 'API Key',
                'placeholder' => 'Enter API Key',
            ],
        ],
        'yuntongxun_options' => [
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
            'account_sid' => [
                'label' => 'Account SID',
                'placeholder' => 'Enter Account SID',
            ],
            'account_token' => [
                'label' => 'Account Token',
                'placeholder' => 'Enter Account Token',
            ],
            'is_sub_account' => [
                'label' => 'Is Sub-account',
            ],
        ],
        'huyi_options' => [
            'api_id' => [
                'label' => 'API ID',
                'placeholder' => 'Enter API ID',
            ],
            'api_key' => [
                'label' => 'API Key',
                'placeholder' => 'Enter API Key',
            ],
            'signature' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'juhe_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
        ],
        'sendcloud_options' => [
            'sms_user' => [
                'label' => 'SMS User',
                'placeholder' => 'Enter SMS User',
            ],
            'sms_key' => [
                'label' => 'SMS Key',
                'placeholder' => 'Enter SMS Key',
            ],
            'timestamp' => [
                'label' => 'Enable Timestamp',
            ],
        ],
        'baidu_options' => [
            'ak' => [
                'label' => 'AK',
                'placeholder' => 'Enter AK',
            ],
            'sk' => [
                'label' => 'SK',
                'placeholder' => 'Enter SK',
            ],
            'invoke_id' => [
                'label' => 'Invoke ID',
                'placeholder' => 'Enter Invoke ID',
            ],
            'domain' => [
                'label' => 'Domain',
                'placeholder' => 'Enter Domain',
            ],
        ],
        'huaxin_options' => [
            'user_id' => [
                'label' => 'User ID',
                'placeholder' => 'Enter User ID',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'account' => [
                'label' => 'Account',
                'placeholder' => 'Enter Account',
            ],
            'ip' => [
                'label' => 'IP',
                'placeholder' => 'Enter IP',
            ],
            'ext_no' => [
                'label' => 'Ext No',
                'placeholder' => 'Enter Ext No',
            ],
        ],
        'chuanglan_options' => [
            'account' => [
                'label' => 'Account',
                'placeholder' => 'Enter Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'intel_account' => [
                'label' => 'Intel Account',
                'placeholder' => 'Enter Intel Account',
            ],
            'intel_password' => [
                'label' => 'Intel Password',
                'placeholder' => 'Enter Intel Password',
            ],
            'channel' => [
                'label' => 'SMS Channel',
                'placeholder' => 'Select SMS channel',
                'options' => [
                    'validate' => 'Verification Code Channel',
                    'promotion' => 'Member Marketing Channel',
                ],
            ],
            'sign' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
            'unsubscribe' => [
                'label' => 'SMS Unsubscribe',
                'placeholder' => 'Enter SMS unsubscribe',
            ],
        ],
        'chuanglanv1_options' => [
            'account' => [
                'label' => 'Account',
                'placeholder' => 'Enter Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'needstatus' => [
                'label' => 'Need Status',
            ],
            'channel' => [
                'label' => 'SMS Channel',
                'placeholder' => 'Select SMS channel',
                'options' => [
                    'validate' => 'Verification Code Channel',
                    'promotion' => 'Member Marketing Channel',
                ],
            ],
        ],
        'rongcloud_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => 'Enter App Secret',
            ],
        ],
        'tianyiwuxian_options' => [
            'username' => [
                'label' => 'Username',
                'placeholder' => 'Enter Username',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'gwid' => [
                'label' => 'Gateway ID',
                'placeholder' => 'Enter Gateway ID',
            ],
        ],
        'twilio_options' => [
            'account_sid' => [
                'label' => 'Account SID',
                'placeholder' => 'Enter Account SID',
            ],
            'from' => [
                'label' => 'Sender Number',
                'placeholder' => 'Enter sender number',
            ],
            'token' => [
                'label' => 'Token',
                'placeholder' => 'Enter Token',
            ],
        ],
        'tiniyo_options' => [
            'account_sid' => [
                'label' => 'Auth ID',
                'placeholder' => 'Enter Auth ID',
            ],
            'from' => [
                'label' => 'Sender Number',
                'placeholder' => 'Enter sender number',
            ],
            'token' => [
                'label' => 'Auth Secret',
                'placeholder' => 'Enter Auth Secret',
            ],
        ],
        'qcloud_options' => [
            'sdk_app_id' => [
                'label' => 'SDK App ID',
                'placeholder' => 'Enter SDK App ID',
            ],
            'secret_id' => [
                'label' => 'Secret ID',
                'placeholder' => 'Enter Secret ID',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'huawei_options' => [
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => 'Enter Endpoint',
            ],
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => 'Enter App Secret',
            ],
            'from_default' => [
                'label' => 'Signature Channel Number',
                'placeholder' => 'Enter Signature Channel Number',
            ],
        ],
        'yunxin_options' => [
            'app_key' => [
                'label' => 'App Key',
                'placeholder' => 'Enter App Key',
            ],
            'app_secret' => [
                'label' => 'App Secret',
                'placeholder' => 'Enter App Secret',
            ],
            'need_up' => [
                'label' => 'Need SMS Uplink Support',
                'placeholder' => 'Specify whether SMS uplink support is needed',
            ],
        ],
        'yunzhixun_options' => [
            'sid' => [
                'label' => 'SID',
                'placeholder' => 'Enter SID',
            ],
            'token' => [
                'label' => 'Token',
                'placeholder' => 'Enter Token',
            ],
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
        ],
        'kingtto_options' => [
            'userid' => [
                'label' => 'User ID',
                'placeholder' => 'Enter User ID',
            ],
            'account' => [
                'label' => 'Account',
                'placeholder' => 'Enter Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
        ],
        'qiniu_options' => [
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'access_key' => [
                'label' => 'Access Key',
                'placeholder' => 'Enter Access Key',
            ],
        ],
        'ucloud_options' => [
            'private_key' => [
                'label' => 'Private Key',
                'placeholder' => 'Enter Private Key',
            ],
            'public_key' => [
                'label' => 'Public Key',
                'placeholder' => 'Enter Public Key',
            ],
            'sig_content' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
            'project_id' => [
                'label' => 'Project ID',
                'placeholder' => 'Enter Project ID',
            ],
        ],
        'smsbao_options' => [
            'user' => [
                'label' => 'Account',
                'placeholder' => 'Enter Account',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
        ],
        'moduyun_options' => [
            'accesskey' => [
                'label' => 'Access Key',
                'placeholder' => 'Enter Access Key',
            ],
            'secretkey' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'sign_id' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
            'type' => [
                'label' => 'SMS Type',
                'placeholder' => 'Select SMS type',
                'options' => [
                    'normal' => 'Normal SMS',
                    'marketing' => 'Marketing SMS',
                ],
            ],
        ],
        'rongheyun_options' => [
            'username' => [
                'label' => 'Username',
                'placeholder' => 'Enter Username',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
            'signature' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'zzyun_options' => [
            'user_id' => [
                'label' => 'Member ID',
                'placeholder' => 'Enter Member ID',
            ],
            'secret' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
        ],
        'maap_options' => [
            'cpcode' => [
                'label' => 'Merchant Code',
                'placeholder' => 'Enter Merchant Code',
            ],
            'key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'excode' => [
                'label' => 'Extension Name',
                'placeholder' => 'Enter Extension Name',
            ],
        ],
        'tinree_options' => [
            'accesskey' => [
                'label' => 'Access Key',
                'placeholder' => 'Enter Access Key',
            ],
            'secret' => [
                'label' => 'Secret',
                'placeholder' => 'Enter Secret',
            ],
            'sign' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature or signature ID',
            ],
        ],
        'nowcn_options' => [
            'key' => [
                'label' => 'User ID',
                'placeholder' => 'Enter User ID',
            ],
            'secret' => [
                'label' => 'Development Secret Key',
                'placeholder' => 'Enter Development Secret Key',
            ],
            'api_type' => [
                'label' => 'SMS Channel',
                'placeholder' => 'Enter SMS Channel',
            ],
        ],
        'volcengine_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => 'Enter Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => 'Enter Access Key Secret',
            ],
            'region_id' => [
                'label' => 'Region ID',
                'placeholder' => 'Enter Region ID',
            ],
            'sign_name' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature or signature ID',
            ],
            'sms_account' => [
                'label' => 'Message Group Account',
                'placeholder' => 'Enter Message Group Account, the string in parentheses in the SMS application at the top right corner of the Volcano SMS page',
            ],
        ],
        'yidongmasblack_options' => [
            'ec_name' => [
                'label' => 'Organization Name',
                'placeholder' => 'Enter Organization Name',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'ap_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
            'sign' => [
                'label' => 'SMS Signature',
                'placeholder' => 'Enter SMS signature',
            ],
            'add_serial' => [
                'label' => 'Channel Number',
                'placeholder' => 'Enter Channel Number',
            ],
        ],
        'events' => [
            'register' => 'Register',
            'login' => 'Login',
            'forget_password' => 'Reset Password',
            'bind' => 'Bind Phone',
            'verify' => 'Verify Phone',
        ],
    ],
];