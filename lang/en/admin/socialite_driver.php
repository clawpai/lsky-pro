<?php

declare(strict_types=1);

return [
    'model_label' => 'Social Login Driver',
    'plural_model_label' => 'Social Login',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'options_provider' => 'Login Driver',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter driver name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'options' => [
            'provider' => [
                'label' => 'Login Gateway',
                'placeholder' => 'Please select login gateway',
            ],
            'client_id' => [
                'label' => 'Client ID',
                'placeholder' => 'Enter Client ID',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => 'Enter Client Secret',
            ],
            'redirect' => [
                'label' => 'Redirect URL',
            ]
        ],
    ],
];