<?php

declare(strict_types=1);

return [
    'model_label' => '分享',
    'plural_model_label' => '分享管理',
    'columns' => [
        'user' => [
            'avatar' => '头像',
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
        ],
        'url' => '分享地址',
        'content' => '分享内容',
        'view_count' => '浏览次数',
        'password' => '提取码',
        'expired_at' => '到期时间',
        'created_at' => '分享时间',
    ],
    'actions' => [
        'url' => [
            'label' => '打开分享',
        ],
    ],
    'filters' => [
        'user' => '用户',
    ],
    'form_fields' => [

    ],
];
