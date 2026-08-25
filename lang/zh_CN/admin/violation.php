<?php

declare(strict_types=1);

return [
    'model_label' => '违规记录',
    'plural_model_label' => '违规记录',
    'columns' => [
        'user' => [
            'name' => '用户名',
            'email' => '邮箱',
            'phone' => '手机号',
            'violations_count' => '用户违规次数',
        ],
        'photo' => [
            'group' => [
                'name' => '所在组名称',
            ],
            'storage' => [
                'name' => '所在储存名称',
            ],
            'thumbnail_url' => '预览图',
            'public_url' => '图片',
            'name' => '文件名',
            'mimetype' => '文件类型',
            'size' => '文件大小',
            'md5' => 'MD5',
            'sha1' => 'SHA-1',
            'ip_address' => '上传 IP',
            'created_at' => '上传时间',
        ],
        'reason' => '违规原因',
        'status' => '状态',
        'handled_at' => '处理时间',
        'created_at' => '记录时间',
    ],
    'filters' => [
        'status' => '状态',
        'user' => '用户',
    ],
    'actions' => [
        'handle' => [
            'label' => '标记为已处理',
            'success' => '处理成功',
        ],
        'delete' => [
            'label' => '删除',
        ],
        'delete_photo' => [
            'label' => '删除图片记录',
            'modal_description' => '确认删除图片的记录以及物理图片(除了缩略图)并标记为已处理？',
            'success' => '删除成功',
        ],
        'delete_violation' => [
            'modal_description' => '确认删除此违规记录吗？如果是该图片的最后一条未处理违规记录，图片状态将恢复为正常。',
            'bulk_modal_description' => '确认删除选中的违规记录吗？对于没有其他未处理违规记录的图片，其状态将恢复为正常。',
            'success' => '违规记录删除成功',
            'error' => '违规记录删除失败',
            'bulk_success' => '成功删除 :count 条违规记录',
            'bulk_error_exception' => '批量删除违规记录时发生错误',
            'no_records_deleted' => '没有记录被删除',
        ],
    ],
];
