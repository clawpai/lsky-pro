<?php

declare(strict_types=1);

return [
    'model_label' => 'Page',
    'plural_model_label' => 'Page Management',
    'columns' => [
        'name' => 'Link Name',
        'type' => 'Page Type',
        'view_count' => 'View Count',
        'sort' => 'Sort Value',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'filters' => [
        'type' => 'Page Type',
    ],
    'form_fields' => [
        'type' => [
            'label' => 'Page Type',
        ],
        'name' => [
            'label' => 'Link Name',
            'placeholder' => 'Please enter link name',
        ],
        'icon' => [
            'label' => 'Page Icon',
            'placeholder' => 'Please enter page icon',
            'helper_text' => 'You can find all available free icon codes here https://fontawesome.com/v5/search?m=free',
        ],
        'title' => [
            'label' => 'Page Title',
            'placeholder' => 'Please enter page title',
        ],
        'content' => [
            'label' => 'Page Content',
            'placeholder' => 'Please enter page content',
        ],
        'sort' => [
            'label' => 'Sort Value',
            'placeholder' => 'Please enter sort value, smaller values appear first',
        ],
        'is_show' => [
            'label' => 'Is Visible',
        ],
        'url' => [
            'label' => 'Link URL',
            'placeholder' => 'Please enter link URL',
        ],
        'slug' => [
            'label' => 'URL Slug',
            'placeholder' => 'Please enter URL slug',
        ],
    ],
];