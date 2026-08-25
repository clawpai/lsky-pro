<?php

declare(strict_types=1);

return [
    'label' => '系统设置',
    'title' => '系统设置',
    'submit' => '保存更改',
    'saved' => '保存成功',
    'basic' => [
        'label' => '常规设置',
        'title' => '常规设置',
        'forms' => [
            'app' => [
                'title' => '应用',
            ],
            'user' => [
                'title' => '用户',
            ],
            'mail' => [
                'title' => '发件',
            ],
            'other' => [
                'title' => '其他',
            ],
        ],
        'fields' => [
            'name' => [
                'label' => '应用名称',
                'placeholder' => '输入应用名称'
            ],
            'url' => [
                'label' => '应用网址',
                'placeholder' => '输入应用网址'
            ],
            'license_key' => [
                'label' => '软件授权密钥',
                'placeholder' => '请输入软件授权密钥'
            ],
            'timezone' => [
                'label' => '系统时区',
                'placeholder' => '请选择系统时区'
            ],
            'ip_gain_method' => [
                'label' => 'IP 获取方式',
                'placeholder' => '请选择 IP 获取方式'
            ],
            'currency' => [
                'label' => '系统货币符号',
                'placeholder' => '请输入系统货币符号，e.g：CNY'
            ],
            'initial_capacity' => [
                'label' => '用户初始容量',
                'placeholder' => '请输入新注册用户初始容量',
            ],
            'locale' => [
                'label' => '默认语言',
                'placeholder' => '请选择系统默认语言'
            ],
            'icp_no' => [
                'label' => '备案号',
                'placeholder' => '输入备案号'
            ],
            'image_driver' => [
                'label' => '图片处理器',
                'placeholder' => '请选择系统默认图片处理器',
                'helper_text' => '注意，切换后需<b>手动重启队列服务</b>使其生效。使用 Libvips 驱动必须确保 PHP 启用 FFI 拓展，该拓展默认是禁用的。一旦切换图片处理器会改变系统所有位置的图片处理方式，已存在的角色组中选择的图片格式可能会失效。',
            ],
        ]
    ],
    'admin' => [
        'label' => '后台设置',
        'title' => '后台设置',
        'forms' => [
            'general' => [
                'title' => '通用',
            ],
        ],
        'fields' => [
            'top_navigation' => [
                'label' => '是否使用顶部导航',
            ],
            'dark_mode' => [
                'label' => '是否启用浅色模式',
            ],
            'default_theme_mode' => [
                'label' => '默认主题模式',
                'placeholder' => '请选择默认主题模式',
                'options' => [
                    'system' => '跟随系统',
                    'light' => '浅色',
                    'dark' => '深色',
                ],
            ],
            'primary_color' => [
                'label' => '主色调',
                'placeholder' => '请选择主色调',
            ]
        ]
    ],
    'site' => [
        'label' => '网站设置',
        'title' => '网站设置',
        'forms' => [
            'general' => [
                'title' => '通用',
            ],
            'background' => [
                'title' => '背景图'
            ],
            'advanced' => [
                'title' => '高级设置'
            ]
        ],
        'fields' => [
            'theme' => [
                'label' => '网站主题',
                'placeholder' => '请选择网站主题'
            ],
            'title' => [
                'label' => '网站标题',
                'placeholder' => '输入网站标题'
            ],
            'subtitle' => [
                'label' => '网站副标题',
                'placeholder' => '输入网站副标题'
            ],
            'homepage_title' => [
                'label' => '首页横幅标题',
                'placeholder' => '输入首页横幅标题'
            ],
            'homepage_description' => [
                'label' => '首页横幅描述',
                'placeholder' => '输入首页横幅描述'
            ],
            'notice' => [
                'label' => '弹出公告',
                'placeholder' => '输入弹出公告，为空则不展示'
            ],
            'homepage_background_image_url' => [
                'label' => '首页背景图链接地址',
                'placeholder' => '输入首页背景图地址，设置此项后自定义图片将失效'
            ],
            'homepage_background_images' => [
                'label' => '首页背景图',
                'placeholder' => '点击上传首页背景图'
            ],
            'auth_page_background_image_url' => [
                'label' => '授权页背景图链接地址',
                'placeholder' => '输入授权页背景图地址，设置此项后自定义图片将失效'
            ],
            'auth_page_background_images' => [
                'label' => '授权页背景图',
                'placeholder' => '点击上传授权页背景图'
            ],
            'custom_css' => [
                'label' => '自定义CSS',
                'placeholder' => '输入自定义CSS代码，将在页面头部载入(不需要使用<script>...</script>标签)'
            ],
            'custom_js' => [
                'label' => '自定义JavaScript',
                'placeholder' => '输入自定义JavaScript代码，将在页面底部载入(不需要使用<script>...</script>标签)'
            ],
        ]
    ],
    'user' => [
        'label' => '用户设置',
        'title' => '用户设置',
        'forms' => [
            'general' => [
                'title' => '通用',
            ],
        ],
        'fields' => [
            'initial_capacity' => [
                'label' => '用户初始容量',
                'placeholder' => '请输入新注册用户初始容量',
            ],
        ]
    ],
    'control' => [
        'label' => '控制中心',
        'title' => '控制中心',
        'forms' => [
            'general' => [
                'title' => '通用',
            ],
        ],
        'fields' => [
            'enable_site' => [
                'label' => '是否启用站点',
                'helper_text' => '仅混合部署模式有效，控制站点是否启用'
            ],
            'enable_registration' => [
                'label' => '是否启用注册',
                'helper_text' => '启用或关闭系统注册功能'
            ],
            'guest_upload' => [
                'label' => '是否允许游客上传',
                'helper_text' => '启用或关闭游客上传文件，游客上传受「系统默认组」控制'
            ],
            'user_email_verify' => [
                'label' => '是否启用邮箱验证',
                'helper_text' => '启用或关闭用户账号邮箱验证功能'
            ],
            'user_phone_verify' => [
                'label' => '是否启用手机号验证',
                'helper_text' => '启用或关闭用户账号手机号验证功能'
            ],
            'enable_stat_api' => [
                'label' => '是否启用统计API',
                'helper_text' => '启用或关闭系统统计信息API接口(默认统计接口访问地址：GET /api/v2/stat{/key?})'
            ],
            'enable_explore' => [
                'label' => '是否启用图片广场',
                'helper_text' => '启用或关闭图片广场功能，图片广场中的图片均为所有用户公开的图片'
            ],
            'enable_stat_api_key' => [
                'label' => '统计API访问密钥',
                'helper_text' => '设置统计API的访问密钥，为空则无需密钥验证',
                'placeholder' => '请输入统计API访问密钥'
            ],
        ]
    ],
    'upgrade' => [
        'label' => '系统升级',
        'title' => '系统升级',
        'btn' => '立即升级',
        'status' => [
            'checking' => '检测中...',
            'upgrading' => '升级中...',
            'completed' => '升级完成，请刷新页面',
            'no_update' => '已经是最新版本',
        ],
        'changelog' => '更新日志',
        'processing' => '正在处理...若长时间停留此处，请检查队列是否正常。',
        'downloading' => '安装包下载中...',
        'installing' => '安装中...',
        'server_failed' => '更新服务器请求失败，请尝试离线安装。',
        'hash_unchanged' => '升级失败：文件哈希值未发生变化，可能安装包损坏或升级过程出错。',
    ],
    'queue' => [
        'label' => '消息队列',
        'title' => '消息队列',
        'tabs' => [
            'jobs' => '运行中的队列',
            'failed_jobs' => '失败的队列',
            'job_batches' => '批量队列',
        ],
        'queue_status' => [
            'title' => '队列服务状态',
            'service_status' => '服务状态',
            'running' => '运行中',
            'stopped' => '已停止',
            'last_processed' => '最后处理时间',
            'oldest_pending' => '最老待处理任务',
            'no_recent_activity' => '无近期活动',
            'no_pending_jobs' => '无待处理任务',
            'just_now' => '刚刚',
            'minutes_ago' => ':minutes 分钟前',
        ],
        'stats' => [
            'total' => '总队列数',
            'pending' => '待处理',
            'reserved' => '处理中',
            'queues' => '队列类型',
        ],
        'actions' => [
            'queue_management' => '队列管理',
            'refresh_status' => '刷新状态',
            'start_queue' => '启动队列',
            'stop_queue' => '停止队列',
            'restart_queue' => '重启队列',
            'clear_all_failed' => '清空失败任务',
            'retry' => '重试',
            'view_exception' => '查看异常',
            'delete' => '删除',
            'bulk_retry' => '批量重试',
            'bulk_delete' => '批量删除',
        ],
        'messages' => [
            'task_type' => '任务类型',
            'unknown_task' => '未知任务',
            'queue_exception_details' => '队列异常详情',
            'failed_task_info' => '失败任务 ID: :id | UUID: :uuid',
            'status_refreshed' => '状态已刷新',
            'start_queue_confirmation' => '确定要在后台启动队列服务吗？这将启动一个守护进程来处理队列任务。',
            'start_confirm_button' => '确认启动',
            'start_cancel_button' => '取消',
            'queue_start_initiated' => '队列启动已发起',
            'queue_start_message' => '队列服务启动命令已执行，请稍等片刻后刷新状态检查',
            'queue_start_message_with_pid' => '队列服务已启动，进程 PID: :pid',
            'queue_start_failed' => '队列启动失败',
            'queue_start_error' => '启动失败：:error',
            'stop_queue_confirmation' => '确定要停止队列服务吗？这将终止所有正在运行的队列进程，正在处理的任务可能会被中断。',
            'stop_confirm_button' => '确认停止',
            'stop_cancel_button' => '取消',
            'queue_stop_initiated' => '队列停止已发起',
            'queue_stop_message' => '队列服务停止命令已执行',
            'queue_stop_failed' => '队列停止失败',
            'queue_stop_error' => '停止失败：:error',
            'queue_stop_no_process' => '未找到运行中的队列进程',
            'restart_queue_confirmation' => '确定要重启队列服务吗？这将优雅地停止当前队列并重新启动，正在处理的任务会完成后再停止。',
            'restart_confirm_button' => '确认重启',
            'restart_cancel_button' => '取消',
            'queue_restart_initiated' => '队列重启已发起',
            'queue_restart_message' => '队列服务重启命令已执行，请稍等片刻后刷新状态检查',
            'queue_restart_message_with_pid' => '队列服务已重启，新进程 PID: :pid',
            'queue_restart_failed' => '队列重启失败',
            'queue_restart_error' => '重启失败：:error',
            'clear_all_failed_confirmation' => '确定要清空所有失败的队列记录吗？此操作将删除所有失败任务的记录，无法恢复。',
            'clear_confirm_button' => '确认清空',
            'clear_cancel_button' => '取消',
            'clear_all_success' => '清空成功',
            'clear_all_success_message' => '已成功清空 :count 个失败队列记录',
            'clear_all_failed' => '清空失败',
            'clear_all_error' => '清空失败：:error',
            'no_failed_jobs' => '无失败任务',
            'no_failed_jobs_message' => '当前没有失败的队列任务需要清理',
            'retry_confirmation' => '确定要重试这个失败的队列任务吗？任务将被重新加入队列。',
            'retry_confirm_button' => '确认重试',
            'retry_cancel_button' => '取消',
            'retry_success' => '重试成功',
            'retry_success_message' => '失败队列任务 #:id 已重新加入队列',
            'retry_failed' => '重试失败',
            'retry_failed_message' => '重试失败：:error',
            'bulk_retry_confirmation' => '确定要重试选中的失败队列任务吗？任务将被重新加入队列。',
            'bulk_retry_success' => '批量重试完成',
            'bulk_retry_success_message' => '重试成功 :success 个任务，失败 :failed 个任务',
            'bulk_retry_failed' => '批量重试失败',
            'bulk_retry_failed_message' => '重试失败，共 :count 个任务重试失败',
            'delete_confirmation' => '确定要删除这个失败的队列记录吗？此操作不可撤销。',
            'delete_confirm_button' => '确认删除',
            'delete_cancel_button' => '取消',
            'delete_success' => '删除成功',
            'delete_success_message' => '失败队列记录 #:id 已被删除',
            'bulk_delete_confirmation' => '确定要删除选中的失败队列记录吗？此操作不可撤销。',
            'bulk_delete_success' => '批量删除成功',
            'bulk_delete_success_message' => '已成功删除 :count 个失败队列记录',
            'no_name_batch' => '未命名批次',
            'not_finished' => '未完成',
            'warning_message' => '⚠️ 请确保排队服务正常运行，否则会影响系统生成缩略图、发送邮件、删除图片等功能。可以使用命令 php artisan queue:work 启动队列处理。',
            'basic_info' => '基本信息',
            'payload_info' => '载荷信息',
            'exception_details' => '异常详情',
        ],
        'fields' => [
            'id' => 'ID',
            'queue' => '队列名称',
            'attempts' => '重试次数',
            'payload' => '载荷',
            'created_at' => '创建时间',
            'available_at' => '可用时间',
            'reserved_at' => '预留时间',
            'uuid' => 'UUID',
            'connection' => '连接',
            'exception' => '异常信息',
            'failed_at' => '失败时间',
            'name' => '批次名称',
            'total_jobs' => '总任务数',
            'pending_jobs' => '待处理任务',
            'failed_jobs' => '失败任务',
            'finished_at' => '完成时间',
            'cancelled_at' => '取消时间',
        ],
    ],
];
