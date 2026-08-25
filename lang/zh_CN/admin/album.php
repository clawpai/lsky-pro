<?php

declare(strict_types=1);

return [
    'model_label' => '相册',
    'plural_model_label' => '相册管理',
    'columns' => [
        'user' => [
            'avatar' => '头像',
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
        ],
        'name' => '相册名',
        'intro' => '简介',
        'is_public' => '是否公开',
        'created_at' => '创建时间',
    ],
    'filters' => [
        'user' => '用户',
        'is_public' => '公开状态',
        'is_public_true' => '公开',
        'is_public_false' => '私有',
    ],
];