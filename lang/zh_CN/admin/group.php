<?php

declare(strict_types=1);

return [
    'model_label' => '角色组',
    'plural_model_label' => '角色组',
    'columns' => [
        'name' => '角色组名称',
        'intro' => '简介',
        'storages_count' => '储存驱动数量',
        'is_default' => '系统默认组',
        'is_guest' => '游客组',
        'created_at' => '创建时间',
    ],
    'actions' => [
        'delete' => [
            'modal_description' => '注意，删除该角色组后，角色组关联的套餐以及用户关联了该组的订阅全部会被移除。',
        ],
    ],
    'filters' => [
        'is_default' => '系统默认组状态',
        'is_default_true' => '是系统默认组',
        'is_default_false' => '非系统默认组',
        'is_guest' => '游客组状态',
        'is_guest_true' => '是游客组',
        'is_guest_false' => '非游客组',
    ],
    'form_tabs' => [
        'basic' => '基础',
        'upload' => '上传',
        'storage' => '储存',
    ],
    'form_fields' => [
        'name' => [
            'label' => '角色组名称',
            'placeholder' => '输入角色组名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'sms' => [
            'label' => '短信驱动',
            'placeholder' => '选择短信驱动    ',
            'helper_text' => '注册账号手机号验证、短信通知等。',
        ],
        'mail' => [
            'label' => '邮件驱动',
            'placeholder' => '选择邮件驱动    ',
            'helper_text' => '注册账号邮件验证、邮件通知等。',
        ],
        'payment' => [
            'label' => '支付驱动',
            'placeholder' => '选择支付驱动    ',
            'helper_text' => '第三方支付驱动，用于购买订阅。',
        ],
        'storages' => [
            'label' => '储存驱动',
            'helper_text' => '归属该组的用户可使用当前关联的储存。',
        ],
        'is_default' => [
            'label' => '是否为系统默认组',
            'helper_text' => '设置默认后，新用户注册以后将会属于该默认角色组，且默认组只能有一个。',
        ],
        'is_guest' => [
            'label' => '是否为游客组',
            'helper_text' => '设置为游客组后，未登录用户受该组控制，且游客组只能有一个。',
        ],
        'options' => [
            'max_upload_size' => [
                'label' => '最大上传尺寸',
                'placeholder' => '输入最大上传尺寸，单位 KB'
            ],
            'file_expire_seconds' => [
                'label' => '图片到期时间',
                'placeholder' => '为 0 则表示文件永久储存，更改后仅对新上传的文件生效',
                'suffix' => '秒',
            ],
            'limit_concurrent_upload' => [
                'label' => '并发上传限制',
                'placeholder' => '上传队列中同时可上传的图片数量',
            ],
            'limit_per_minute' => [
                'label' => '每分钟上传限制',
                'placeholder' => '输入每分钟内可上传的图片数量',
            ],
            'limit_per_hour' => [
                'label' => '每小时上传限制',
                'placeholder' => '输入每小时内可上传的图片数量',
            ],
            'limit_per_day' => [
                'label' => '每天上传限制',
                'placeholder' => '输入每天内可上传的图片数量',
            ],
            'limit_per_week' => [
                'label' => '每周上传限制',
                'placeholder' => '输入每周内可上传的图片数量',
            ],
            'limit_per_month' => [
                'label' => '每月上传限制',
                'placeholder' => '输入每月内可上传的图片数量',
            ],
            'allow_file_types' => [
                'label' => '允许上传的文件类型',
                'helper_text' => '所有支持的文件类型由系统图片处理器决定，如果存在无法选择的类型，则表示不支持该文件类型。',
            ],
        ]
    ],
];
