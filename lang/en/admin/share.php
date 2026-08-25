<?php

declare(strict_types=1);

return [
    'model_label' => 'Share',
    'plural_model_label' => 'Share Management',
    'columns' => [
        'user' => [
            'avatar' => 'Avatar',
            'name' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone Number',
        ],
        'url' => 'Share URL',
        'content' => 'Share Content',
        'view_count' => 'View Count',
        'password' => 'Access Code',
        'expired_at' => 'Expiration Time',
        'created_at' => 'Share Time',
    ],
    'actions' => [
        'url' => [
            'label' => 'Open Share',
        ],
    ],
    'filters' => [
        'user' => 'User',
    ],
    'form_fields' => [

    ],
];