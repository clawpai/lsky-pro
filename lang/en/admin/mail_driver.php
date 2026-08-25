<?php

declare(strict_types=1);

return [
    'model_label' => 'Mail Driver',
    'plural_model_label' => 'Mail',
    'columns' => [
        'name' => 'Storage Name',
        'intro' => 'Introduction',
        'options_provider' => 'Mail Provider',
        'created_at' => 'Creation Time',
    ],
    'actions' => [
        'test_connection' => [
            'label' => 'Test Connection',
            'success' => 'Mail connection test successful!',
            'failed' => 'Mail connection test failed',
            'error_message' => 'Connection error: :message',
            'confirm' => 'Are you sure you want to test the mail connection with current configuration?',
            'validation_error' => 'Form Validation Error',
            'please_fill_form' => 'Please complete the form first',
            'select_provider' => 'Please select a mail driver type first',
        ],
    ],
    'form_fields' => [
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Enter Name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter Introduction',
        ],
        'options' => [
            'provider' => [
                'label' => 'Mail Driver',
                'placeholder' => 'Please select mail driver'
            ],
            'from_address' => [
                'label' => 'Sender Email Address',
                'placeholder' => 'Enter sender email address'
            ],
            'from_name' => [
                'label' => 'Sender Name',
                'placeholder' => 'Enter sender name'
            ],
        ],
        'smtp_options' => [
            'host' => [
                'label' => 'SMTP Server Address',
                'placeholder' => 'Enter SMTP server address, e.g., smtp.qiye.aliyun.com',
            ],
            'port' => [
                'label' => 'SMTP Server Port',
                'placeholder' => 'Enter SMTP server port, e.g., 465',
            ],
            'encryption' => [
                'label' => 'SMTP Encryption Method',
                'placeholder' => 'Enter SMTP encryption method, e.g., ssl',
            ],
            'username' => [
                'label' => 'SMTP Username',
                'placeholder' => 'Enter SMTP username',
            ],
            'password' => [
                'label' => 'SMTP Password',
                'placeholder' => 'Enter SMTP password',
            ],
        ],
        'mailgun_options' => [
            'service' => [
                'domain' => [
                    'label' => 'Domain',
                    'placeholder' => 'Enter Domain',
                ],
                'secret' => [
                    'label' => 'Secret',
                    'placeholder' => 'Enter Secret',
                ],
                'endpoint' => [
                    'label' => 'Endpoint',
                    'placeholder' => 'Enter Endpoint',
                ],
                'scheme' => [
                    'label' => 'Scheme',
                    'placeholder' => 'Enter Scheme',
                ],
            ]
        ],
        'postmark_options' => [
            'service' => [
                'token' => [
                    'label' => 'Token',
                    'placeholder' => 'Enter Token',
                ],
            ]
        ],
        'ses_options' => [
            'service' => [
                'key' => [
                    'label' => 'AccessKeyId',
                    'placeholder' => 'Enter AccessKeyId',
                ],
                'secret' => [
                    'label' => 'SecretAccessKey',
                    'placeholder' => 'Enter SecretAccessKey',
                ],
                'region' => [
                    'label' => 'Region',
                    'placeholder' => 'Enter Region, e.g., us-east-1',
                ],
                'token' => [
                    'label' => 'Token',
                    'placeholder' => 'Enter Token',
                ]
            ]
        ],
    ],
];