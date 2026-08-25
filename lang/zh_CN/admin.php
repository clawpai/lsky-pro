<?php

declare(strict_types=1);

return [
    'user_info' => '用户信息',

    'common' => [
        'filters' => [
            'created_at' => '创建时间',
            'created_from' => '开始时间',
            'created_from_placeholder' => '选择开始时间',
            'created_until' => '结束时间',
            'created_until_placeholder' => '选择结束时间',
            'all' => '所有',
        ],
    ],

    'navigation_groups' => [
        'user' => '用户',
        'operate' => '运营',
        'driver' => '驱动器',
        'system' => '系统',
    ],
    'plan_types' => [
        'vip' => '会员套餐',
        'storage' => '容量套餐',
    ],
    'violation_statuses' => [
        'unhandled' => '未处理',
        'handled' => '已处理',
    ],
    'order_statuses' => [
        'unpaid' => '未支付',
        'paid' => '已支付',
        'cancelled' => '已取消',
    ],
    'report_statuses' => [
        'unhandled' => '未处理',
        'handled' => '已处理',
    ],
    'ticket_levels' => [
        'low' => '低',
        'medium' => '中',
        'high' => '高',
    ],
    'ticket_statuses' => [
        'in_progress' => '进行中',
        'completed' => '已完成',
    ],
    'coupon_types' => [
        'direct' => '直接抵扣',
        'percent' => '百分比抵扣',
    ],
    'page_types' => [
        'internal' => '内页',
        'external' => '外链',
    ],
    'feedback_types' => [
        'general' => '一般意见',
        'dmca' => 'DMCA（数字千年版权）投诉',
    ],
    'storage_providers' => [
        'local' => '本地',
        's3' => 'AWS S3',
        'oss' => '阿里云 OSS',
        'cos' => '腾讯云 COS',
        'qiniu' => '七牛云 Kodo',
        'upyun' => '又拍云 USS',
        'sftp' => 'Sftp',
        'ftp' => 'Ftp',
        'webdav' => 'Webdav',
    ],
    'sms_providers' => [
        'aliyun' => '阿里云',
        'aliyunrest' => '阿里云 Rest',
        'aliyunintl' => '阿里云国际',
        'yunpian' => '云片',
        'submail' => 'Submail',
        'luosimao' => '螺丝帽',
        'yuntongxun' => '容联云通讯',
        'huyi' => '互亿无线',
        'juhe' => '聚合数据',
        'sendcloud' => 'SendCloud',
        'baidu' => '百度云',
        'huaxin' => '华信短信平台',
        'chuanglan' => '253云通讯（创蓝）',
        'chuanglanv1' => '创蓝云智',
        'rongcloud' => '融云',
        'tianyiwuxian' => '天毅无线',
        'twilio' => 'Twilio',
        'tiniyo' => 'Tiniyo',
        'qcloud' => '腾讯云 SMS',
        'huawei' => '华为云 SMS',
        'yunxin' => '网易云信',
        'yunzhixun' => '云之讯',
        'kingtto' => '凯信通',
        'qiniu' => '七牛云',
        'ucloud' => 'Ucloud',
        'smsbao' => '短信宝',
        'moduyun' => '摩杜云',
        'rongheyun' => '融合云（助通）',
        'zzyun' => '蜘蛛云',
        'maap' => '融合云信',
        'tinree' => '天瑞云',
        'nowcn' => '时代互联',
        'volcengine' => '火山引擎',
        'yidongmasblack' => '移动云MAS（黑名单模式）',
    ],
    'scan_providers' => [
        'aliyun_v1' => '阿里云内容安全 1.0',
        'aliyun_v2' => '阿里云内容安全增强版',
        'tencent' => '腾讯云内容安全',
        'nsfw_js' => 'NsfwJS',
        'moderate_content' => 'ModerateContent',
    ],
    'payment_providers' => [
        'alipay' => '支付宝',
        'wechat' => '微信',
        'unipay' => '银联',
        'paypal' => 'PayPal',
        'epay' => 'EPay',
    ],
    'payment_channels' => [
        'alipay' => '支付宝',
        'wechat' => '微信支付',
        'unipay' => '银联支付',
        'wxpay' => '微信支付',
        'paypal' => 'PayPal',
        'qqpay' => 'QQ 钱包',
        'bank' => '网银支付',
        'jdpay' => '京东支付',
        'usdt' => 'USDT-TRC20',
        'unified' => '统一支付',
    ],
    'socialite_providers' => [
        'github' => 'Github',
        'qq' => 'QQ',
    ],
    'storage_naming_rules' => [
        'Y' => [
            'name' => '年',
            'description' => '2024',
        ],
        'y' => [
            'name' => '年',
            'description' => '24',
        ],
        'm' => [
            'name' => '月',
            'description' => '01',
        ],
        'd' => [
            'name' => '日',
            'description' => '04',
        ],
        'Ymd' => [
            'name' => '年月日',
            'description' => '20240104',
        ],
        'filename' => [
            'name' => '文件名',
            'description' => '吴彦祖的自拍照',
        ],
        'ext' => [
            'name' => '文件拓展名',
            'description' => 'png',
        ],
        'time' => [
            'name' => '时间戳',
            'description' => '1718956160',
        ],
        'uniqid' => [
            'name' => '唯一ID',
            'description' => '667530e55196f',
        ],
        'md5' => [
            'name' => '文件 md5 值',
            'description' => 'e10adc3949ba59abbe56e057f20f883e',
        ],
        'sha1' => [
            'name' => '文件 sha1 值',
            'description' => '7c4a8d09ca3762af61e59520943dc26494f8941b',
        ],
        'uuid' => [
            'name' => 'UUID',
            'description' => 'ca083d36-1f0a-4d2a-83f7-f3e97f4f4bfa',
        ],
        'uid' => [
            'name' => '用户 ID',
            'description' => '1',
        ],
    ],
];
