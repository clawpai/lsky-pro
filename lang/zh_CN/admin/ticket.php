<?php

declare(strict_types=1);

return [
    'model_label' => '工单',
    'plural_model_label' => '工单管理',
    'columns' => [
        'issue_no' => '工单号',
        'title' => '标题',
        'status' => '状态',
        'level' => '级别',
        'reply' => [
            'content' => '最后回复内容',
            'created_at' => '最后回复时间',
        ],
        'created_at' => '创建时间',
        'user' => [
            'name' => '发起用户',
            'email' => '发起用户邮箱',
            'phone' => '发起用户手机号',
        ],
     ],
    'filters' => [
        'status' => '状态',
        'level' => '级别',
    ],
    'actions' => [
        'close' => [
            'label' => '关闭工单',
            'description' => '关闭工单后无法继续进行任何操作，确定要关闭工单吗？',
            'success' => '关闭成功',
        ],
        'view' => [
            'label' => '详情',
        ],
    ],
    'view' => [
        'title' => '工单详情',
        'navigation_label' => '工单',
        'session_label' => '对话记录',
        'close_tip' => '工单已关闭，无法继续回复。',
        'reply' => [
            'content' => [
                'label' => '回复内容',
                'placeholder' => '请输入回复内容，回车换行。',
            ],
            'submit' => [
                'label' => '提交回复',
                'success' => '回复成功'
            ],
        ]
    ]
];
