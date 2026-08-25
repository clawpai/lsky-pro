<?php

declare(strict_types=1);

return [
    'model_label' => 'Order',
    'plural_model_label' => 'Order Management',
    'columns' => [
        'user' => [
            'avatar' => 'Avatar',
            'name' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone Number',
        ],
        'plan' => [
            'name' => 'Plan Name',
        ],
        'trade_no' => 'Order Number',
        'type' => 'Order Type',
        'pay_method' => 'Payment Method',
        'amount' => 'Order Amount',
        'status' => 'Status',
        'paid_at' => 'Payment Time',
        'canceled_at' => 'Cancellation Time',
        'created_at' => 'Order Time',
    ],
    'filters' => [
        'status' => 'Status',
        'pay_method' => 'Payment Method',
        'user' => 'User',
    ],
    'actions' => [
        'cancel' => [
            'label' => 'Cancel Order',
            'success' => 'Cancellation Successful',
        ],
        'set_amount' => [
            'label' => 'Set Amount',
            'success' => 'Set Successfully',
        ],
    ],
    'view' => [
        'plan' => [
            'label' => 'Plan Information',
            'name' => 'Plan Name',
            'intro' => 'Plan Introduction',
            'badge' => 'Badge',
            'group' => 'Plan Associated Role Group',
            'capacity' => 'Plan Capacity',
        ],
        'product' => [
            'label' => 'Product Information',
            'name' => 'Product Name',
            'duration' => 'Duration (minutes)',
            'price' => 'Price',
        ],
        'order' => [
            'label' => 'Order Information',
        ],
        'user' => [
            'label' => 'User Information',
        ],
    ],
];