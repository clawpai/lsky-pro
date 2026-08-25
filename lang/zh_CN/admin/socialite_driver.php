<?php

declare(strict_types=1);

return [
    'model_label' => '社会化登录驱动器',
    'plural_model_label' => '社会化登录',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'options_provider' => '登录驱动',
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
                'label' => '登录网关',
                'placeholder' => '请选择登录网关'
            ],
            'client_id' => [
                'label' => 'Client ID',
                'placeholder' => '请输入 Client ID',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => '请输入 Client Secret',
            ],
            'redirect' => [
                'label' => '回调地址',
            ]
        ],
    ],
];
