<?php

declare(strict_types=1);

return [
    'model_label' => 'Role Group',
    'plural_model_label' => 'Role Groups',
    'columns' => [
        'name' => 'Role Group Name',
        'intro' => 'Introduction',
        'storages_count' => 'Number of Storage Drivers',
        'is_default' => 'System Default Group',
        'is_guest' => 'Guest Group',
        'created_at' => 'Created At',
    ],
    'actions' => [
        'delete' => [
            'modal_description' => 'Note: After deleting this role group, all associated plans and subscriptions linked to this group will be removed.',
        ],
    ],
    'filters' => [
        'is_default' => 'Default Group Status',
        'is_default_true' => 'Is Default Group',
        'is_default_false' => 'Not Default Group',
        'is_guest' => 'Guest Group Status',
        'is_guest_true' => 'Is Guest Group',
        'is_guest_false' => 'Not Guest Group',
    ],
    'form_tabs' => [
        'basic' => 'Basic',
        'upload' => 'Upload',
        'storage' => 'Storage',
    ],
    'form_fields' => [
        'name' => [
            'label' => 'Role Group Name',
            'placeholder' => 'Enter role group name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'sms' => [
            'label' => 'SMS Driver',
            'placeholder' => 'Select SMS driver',
            'helper_text' => 'Used for mobile phone verification during account registration, SMS notifications, etc.',
        ],
        'mail' => [
            'label' => 'Email Driver',
            'placeholder' => 'Select email driver',
            'helper_text' => 'Used for email verification during account registration, email notifications, etc.',
        ],
        'payment' => [
            'label' => 'Payment Driver',
            'placeholder' => 'Select payment driver',
            'helper_text' => 'Third-party payment driver used for purchasing subscriptions.',
        ],
        'storages' => [
            'label' => 'Storage Driver',
            'helper_text' => 'Users in this group can use the associated storage.',
        ],
        'is_default' => [
            'label' => 'Set as System Default Group',
            'helper_text' => 'If set as default, new users will belong to this default role group. Only one default group is allowed.',
        ],
        'is_guest' => [
            'label' => 'Set as Guest Group',
            'helper_text' => 'If set as the guest group, users who are not logged in will be controlled by this group. Only one guest group is allowed.',
        ],
        'options' => [
            'max_upload_size' => [
                'label' => 'Maximum Upload Size',
                'placeholder' => 'Enter maximum upload size in KB',
            ],
            'file_expire_seconds' => [
                'label' => 'File Expiration Time',
                'placeholder' => 'Enter 0 for permanent storage; changes apply only to newly uploaded files',
                'suffix' => 'seconds',
            ],
            'limit_concurrent_upload' => [
                'label' => 'Concurrent Upload Limit',
                'placeholder' => 'Number of files that can be uploaded simultaneously in the queue',
            ],
            'limit_per_minute' => [
                'label' => 'Upload Limit Per Minute',
                'placeholder' => 'Enter the number of files that can be uploaded per minute',
            ],
            'limit_per_hour' => [
                'label' => 'Upload Limit Per Hour',
                'placeholder' => 'Enter the number of files that can be uploaded per hour',
            ],
            'limit_per_day' => [
                'label' => 'Upload Limit Per Day',
                'placeholder' => 'Enter the number of files that can be uploaded per day',
            ],
            'limit_per_week' => [
                'label' => 'Upload Limit Per Week',
                'placeholder' => 'Enter the number of files that can be uploaded per week',
            ],
            'limit_per_month' => [
                'label' => 'Upload Limit Per Month',
                'placeholder' => 'Enter the number of files that can be uploaded per month',
            ],
            'allow_file_types' => [
                'label' => 'Allowed File Types',
                'helper_text' => 'All supported file types are determined by the system image processor. If there is a type that cannot be selected, it means that the file type is not supported.',
            ],
        ]
    ],
];