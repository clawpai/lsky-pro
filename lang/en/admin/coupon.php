<?php

declare(strict_types=1);

return [
    'model_label' => 'Coupon',
    'plural_model_label' => 'Coupon Management',
    'columns' => [
        'type' => 'Type',
        'name' => 'Name',
        'code' => 'Coupon Code',
        'value' => 'Value',
        'usage_limit' => 'Usage Limit',
        'usage_count' => 'Usage Count',
        'expired_at' => 'Expiration Date',
        'created_at' => 'Created At',
    ],
    'actions' => [],
    'filters' => [
        'type' => 'Type',
        'expired_status' => 'Expiration Status',
        'unexpired' => 'Unexpired',
        'expired' => 'Expired',
        'all_status' => 'All Status',
    ],
    'form_fields' => [
        'type' => [
            'label' => 'Type',
            'placeholder' => 'Please enter the type',
        ],
        'name' => [
            'label' => 'Name',
            'placeholder' => 'Please enter the name',
        ],
        'code' => [
            'label' => 'Coupon Code',
            'placeholder' => 'Please enter the coupon code',
        ],
        'direct_value' => [
            'label' => 'Deduction Amount',
            'placeholder' => 'Please enter the deduction amount',
            'helper_text' => 'Please fill in the deduction amount (Total = Price - Deduction Amount).',
        ],
        'percent_value' => [
            'label' => 'Discount Rate',
            'placeholder' => 'Please enter the discount rate',
            'helper_text' => 'Please fill in a number between 0-1, e.g., for a 10% discount enter 0.9 (Total = Price * Discount Rate).',
        ],
        'usage_limit' => [
            'label' => 'Usage Limit',
            'placeholder' => 'Please enter the usage limit',
        ],
        'expired_at' => [
            'label' => 'Expiration Date',
            'placeholder' => 'Please enter the expiration date',
        ],
    ],
];