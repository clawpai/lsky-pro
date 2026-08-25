<?php

declare(strict_types=1);

return [
    'model_label' => 'Image Security Driver',
    'plural_model_label' => 'Image Security',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'options_provider' => 'Review Driver',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter driver name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'options' => [
            'provider' => [
                'label' => 'Review Driver',
                'placeholder' => 'Please select review driver',
            ],
        ],
        'options_is_sync' => [
            'label' => 'Synchronous Review',
            'helper_text' => 'Default is asynchronous (reviewed via asynchronous queue after successful upload). If set to synchronous review, abnormal images will be directly rejected during upload, and uploaded images will not be saved nor have violation records. Note that synchronous review will increase upload time.',
        ],
        'options_violation_store_dir' => [
            'label' => 'Violation Image Transfer Directory',
            'placeholder' => 'Violation image transfer directory. Leave empty to not transfer. e.g.: violations/photos',
            'helper_text' => 'This directory is relative to the root directory of the storage. If not empty, violation images will be transferred to this directory with system-randomly named file names, and the image access links will also change accordingly. Note that transferred images cannot be restored.',
        ],
        'aliyun_v1_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => 'Enter Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => 'Enter Access Key Secret',
            ],
            'region_id' => [
                'label' => 'Region ID',
                'placeholder' => 'Enter Region ID',
            ],
            'scenes' => [
                'label' => 'Review Scenes',
                'options' => [
                    'porn' => 'Image Intelligent Porn Detection',
                    'terrorism' => 'Image Terrorism and Political Content',
                    'ad' => 'Image and Text Violations',
                    'qrcode' => 'Image QR Code',
                    'live' => 'Image Inappropriate Scenes',
                    'logo' => 'Image Logo',
                ],
            ],
            'biz_type' => [
                'label' => 'Biz Type',
                'placeholder' => 'Enter business scenario Biz Type',
            ],
        ],
        'aliyun_v2_options' => [
            'access_key_id' => [
                'label' => 'Access Key ID',
                'placeholder' => 'Enter Access Key ID',
            ],
            'access_key_secret' => [
                'label' => 'Access Key Secret',
                'placeholder' => 'Enter Access Key Secret',
            ],
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => 'Enter Endpoint',
            ],
            'service' => [
                'label' => 'Service',
                'placeholder' => 'Please enter Service',
            ],
            'http_proxy' => [
                'label' => 'HTTP Proxy',
                'placeholder' => 'Enter HTTP proxy, e.g.: http://10.10.xx.xx:xxxx',
            ],
            'https_proxy' => [
                'label' => 'HTTPS Proxy',
                'placeholder' => 'Enter HTTPS proxy, e.g.: https://10.10.xx.xx:xxxx',
            ],
        ],
        'tencent_options' => [
            'secret_id' => [
                'label' => 'Secret ID',
                'placeholder' => 'Enter Secret ID',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => 'Enter Endpoint, e.g.: ims.tencentcloudapi.com',
            ],
            'region_id' => [
                'label' => 'Region ID',
                'placeholder' => 'Enter Region ID',
            ],
            'biz_type' => [
                'label' => 'Biz Type',
                'placeholder' => 'Enter business scenario Biz Type',
            ],
        ],
        'nsfw_js_options' => [
            'endpoint' => [
                'label' => 'API Endpoint',
                'placeholder' => 'Enter API endpoint, e.g.: http(s)://domain.com/classify',
            ],
            'attr_name' => [
                'label' => 'Form Attribute Name',
                'placeholder' => 'Enter form attribute name, e.g.: image',
            ],
            'threshold' => [
                'label' => 'Threshold',
                'placeholder' => 'Enter threshold, e.g.: 60',
                'helper_text' => 'Threshold refers to the maximum degree of image violation, ranging from 1-100. The lower the value, the stricter the review.',
            ],
            'scenes' => [
                'label' => 'Review Scenes',
                'options' => [
                    'drawing' => 'Suitable for Work Environment Drawings (including anime)',
                    'hentai' => 'Yellow and Pornographic Drawings',
                    'neutral' => 'Suitable for Work Environment Neutral Images',
                    'porn' => 'Pornographic Images, Sexual Activity',
                    'sexy' => 'Sexy Images, Non-Pornographic Content',
                ],
            ],
        ],
        'moderate_content_options' => [
            'api_key' => [
                'label' => 'API Key',
                'placeholder' => 'Enter API Key',
            ],
        ],
    ],
];