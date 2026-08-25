<?php

declare(strict_types=1);

return [
    'model_label' => 'Cloud Processing Driver',
    'plural_model_label' => 'Cloud Processing',
    'columns' => [
        'name' => 'Driver Name',
        'intro' => 'Introduction',
        'created_at' => 'Creation Time',
    ],
    'actions' => [],
    'form_tabs' => [
        'basic' => 'Basic',
        'supported_param' => 'Supported Parameters',
        'preset' => 'Preset Versions',
    ],
    'form_fields' => [
        'name' => [
            'label' => 'Driver Name',
            'placeholder' => 'Enter driver name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'options_cache' => [
            'label' => 'Cloud Processing Cache Directory',
            'placeholder' => 'Enter cloud processing cache directory, the absolute path on the server where the program is located',
            'helper_text' => 'After enabling cloud processing, the access URL of this storage will become invalid (replaced by the application domain set by the program). All image access requests will be processed by the program first. Each image\'s first access will be processed by the cloud processing rules, with processing speed determined by image size and server performance. Once the cache file is generated, subsequent accesses will directly output the image without processing. Processed images will be cached in this directory. The cloud processing cache directory must be the absolute path on the server where the program is located.',
            'validation' => [
                'rule_writable' => 'Cloud processing cache directory does not exist or does not have write permissions',
            ],
        ],
        'options_response' => [
            'label' => 'Cloud Processing File Output Method',
            'placeholder' => 'Please select cloud processing file output method',
            'helper_text' => 'Stream output loads cached files into memory via the program and then outputs them to the client through the program, which requires high I/O performance from the server. X-Sendfile outputs specific headers to inform the Web server of the file location, then internally redirects the server to directly output the file to the client. It is recommended to use this method (currently only supports Nginx, requires configuration of pseudo-static use).',
        ],
        'options_presets' => [
            'label' => 'Preset Versions',
            'helper_text' => 'You can set multiple preset versions, each with different processing rules, and define them using parameter p. It supports defining multiple presets simultaneously, for example: kayaks.jpg?p=small,medium',
            'form_fields' => [
                'name' => [
                    'label' => 'Name',
                    'placeholder' => 'Please enter preset parameter name, must be unique, can include English letters, numbers, hyphens (-), or underscores (_).',
                ],
                'is_default' => [
                    'label' => 'Is Default Preset',
                    'helper_text' => 'Whether to set as the default preset. Only one default preset can exist.',
                ],
                'params' => [
                    'label' => 'Preset Parameters',
                    'helper_text' => 'For preset parameter descriptions, please refer to the <a target="_blank" href="https://docs.lsky.pro/guide/process#处理规则">documentation</a>',
                    'key_label' => 'Parameter Name',
                    'value_label' => 'Parameter Value',
                    'key_placeholder' => 'Please enter parameter name, e.g: w',
                    'value_placeholder' => 'Please enter parameter value, e.g: 400',
                ]
            ]
        ],
        'options_supported_params' => [
            'label' => 'Supported Processing Parameters',
            'helper_text' => 'Processing parameters can coexist with preset versions. Choosing processing parameters does not affect the preset version settings. For security reasons, it is recommended to use only preset versions to control image styles.',
        ],
    ],
];