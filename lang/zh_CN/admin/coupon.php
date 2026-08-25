<?php

declare(strict_types=1);

return [
    'model_label' => '优惠券',
    'plural_model_label' => '优惠券管理',
    'columns' => [
        'type' => '类型',
        'name' => '名称',
        'code' => '优惠码',
        'value' => '面值',
        'usage_limit' => '可使用次数',
        'usage_count' => '已使用次数',
        'expired_at' => '到期时间',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'filters' => [
        'type' => '类型',
        'expired_status' => '过期状态',
        'unexpired' => '未过期',
        'expired' => '已过期',
        'all_status' => '全部状态',
    ],
    'form_fields' => [
        'type' => [
            'label' => '类型',
            'placeholder' => '请输入类型',
        ],
        'name' => [
            'label' => '名称',
            'placeholder' => '请输入名称',
        ],
        'code' => [
            'label' => '优惠码',
            'placeholder' => '请输入优惠码',
        ],
        'direct_value' => [
            'label' => '抵扣金额',
            'placeholder' => '请输入抵扣金额',
            'helper_text' => '请填写可抵扣金额（合计 = 价格 - 抵扣金额）。',
        ],
        'percent_value' => [
            'label' => '折扣率',
            'placeholder' => '请输入折扣率',
            'helper_text' => '请填写 0-1 之间的数字，例如 9 折请输入 0.9 (合计 = 价格 * 折扣率)',
        ],
        'usage_limit' => [
            'label' => '可使用次数',
            'placeholder' => '请输入可使用次数',
        ],
        'expired_at' => [
            'label' => '到期时间',
            'placeholder' => '请输入到期时间',
        ],
    ],
];
