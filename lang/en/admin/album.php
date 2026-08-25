<?php

declare(strict_types=1);

return [
    'model_label' => 'Album',
    'plural_model_label' => 'Album Management',
    'columns' => [
        'user' => [
            'avatar' => 'Avatar',
            'name' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone Number',
        ],
        'name' => 'Album Name',
        'intro' => 'Introduction',
        'is_public' => 'Is Public',
        'created_at' => 'Created At',
    ],
    'filters' => [
        'user' => 'User',
        'is_public' => 'Visibility Status',
        'is_public_true' => 'Public',
        'is_public_false' => 'Private',
    ],
];