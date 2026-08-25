<?php

declare(strict_types=1);

return [
    'model_label' => '订单',
    'plural_model_label' => '订单管理',
    'columns' => [
        'user' => [
            'avatar' => '头像',
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
        ],
        'plan' => [
            'name' => '套餐名称',
        ],
        'trade_no' => '订单号',
        'type' => '订单类型',
        'pay_method' => '支付方式',
        'amount' => '订单金额',
        'status' => '状态',
        'paid_at' => '支付时间',
        'canceled_at' => '取消时间',
        'created_at' => '下单时间',
    ],
    'filters' => [
        'status' => '状态',
        'pay_method' => '支付方式',
        'user' => '用户',
    ],
    'actions' => [
        'cancel' => [
            'label' => '取消订单',
            'success' => '取消成功',
        ],
        'set_amount' => [
            'label' => '设置金额',
            'success' => '设置成功',
        ],
    ],
    'view' => [
        'plan' => [
            'label' => '套餐信息',
            'name' => '套餐名称',
            'intro' => '套餐简介',
            'badge' => '角标',
            'group' => '套餐关联角色组',
            'capacity' => '套餐容量',
        ],
        'product' => [
            'label' => '产品信息',
            'name' => '产品名称',
            'duration' => '时长(分钟)',
            'price' => '价格',
        ],
        'order' => [
            'label' => '订单信息',
        ],
        'user' => [
            'label' => '用户信息',
        ],
    ],
];
