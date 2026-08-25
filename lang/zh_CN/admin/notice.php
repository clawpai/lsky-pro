<?php

declare(strict_types=1);

return [
    'model_label' => '公告',
    'plural_model_label' => '公告管理',
    'columns' => [
        'title' => '标题',
        'sort' => '排序值',
        'created_at' => '发布时间',
    ],
    'actions' => [],
    'form_fields' => [
        'title' => [
            'label' => '标题',
            'placeholder' => '请输入标题',
        ],
        'content' => [
            'label' => '内容',
            'placeholder' => '请输入内容',
        ],
        'sort' => [
            'label' => '排序值',
            'placeholder' => '请输入排序值，越小越靠前',
        ]
    ],
];
