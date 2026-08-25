<?php

declare(strict_types=1);

return [
    'model_label' => 'Announcement',
    'plural_model_label' => 'Announcement Management',
    'columns' => [
        'title' => 'Title',
        'sort' => 'Sort Value',
        'created_at' => 'Publication Time',
    ],
    'actions' => [],
    'form_fields' => [
        'title' => [
            'label' => 'Title',
            'placeholder' => 'Please enter title',
        ],
        'content' => [
            'label' => 'Content',
            'placeholder' => 'Please enter content',
        ],
        'sort' => [
            'label' => 'Sort Value',
            'placeholder' => 'Please enter sort value, smaller values appear first',
        ]
    ],
];