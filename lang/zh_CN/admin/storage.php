<?php

declare(strict_types=1);

return [
    'model_label' => '储存',
    'plural_model_label' => '储存管理',
    'columns' => [
        'name' => '储存名称',
        'intro' => '简介',
        'prefix' => '储存前缀',
        'provider' => '储存类型',
        'created_at' => '创建时间',
    ],
    'actions' => [
        'delete' => [
            'modal_description' => '删除后可能会导致图片无法访问，请谨慎操作。',
        ],
        'test_connection' => [
            'label' => '测试连接',
            'success' => '连接测试成功！',
            'failed' => '连接测试失败',
            'error_message' => '连接错误: :message',
            'confirm' => '确定要测试当前配置的连接吗？',
            'validation_error' => '表单验证错误',
            'please_fill_form' => '请先完成表单填写',
            'select_provider' => '请先选择存储驱动器类型',
        ],
    ],
    'tabs' => [
        'general' => '通用',
        'options' => '配置',
        'processor' => '处理器',
    ],
    'form_fields' => [
        'name' => [
            'label' => '储存名称',
            'placeholder' => '输入储存名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'provider' => [
            'label' => '储存驱动',
            'placeholder' => '请选择储存驱动'
        ],
        'prefix' => [
            'label' => '访问前缀',
            'placeholder' => '输入访问前缀',
            'helper_text' => '例如：http://localhost/uploads 中的 uploads 则表示访问前缀。本地储存和开启云处理后的储存必须设置访问前缀，并且访问前缀不能重复(保留前缀有 admin、css、js、storage、assets、themes)。',
            'validation' => [
                'retention' => '系统保留访问前缀',
                'exists' => '访问前缀已存在',
            ],
        ],
        'scan_driver_id' => [
            'label' => '图片安全驱动',
            'placeholder' => '选择一个图片安全驱动',
            'helper_text' => '图片安全审核驱动，用于图片上传后对图片进行违规审查。',
        ],
        'process_driver_id' => [
            'label' => '云处理驱动',
            'placeholder' => '选择一个云处理驱动',
            'helper_text' => '预设不同的处理规则，通过 url 对支持的图片格式进行处理，不选择则不支持云处理。云处理不会对原图进行修改，而是复制出一份新的版本储存在指定的本地缓存目录中。'
        ],
        'handle_driver_id' => [
            'label' => '图片处理驱动',
            'placeholder' => '选择一个图片处理驱动',
            'helper_text' => '图片上传后通过处理驱动对支持的图片格式进行修改，不选择则保持原图不修改。⚠️注意：在处理驱动处理之前，系统中若检测到上传的图片已经存在，那么会直接对已存在的图片进行处理。这个操作意味着原图片可能会被处理成其他格式，图片的基本信息也会因为处理后而被修改。'
        ],
        'options' => [
            'public_url' => [
                'label' => '访问 URL',
                'placeholder' => '输入访问 URL'
            ],
            'naming_rule' => [
                'label' => '命名规则',
                'placeholder' => '输入命名规则',
                'helper_text' => '更多命名规则请查阅<a href="https://docs.lsky.pro/guide/storage#%E6%94%AF%E6%8C%81%E7%9A%84%E6%96%87%E4%BB%B6%E5%91%BD%E5%90%8D%E8%A7%84%E5%88%99" target="_blank">文档</a>',
            ],
            'options' => [
                'label' => '自定义配置',
                'key_label' => '配置名',
                'value_label' => '配置值',
                'key_placeholder' => '输入配置名，例如：params.ACL',
                'value_placeholder' => '输入配置值，例如：public-read',
                'helper_text' => '自定义配置，配置名可以使用 "." 符号表示配置层级，例如输入 params.ACL 将会被转换成 [\'params\' => [\'ACL\' => \'配置值\']]。',
            ],
        ],
        'thumbnail' => [
            'generate_thumbnail' => [
                'label' => '生成缩略图',
                'helper_text' => '启用后将为上传的图片生成缩略图，关闭后将不生成缩略图并且无需配置缩略图相关参数',
            ],
            'max_size' => [
                'label' => '缩略图最大尺寸',
                'placeholder' => '输入缩略图最大尺寸（像素）',
                'helper_text' => '缩略图的最大宽度或高度，范围：100-4000 像素，默认：800',
            ],
            'quality' => [
                'label' => '缩略图质量',
                'placeholder' => '输入缩略图质量（1-100）',
                'helper_text' => '缩略图的压缩质量，数值越高质量越好，范围：10-100，默认：90',
            ],
        ],
        'local_options' => [
            'root' => [
                'label' => '储存目录',
                'placeholder' => '输入储存目录，绝对路径',
                'helper_text' => '储存目录必须是绝对路径。',
            ],
        ],
        's3_options' => [
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => '输入 Endpoint，例如：https://s3.amazonaws.com',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => '输入 Region，例如：us-east-1',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => '输入 Bucket',
            ],
            'use_path_style_endpoint' => [
                'label' => 'Path style',
                'helper_text' => '1. 虚拟主机样式（Virtual Hosted-Style）URL：这是默认的 URL 样式，它基于存储桶和对象的 DNS 名称。例如 https://bucket-name.s3.amazonaws.com/object-key<br>2. 路径样式（Path-Style）URL：这种 URL 样式在主机名后使用存储桶名称作为路径的一部分。例如 https://s3.amazonaws.com/bucket-name/object-key',
            ],
            'access_key_id' => [
                'label' => 'AccessKeyId',
                'placeholder' => '输入 AccessKeyId',
            ],
            'secret_access_key' => [
                'label' => 'SecretAccessKey',
                'placeholder' => '输入 SecretAccessKey',
            ],
        ],
        'oss_options' => [
            'endpoint' => [
                'label' => '外网 Endpoint',
                'placeholder' => '输入 Endpoint，例如：oss-cn-shanghai.aliyuncs.com',
            ],
            'internal' => [
                'label' => '内网 Endpoint',
                'placeholder' => '输入内网 Endpoint，内网上传地址，填写即启用，例如：oss-cn-shanghai-internal.aliyuncs.com',
            ],
            'is_cname' => [
                'label' => 'Is Cname',
                'helper_text' => '若 Endpoint 为自定义域名，请打开此项。',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => '输入 Region，例如：cn-shanghai',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => '输入 Bucket',
            ],
            'access_key_id' => [
                'label' => 'AccessKeyId',
                'placeholder' => '输入 AccessKeyId',
            ],
            'access_key_secret' => [
                'label' => 'AccessKeySecret',
                'placeholder' => '输入 AccessKeySecret',
            ],
        ],
        'cos_options' => [
            'app_id' => [
                'label' => 'App Id',
                'placeholder' => '输入 App Id',
            ],
            'secret_id' => [
                'label' => 'Secret Id',
                'placeholder' => '输入 Secret Id',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => '输入 Secret Key',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => '输入 Region，例如：ap-guangzhou',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => '输入 Bucket',
            ],
        ],
        'qiniu_options' => [
            'access_key' => [
                'label' => 'Access Key',
                'placeholder' => '输入 Access Key',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => '输入 Secret Key',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => '输入 Bucket',
            ],
            'domain' => [
                'label' => '域名',
                'placeholder' => '输入自定义域名',
                'helper_text' => '没有配置会导致无法使用云处理',
            ],
        ],
        'upyun_options' => [
            'service' => [
                'label' => '服务名',
                'placeholder' => '输入服务名',
            ],
            'operator' => [
                'label' => '操作员',
                'placeholder' => '输入操作员',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
        ],
        'sftp_options' => [
            'host' => [
                'label' => '主机地址',
                'placeholder' => '输入主机地址',
            ],
            'username' => [
                'label' => '用户名',
                'placeholder' => '输入用户名',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
            'private_key' => [
                'label' => '私钥',
                'placeholder' => '使用私钥连接请输入私钥',
            ],
            'passphrase' => [
                'label' => '私钥口令',
                'placeholder' => '使用私钥连接请输入私钥口令',
            ],
            'port' => [
                'label' => '端口',
                'placeholder' => '输入端口，默认为 22',
            ],
            'use_agent' => [
                'label' => '使用 SSH 代理',
            ],
            'timeout' => [
                'label' => '连接超时时间',
                'placeholder' => '输入连接超时时间，默认为 10 秒',
            ],
            'max_tries' => [
                'label' => '连接最大尝试次数',
                'placeholder' => '输入连接最大尝试次数，默认为 4 次',
            ],
            'host_fingerprint' => [
                'label' => '主机指纹',
                'placeholder' => '输入主机指纹',
            ],
        ],
        'ftp_options' => [
            'host' => [
                'label' => '主机地址',
                'placeholder' => '输入主机地址',
            ],
            'username' => [
                'label' => '用户名',
                'placeholder' => '输入用户名',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
            'port' => [
                'label' => '端口',
                'placeholder' => '输入端口，默认为 21',
            ],
            'timeout' => [
                'label' => '连接超时时间',
                'placeholder' => '输入连接超时时间，默认为 90 秒',
            ],
            'ssl' => [
                'label' => '是否使用 SSL 连接',
            ],
            'passive' => [
                'label' => '是否使用被动模式',
            ],
            'transfer_mode' => [
                'label' => '传输模式',
                'placeholder' => '请选择传输模式，默认为 FTP_BINARY',
            ],
            'ignore_passive_address' => [
                'label' => '是否忽略被动模式下的远程 IP 地址',
            ],
            'enable_timestamps_on_unix_listings' => [
                'label' => '是否启用 Unix 时间戳',
            ],
            'utf8' => [
                'label' => '是否启用 UTF-8 编码',
            ],
            'recurse_manually' => [
                'label' => '是否手动递归',
            ],
        ],
        'webdav_options' => [
            'base_uri' => [
                'label' => '连接地址',
                'placeholder' => '输入连接地址',
            ],
            'username' => [
                'label' => '用户名',
                'placeholder' => '输入用户名',
            ],
            'password' => [
                'label' => '密码',
                'placeholder' => '输入密码',
            ],
            'auth_type' => [
                'label' => '认证方式',
                'placeholder' => '请选择认证方式，默认 Auto',
            ],
        ],
    ],
];
