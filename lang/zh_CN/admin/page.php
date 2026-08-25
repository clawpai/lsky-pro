<?php

declare(strict_types=1);

return [
    'model_label' => '页面',
    'plural_model_label' => '页面管理',
    'columns' => [
        'name' => '链接名称',
        'type' => '页面类型',
        'view_count' => '浏览量',
        'sort' => '排序值',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'filters' => [
        'type' => '页面类型',
    ],
    'form_fields' => [
        'type' => [
            'label' => '页面类型',
        ],
        'name' => [
            'label' => '链接名称',
            'placeholder' => '请输入链接名称',
        ],
        'icon' => [
            'label' => '页面图标',
            'placeholder' => '请输入页面图标',
            'helper_text' => '你可以在这里找到所有可用的免费图标代码 https://fontawesome.com/v5/search?m=free',
        ],
        'title' => [
            'label' => '页面标题',
            'placeholder' => '请输入页面标题',
        ],
        'content' => [
            'label' => '页面内容',
            'placeholder' => '请输入页面内容',
        ],
        'sort' => [
            'label' => '排序值',
            'placeholder' => '请输入排序值，值越小越靠前',
        ],
        'is_show' => [
            'label' => '是否显示',
        ],
        'url' => [
            'label' => '链接地址',
            'placeholder' => '请输入链接地址',
        ],
        'slug' => [
            'label' => 'URL Slug',
            'placeholder' => '请输入 URL Slug',
        ],
    ],
];
