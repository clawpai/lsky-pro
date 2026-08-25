<?php

declare(strict_types=1);

return [
    'user_info' => 'User Information',

    'common' => [
        'filters' => [
            'created_at' => 'Created Time',
            'created_from' => 'From Date',
            'created_from_placeholder' => 'Select start date',
            'created_until' => 'To Date',
            'created_until_placeholder' => 'Select end date',
            'all' => 'All',
        ],
    ],

    'navigation_groups' => [
        'user' => 'User',
        'operate' => 'Operations',
        'driver' => 'Driver',
        'system' => 'System',
    ],
    'plan_types' => [
        'vip' => 'VIP Plan',
        'storage' => 'Storage Plan',
    ],
    'violation_statuses' => [
        'unhandled' => 'Unhandled',
        'handled' => 'Handled',
    ],
    'order_statuses' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ],
    'report_statuses' => [
        'unhandled' => 'Unhandled',
        'handled' => 'Handled',
    ],
    'ticket_levels' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ],
    'ticket_statuses' => [
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ],
    'coupon_types' => [
        'direct' => 'Direct Deduction',
        'percent' => 'Percentage Deduction',
    ],
    'page_types' => [
        'internal' => 'Internal Page',
        'external' => 'External Link',
    ],
    'feedback_types' => [
        'general' => 'General Feedback',
        'dmca' => 'DMCA (Digital Millennium Copyright) Complaint',
    ],
    'storage_providers' => [
        'local' => 'Local',
        's3' => 'AWS S3',
        'oss' => 'Alibaba Cloud OSS',
        'cos' => 'Tencent Cloud COS',
        'qiniu' => 'Qiniu Cloud Kodo',
        'upyun' => 'UpYun USS',
        'sftp' => 'SFTP',
        'ftp' => 'FTP',
        'webdav' => 'WebDAV',
    ],
    'sms_providers' => [
        'aliyun' => 'Aliyun',
        'aliyunrest' => 'Aliyun REST',
        'aliyunintl' => 'Aliyun International',
        'yunpian' => 'Yunpian',
        'submail' => 'Submail',
        'luosimao' => 'Luosimao',
        'yuntongxun' => 'Yuntongxun Communication',
        'huyi' => 'Huyi Wireless',
        'juhe' => 'Juhe Data',
        'sendcloud' => 'SendCloud',
        'baidu' => 'Baidu Cloud',
        'huaxin' => 'Huaxin SMS Platform',
        'chuanglan' => '253 Cloud Communication (Chuanglan)',
        'chuanglanv1' => 'Chuanglan Cloud Intelligence',
        'rongcloud' => 'Rong Cloud',
        'tianyiwuxian' => 'Tianyi Wireless',
        'twilio' => 'Twilio',
        'tiniyo' => 'Tiniyo',
        'qcloud' => 'Tencent Cloud SMS',
        'huawei' => 'Huawei Cloud SMS',
        'yunxin' => 'NetEase Yunxin',
        'yunzhixun' => 'Yunzhixun',
        'kingtto' => 'Kingtto',
        'qiniu' => 'Qiniu Cloud',
        'ucloud' => 'Ucloud',
        'smsbao' => 'SMSBao',
        'moduyun' => 'Modyun',
        'rongheyun' => 'Rongheyun (Zhutong)',
        'zzyun' => 'Zzyun',
        'maap' => 'Ronghe Yunxin',
        'tinree' => 'Tinree Cloud',
        'nowcn' => 'Times Interconnect',
        'volcengine' => 'Volcano Engine',
        'yidongmasblack' => 'Yidong Cloud MAS (Blacklist Mode)',
    ],
    'scan_providers' => [
        'aliyun_v1' => 'Aliyun Content Security 1.0',
        'aliyun_v2' => 'Aliyun Content Security Enhanced',
        'tencent' => 'Tencent Cloud Content Security',
        'nsfw_js' => 'NsfwJS',
        'moderate_content' => 'ModerateContent',
    ],
    'payment_providers' => [
        'alipay' => 'Alipay',
        'wechat' => 'WeChat',
        'unipay' => 'UnionPay',
        'paypal' => 'PayPal',
        'epay' => 'EPay',
    ],
    'payment_channels' => [
        'alipay' => 'Alipay',
        'wechat' => 'WeChat Pay',
        'unipay' => 'Uni Pay',
        'wxpay' => 'WeChat Pay',
        'paypal' => 'PayPal',
        'qqpay' => 'QQ Wallet',
        'bank' => 'Bank Pay',
        'jdpay' => 'JD Pay',
        'usdt' => 'USDT-TRC20',
        'unified' => 'Unified payment',
    ],
    'socialite_providers' => [
        'github' => 'GitHub',
        'qq' => 'QQ',
    ],
    'storage_naming_rules' => [
        'Y' => [
            'name' => 'Year',
            'description' => '2024',
        ],
        'y' => [
            'name' => 'Year (short)',
            'description' => '24',
        ],
        'm' => [
            'name' => 'Month',
            'description' => '01',
        ],
        'd' => [
            'name' => 'Day',
            'description' => '04',
        ],
        'Ymd' => [
            'name' => 'Year-Month-Day',
            'description' => '20240104',
        ],
        'filename' => [
            'name' => 'Filename',
            'description' => 'Wu Yanzu\'s Selfie',
        ],
        'ext' => [
            'name' => 'File Extension',
            'description' => 'png',
        ],
        'time' => [
            'name' => 'Timestamp',
            'description' => '1718956160',
        ],
        'uniqid' => [
            'name' => 'Unique ID',
            'description' => '667530e55196f',
        ],
        'md5' => [
            'name' => 'File MD5',
            'description' => 'e10adc3949ba59abbe56e057f20f883e',
        ],
        'sha1' => [
            'name' => 'File SHA1',
            'description' => '7c4a8d09ca3762af61e59520943dc26494f8941b',
        ],
        'uuid' => [
            'name' => 'UUID',
            'description' => 'ca083d36-1f0a-4d2a-83f7-f3e97f4f4bfa',
        ],
        'uid' => [
            'name' => 'User ID',
            'description' => '1',
        ],
    ],
];