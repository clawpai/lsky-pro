<?php

declare(strict_types=1);

return [
    'model_label' => '用户',
    'plural_model_label' => '用户管理',
    'columns' => [
        'avatar' => '头像',
        'name' => '姓名',
        'email' => '邮箱',
        'phone' => '手机号',
        'phone_verified_at' => '手机号验证时间',
        'email_verified_at' => '邮箱验证时间',
        'login_ip' => '最后登录 IP',
        'register_ip' => '注册 IP',
        'created_at' => '注册时间',
        'is_admin' => '是否为管理员',
        'photos_count' => '图片数量',
        'shares_count' => '分享数量',
        'orders_count' => '订单数量',
        'groups_count' => '角色组数量',
        'capacities_sum_capacity' => '可用容量',
        'group_name' => '角色组',
     ],
    'actions' => [
        'change_password' => [
            'label' => '修改密码',
        ],
        'delete_all_photos' => [
            'label' => '删除所有图片',
            'modal_heading' => '删除用户所有图片',
            'modal_description' => '确定要删除该用户的所有图片吗？此操作不可逆！',
            'modal_submit' => '确认删除',
            'success_title' => '删除成功',
            'success_message' => '已成功删除 :count 张图片',
            'info_title' => '删除完成',
            'info_message' => '该用户没有图片需要删除',
            'error_title' => '删除失败',
            'error_message' => '删除图片时发生错误：:error',
            'no_photos_tooltip' => '该用户没有图片',
        ]
    ],
    'filters' => [
        'email_verified' => '邮箱验证状态',
        'email_verified_true' => '已验证',
        'email_verified_false' => '未验证',
        'phone_verified' => '手机验证状态',
        'phone_verified_true' => '已验证',
        'phone_verified_false' => '未验证',
        'group' => '角色组',
        'created_at' => '注册时间',
        'created_from' => '开始时间',
        'created_from_placeholder' => '选择开始时间',
        'created_until' => '结束时间',
        'created_until_placeholder' => '选择结束时间',
    ],
    'form_fields' => [
        'group_id' => [
            'label' => '角色组',
            'placeholder' => '请选择初始角色组'
        ],
        'capacity' => [
            'label' => '初始容量(kb)',
            'placeholder' => '请输入用户初始容量，单位(kb)',
        ],
        'avatar' => [
            'label' => '头像',
            'placeholder' => '选择头像',
        ],
        'username' => [
            'label' => '用户名',
            'placeholder' => '请输入用户名',
        ],
        'name' => [
            'label' => '姓名',
            'placeholder' => '请输入姓名',
        ],
        'email' => [
            'label' => '邮箱',
            'placeholder' => '请输入邮箱',
        ],
        'phone' => [
            'label' => '手机号',
            'placeholder' => '请输入手机号',
        ],
        'country_code' => [
            'label' => '国家',
            'placeholder' => '请选择国家',
        ],
        'password' => [
            'label' => '密码',
            'placeholder' => '请输入密码',
            'placeholder_for_edit' => '不修改请留空',
        ],
        'new_password' => [
            'label' => '新密码',
            'placeholder' => '请输入新密码',
        ],
        'new_password_confirmation' => [
            'label' => '确认新密码',
            'placeholder' => '请确认新密码',
        ],
    ],
];
