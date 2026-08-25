<?php

declare(strict_types=1);

return [
    'model_label' => 'Report',
    'plural_model_label' => 'Report Management',
    'columns' => [
        'report_user' => [
            'email' => 'Reported User Email',
            'name' => 'Reported User Name',
            'phone' => 'Reported User Phone Number',
            'be_reports_count' => 'Number of Reports Against User',
        ],
        'type' => 'Report Type',
        'types' => [
            'album' => 'Album',
            'photo' => 'Photo',
            'share' => 'Share',
            'user' => 'User',
        ],
        'content' => 'Report Content',
        'status' => 'Status',
        'handled_at' => 'Handled At',
        'created_at' => 'Report Time',
    ],
    'filters' => [
        'status' => 'Status',
    ],
    'actions' => [
        'handle' => [
            'label' => 'Mark as Handled',
            'success' => 'Handled Successfully',
        ],
        'view' => [
            'label' => 'View'
        ]
    ],
];