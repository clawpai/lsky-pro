<?php

declare(strict_types=1);

return [
    'model_label' => '举报',
    'plural_model_label' => '举报管理',
    'columns' => [
        'report_user' => [
            'email' => '被举报用户邮箱',
            'name' => '被举报用户名称',
            'phone' => '被举报用户手机号',
            'be_reports_count' => '该用户被举报次数',
        ],
        'type' => '举报类型',
        'types' => [
            'album' => '相册',
            'photo' => '图片',
            'share' => '分享',
            'user' => '用户',
        ],
        'content' => '举报内容',
        'status' => '状态',
        'handled_at' => '处理时间',
        'created_at' => '举报时间',
    ],
    'filters' => [
        'status' => '状态',
    ],
    'actions' => [
        'handle' => [
            'label' => '标记为已处理',
            'success' => '处理成功',
        ],
        'view' => [
            'label' => '查看'
        ]
    ],
];
