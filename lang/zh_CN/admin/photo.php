<?php

declare(strict_types=1);

return [
    'model_label' => '图片',
    'plural_model_label' => '图片管理',
    'columns' => [
        'user' => [
            'avatar' => '头像',
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
        ],
        'group' => [
            'name' => '所在组名称',
        ],
        'storage' => [
            'name' => '所在储存名称',
        ],
        'thumbnail_url' => '预览图',
        'public_url' => '图片',
        'name' => '图片名',
        'mimetype' => '图片类型',
        'size' => '图片大小',
        'is_public' => '是否公开',
        'status' => '状态',
        'md5' => 'MD5',
        'sha1' => 'SHA-1',
        'ip_address' => '上传 IP',
        'created_at' => '上传时间',
    ],
    'filters' => [
        'status' => '状态',
        'user' => '用户',
        'storage' => '存储',
        'is_public' => '公开状态',
        'is_public_true' => '公开',
        'is_public_false' => '私有',
    ],
    'actions' => [
        'rename' => [
            'label' => '重命名',
            'form_fields' => [
                'name' => '图片名',
            ],
            'success' => '重命名成功'
        ],
        'url' => [
            'label' => '打开图片',
        ],
        'restore_violation' => [
            'label' => '恢复违规图片',
            'modal_description' => '确认将此违规图片恢复为正常状态吗？这将会标记所有相关的违规记录为已处理。',
            'bulk_modal_description' => '确认将选中的违规图片恢复为正常状态吗？这将会标记所有相关的违规记录为已处理。',
            'success' => '违规图片恢复成功',
            'error' => '恢复违规图片失败',
            'bulk_success' => '成功恢复 :count 张违规图片',
            'bulk_error' => ':count 张图片恢复失败',
            'no_violation_selected' => '没有选中违规状态的图片',
        ],
        'update_status' => [
            'success' => '状态更新成功',
            'error' => '状态更新失败',
        ],
    ],
    'status' => [
        'normal' => '正常',
        'pending' => '审核中',
        'violation' => '违规',
    ],
    'normal' => '正常',
    'violation' => '违规',
];
