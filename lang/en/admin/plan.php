<?php

declare(strict_types=1);

return [
    'model_label' => 'Package',
    'plural_model_label' => 'Package Management',
    'columns' => [
        'type' => 'Package Type',
        'name' => 'Package Name',
        'intro' => 'Package Introduction',
        'orders_count' => 'Purchase Count',
        'is_up' => 'Is Listed',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'filters' => [
        'type' => 'Package Type',
        'is_up' => 'Listing Status',
        'is_up_true' => 'Listed',
        'is_up_false' => 'Not Listed',
    ],
    'sections' => [
        'general' => 'General Settings',
        'price' => 'Tiered Pricing Settings',
    ],
    'form_fields' => [
        'type' => [
            'label' => 'Package Type',
        ],
        'name' => [
            'label' => 'Package Name',
            'placeholder' => 'Please enter package name',
        ],
        'intro' => [
            'label' => 'Package Introduction',
            'placeholder' => 'Enter package introduction',
        ],
        'badge' => [
            'label' => 'Badge Content',
            'placeholder' => 'Enter badge content to highlight the package',
        ],
        'features' => [
            'label' => 'Package Benefits',
            'placeholder' => 'For example: Unlimited uploads',
        ],
        'sort' => [
            'label' => 'Sort Value',
            'placeholder' => 'Please enter sort value, smaller values appear first',
        ],
        'is_up' => [
            'label' => 'Is Listed',
        ],
        'group_id' => [
            'label' => 'Role Group',
            'placeholder' => 'Select associated role group',
            'helper_text' => 'By associating a role group, you can assign the specified role group to users who purchase this package.',
        ],
        'capacity' => [
            'label' => 'Capacity',
            'placeholder' => 'Enter capacity, in KB',
            'helper_text' => 'By setting the capacity, you can increase the storage capacity for users who purchase this package.',
        ],
        'prices' => [
            'label' => 'Tiered Pricing',
            'helper_text' => 'Tiered pricing means that the package is sold for different durations. At least one tiered price is required and will be displayed in ascending order of price.',
            'children' => [
                'name' => [
                    'label' => 'Price Name',
                    'placeholder' => 'Please enter price name, e.g., Monthly Card',
                ],
                'duration' => [
                    'label' => 'Duration (minutes)',
                    'placeholder' => 'Please enter duration, in minutes',
                ],
                'price' => [
                    'label' => 'Price',
                    'placeholder' => 'Please enter price, in Yuan',
                ],
            ]
        ],
    ],
];