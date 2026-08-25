<?php

declare(strict_types=1);

return [
    'model_label' => '图片安全驱动器',
    'plural_model_label' => '图片安全',
    'columns' => [
        'name' => '驱动名称',
        'intro' => '简介',
        'options_provider' => '审核驱动',
        'created_at' => '创建时间',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => '驱动名称',
            'placeholder' => '输入驱动名称',
        ],
        'intro' => [
            'label' => '简介',
            'placeholder' => '输入简介',
        ],
        'options' => [
            'provider' => [
                'label' => '审核驱动',
                'placeholder' => '请选择审核驱动'
            ],
        ],
        'options_is_sync' => [
            'label' => '同步审核',
            'helper_text' => '默认为异步(上传成功后通过异步队列进行审核)，如果设置为同步审核，上传图片时检测为异常图片则直接拒绝上传，上传的图片不会被保存且不存在违规记录。注意，同步审核会使上传时间增加。',
        ],
        'options_violation_store_dir' => [
            'label' => '违规图片转移目录',
            'placeholder' => '违规图片转移目录，为空则不转移。e.g：violations/photos',
            'helper_text' => '此目录相对于储存的根目录。如果不为空，违规的图片会被转移至此目录中，文件名称由系统随机命名，该图片的访问链接也会同时发生改变。注意，被转移的图片无法恢复。',
        ],
        'aliyun_v1_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => '请输入 Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => '请输入 Access Key Secret',
            ],
            'region_id' => [
                'label' => 'Region ID',
                'placeholder' => '请输入 Region ID',
            ],
            'scenes' => [
                'label' => '审核场景',
                'options' => [
                    'porn' => '图片智能鉴黄',
                    'terrorism' => '图片暴恐涉政',
                    'ad' => '图文违规',
                    'qrcode' => '图片二维码',
                    'live' => '图片不良场景',
                    'logo' => '图片 Logo'
                ],
            ],
            'biz_type' => [
                'label' => 'Biz Type',
                'placeholder' => '请输入业务场景 Biz Type',
            ],
        ],
        'aliyun_v2_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => '请输入 Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => '请输入 Access Key Secret',
            ],
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => '请输入 Endpoint',
            ],
            'service' => [
                'label' => 'Service',
                'placeholder' => '请输入 Service',
            ],
            'http_proxy' => [
                'label' => 'Http 代理',
                'placeholder' => '输入 Http 代理，例如：http://10.10.xx.xx:xxxx',
            ],
            'https_proxy' => [
                'label' => 'Https 代理',
                'placeholder' => '输入 Https 代理，例如：https://10.10.xx.xx:xxxx',
            ],
        ],
        'tencent_options' => [
            'secret_id' => [
                'label' => 'Secret ID',
                'placeholder' => '请输入 Secret ID',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => '请输入 Secret Key',
            ],
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => '请输入 Endpoint，例如：ims.tencentcloudapi.com',
            ],
            'region_id' => [
                'label' => 'Region Id',
                'placeholder' => '请输入 Region Id',
            ],
            'biz_type' => [
                'label' => 'Biz Type',
                'placeholder' => '请输入业务场景 Biz Type',
            ],
        ],
        'nsfw_js_options' => [
            'endpoint' => [
                'label' => '接口地址',
                'placeholder' => '请输入接口地址，例如：http(s)://domain.com/classify',
            ],
            'attr_name' => [
                'label' => '表单属性名称',
                'placeholder' => '请输入表单属性名称，例如：image',
            ],
            'threshold' => [
                'label' => '阈值',
                'placeholder' => '请输入阈值，例如：60',
                'helper_text' => '阈值是指图片违规程度上限，取值 1-100 之间，数值越低审核越严格',
            ],
            'scenes' => [
                'label' => '审核场景',
                'options' => [
                    'drawing' => '适合工作环境的绘画（包括动漫）',
                    'hentai' => '黄色和色情绘画',
                    'neutral' => '适合工作环境的中性图片',
                    'porn' => '色情图片，性行为',
                    'sexy' => '性感的图片，非色情内容',
                ],
            ],
        ],
        'moderate_content_options' => [
            'api_key' => [
                'label' => 'API Key',
                'placeholder' => '请输入 API Key',
            ]
        ],
    ],
];
