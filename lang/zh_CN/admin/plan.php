<?php

declare(strict_types=1);

return [
    'model_label' => '套餐',
    'plural_model_label' => '套餐管理',
    'columns' => [
        'type' => '套餐类型',
        'name' => '套餐名称',
        'intro' => '套餐简介',
        'orders_count' => '购买次数',
        'is_up' => '是否上架',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'filters' => [
        'type' => '套餐类型',
        'is_up' => '上架状态',
        'is_up_true' => '已上架',
        'is_up_false' => '未上架',
    ],
    'sections' => [
        'general' => '通用设置',
        'price' => '阶梯价格设置',
    ],
    'form_fields' => [
        'type' => [
            'label' => '套餐类型',
        ],
        'name' => [
            'label' => '套餐名称',
            'placeholder' => '请输入套餐名称',
        ],
        'intro' => [
            'label' => '套餐简介',
            'placeholder' => '输入套餐简介',
        ],
        'badge' => [
            'label' => '角标内容',
            'placeholder' => '输入角标内容，可使套餐突出显示',
        ],
        'features' => [
            'label' => '套餐权益',
            'placeholder' => '例如：不限上传次数',
        ],
        'sort' => [
            'label' => '排序值',
            'placeholder' => '请输入排序值，值越小越靠前',
        ],
        'is_up' => [
            'label' => '是否上架',
        ],
        'group_id' => [
            'label' => '角色组',
            'placeholder' => '选择关联角色组',
            'helper_text' => '你可以通过关联某个角色组，从而实现给购买该套餐的用户分配指定的角色组。',
        ],
        'capacity' => [
            'label' => '容量',
            'placeholder' => '输入容量，单位 KB',
            'helper_text' => '你可以通过设置容量，从而实现给购买该套餐的用户增加容量。',
        ],
        'prices' => [
            'label' => '阶梯价格',
            'helper_text' => '阶梯价格是指该套餐分不同的时长进行售卖，至少有一项阶梯价格，展示时按价格从小到大排序。',
            'children' => [
                'name' => [
                    'label' => '价格名称',
                    'placeholder' => '请输入价格名称，例如：月卡',
                ],
                'duration' => [
                    'label' => '时长(分钟)',
                    'placeholder' => '请输入时长，单位分钟',
                ],
                'price' => [
                    'label' => '价格',
                    'placeholder' => '请输入价格，单位元',
                ],
            ]
        ],
    ],
];
