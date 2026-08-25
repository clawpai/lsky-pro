<?php

declare(strict_types=1);

return [
    'model_label' => '邮件驱动器',
    'plural_model_label' => '邮件',
    'columns' => [
        'name' => '储存名称',
        'intro' => '简介',
        'options_provider' => '发信驱动',
        'created_at' => '创建时间',
    ],
    'actions' => [
        'test_connection' => [
            'label' => '测试连接',
            'success' => '邮件连接测试成功！',
            'failed' => '邮件连接测试失败',
            'error_message' => '连接错误: :message',
            'confirm' => '确定要测试当前配置的邮件连接吗？',
            'validation_error' => '表单验证错误',
            'please_fill_form' => '请先完成表单填写',
            'select_provider' => '请先选择邮件驱动器类型',
        ],
    ],
    'form_fields' => [
        'name' => [
            'label' => '名称',
            'placeholder' => '输入名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options' => [
            'provider' => [
                'label' => '邮件驱动',
                'placeholder' => '请选择邮件驱动'
            ],
            'from_address' => [
                'label' => '发件人邮箱地址',
                'placeholder' => '输入发件人邮箱地址'
            ],
            'from_name' => [
                'label' => '发件人名称',
                'placeholder' => '输入发件人名称'
            ],
        ],
        'smtp_options' => [
            'host' => [
                'label' => 'SMTP 服务器地址',
                'placeholder' => '输入 SMTP 服务器地址，例如：smtp.qiye.aliyun.com',
            ],
            'port' => [
                'label' => 'SMTP 服务器端口',
                'placeholder' => '输入 SMTP 服务器端口，例如：465',
            ],
            'encryption' => [
                'label' => 'SMTP 加密方式',
                'placeholder' => '输入 SMTP 加密方式，例如：ssl',
            ],
            'username' => [
                'label' => 'SMTP 用户名',
                'placeholder' => '输入 SMTP 用户名',
            ],
            'password' => [
                'label' => 'SMTP 密码',
                'placeholder' => '输入 SMTP 密码',
            ],
        ],
        'mailgun_options' => [
            'service' => [
                'domain' => [
                    'label' => 'Domain',
                    'placeholder' => '输入 Domain',
                ],
                'secret' => [
                    'label' => 'Secret',
                    'placeholder' => '输入 Secret',
                ],
                'endpoint' => [
                    'label' => 'Endpoint',
                    'placeholder' => '输入 Endpoint',
                ],
                'scheme' => [
                    'label' => 'Scheme',
                    'placeholder' => '输入 Scheme',
                ],
            ]
        ],
        'postmark_options' => [
            'service' => [
                'token' => [
                    'label' => 'Token',
                    'placeholder' => '输入 Token',
                ],
            ]
        ],
        'ses_options' => [
            'service' => [
                'key' => [
                    'label' => 'AccessKeyId',
                    'placeholder' => '输入 AccessKeyId',
                ],
                'secret' => [
                    'label' => 'SecretAccessKey',
                    'placeholder' => '输入 SecretAccessKey',
                ],
                'region' => [
                    'label' => 'Region',
                    'placeholder' => '输入 Region，例如：us-east-1',
                ],
                'token' => [
                    'label' => 'Token',
                    'placeholder' => '输入 Token',
                ]
            ]
        ],
    ],
];
