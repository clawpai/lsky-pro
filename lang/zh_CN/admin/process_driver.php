<?php

declare(strict_types=1);

return [
    'model_label' => '云处理驱动器',
    'plural_model_label' => '云处理',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'form_tabs' => [
        'basic' => '基础',
        'supported_param' => '支持参数',
        'preset' => '预设版本',
    ],
    'form_fields' => [
        'name' => [
            'label' => '驱动名称',
            'placeholder' => '输入驱动名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options_cache' => [
            'label' => '云处理缓存目录',
            'placeholder' => '输入云处理缓存目录，程序所在服务器的绝对路径',
            'helper_text' => '开启云处理后，该储存的访问 URL 将失效(会被程序设置的应用域名替代)，所有访问图片请求将优先经过程序。每张图片首次访问都会经过云处理规则进行处理，处理速度由图片大小和服务器性能决定，一旦生成缓存文件后，下次访问则不处理直接输出图片。经过处理的图片将会缓存在该目录中，云处理缓存目录必须是程序所在服务器的绝对路径。',
            'validation' => [
                'rule_writable' => '云处理缓存目录不存在或没有写入权限',
            ],
        ],
        'options_response' => [
            'label' => '云处理文件输出方式',
            'placeholder' => '请选择云处理文件输出方式',
            'helper_text' => '流输出是通过程序加载缓存文件到内存中，然后通过程序输出到客户端，对服务器有着较高的 I/O 性能要求。X-Sendfile 是通过输出特定的 Header 告诉 Web 服务器文件所在位置，然后通过服务器内部重定向，将文件直接输出给客户端，推荐使用该方式(当前仅支持 Nginx，需配置伪静态使用)。',
        ],
        'options_presets' => [
            'label' => '预设版本',
            'helper_text' => '你可以设置多个预设版本，每个版本拥有不同的处理规则，然后通过参数 p 进行定义，支持同时定义多个预设，例如：kayaks.jpg?p=small,medium',
            'form_fields' => [
                'name' => [
                    'label' => '名称',
                    'placeholder' => '请输入预设参数名称，需唯一，可以包含英文字母、数字、中横线(-)或下划线(_)。',
                ],
                'is_default' => [
                    'label' => '是否为默认预设',
                    'helper_text' => '是否为设置为默认预设，默认预设只能存在一个。',
                ],
                'params' => [
                    'label' => '预设参数',
                    'helper_text' => '预设参数说明请<a target="_blank" href="https://docs.lsky.pro/guide/process#处理规则">查阅文档</a>',
                    'key_label' => '参数名',
                    'value_label' => '参数值',
                    'key_placeholder' => '请输入参数名，e.g: w',
                    'value_placeholder' => '请输入参数值，e.g: 400',
                ]
            ]
        ],
        'options_supported_params' => [
            'label' => '支持的处理参数',
            'helper_text' => '处理参数可以和预设版本共存，选择处理参数不影响预设版本的设置。为了安全考虑，建议仅使用预设版本来控制图片样式。'
        ],
    ],
];
