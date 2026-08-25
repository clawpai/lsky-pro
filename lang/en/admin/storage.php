<?php

declare(strict_types=1);

return [
    'model_label' => 'Storage',
    'plural_model_label' => 'Storage Management',
    'columns' => [
        'name' => 'Storage Name',
        'intro' => 'Introduction',
        'prefix' => 'Storage Prefix',
        'provider' => 'Storage Type',
        'created_at' => 'Creation Time',
    ],
    'actions' => [
        'delete' => [
            'modal_description' => 'Deleting may cause images to become inaccessible. Please proceed with caution.',
        ],
        'test_connection' => [
            'label' => 'Test Connection',
            'success' => 'Connection test successful!',
            'failed' => 'Connection test failed',
            'error_message' => 'Connection error: :message',
            'confirm' => 'Are you sure you want to test the connection with current configuration?',
            'validation_error' => 'Form Validation Error',
            'please_fill_form' => 'Please complete the form first',
            'select_provider' => 'Please select a storage driver type first',
        ],
    ],
    'tabs' => [
        'general' => 'General',
        'options' => 'Configuration',
        'processor' => 'Processor',
    ],
    'form_fields' => [
        'name' => [
            'label' => 'Storage Name',
            'placeholder' => 'Enter storage name',
        ],
        'intro' => [
            'label' => 'Introduction',
            'placeholder' => 'Enter introduction',
        ],
        'provider' => [
            'label' => 'Storage Driver',
            'placeholder' => 'Please select storage driver',
        ],
        'prefix' => [
            'label' => 'Access Prefix',
            'placeholder' => 'Enter access prefix',
            'helper_text' => 'For example, in http://localhost/uploads, "uploads" is the access prefix. Local storage and storage with cloud processing enabled must set an access prefix, and the access prefix must not be duplicated (reserved prefixes include admin, css, js, storage, assets, themes).',
            'validation' => [
                'retention' => 'System reserved access prefixes',
                'exists' => 'Access prefix already exists',
            ],
        ],
        'scan_driver_id' => [
            'label' => 'Image Security Driver',
            'placeholder' => 'Select an image security driver',
            'helper_text' => 'Image security review driver used to inspect uploaded images for violations.',
        ],
        'process_driver_id' => [
            'label' => 'Cloud Processing Driver',
            'placeholder' => 'Select a cloud processing driver',
            'helper_text' => 'Preset different processing rules to handle supported image formats via URL. If not selected, cloud processing is not supported. Cloud processing does not modify the original image but creates a new version stored in the specified local cache directory.',
        ],
        'handle_driver_id' => [
            'label' => 'Image Processing Driver',
            'placeholder' => 'Select an image processing driver',
            'helper_text' => 'After image upload, the processing driver modifies supported image formats. If not selected, the original image remains unmodified. ⚠️ Note: Before the processing driver processes the image, if the system detects that the uploaded image already exists, it will directly process the existing image. This operation means the original image might be converted to another format, and the basic information of the image will be modified after processing.',
        ],
        'options' => [
            'public_url' => [
                'label' => 'Access URL',
                'placeholder' => 'Enter Access URL',
            ],
            'naming_rule' => [
                'label' => 'Naming Rule',
                'placeholder' => 'Enter Naming Rule',
                'helper_text' => 'For more naming rules, please refer to the <a href="https://docs.lsky.pro/guide/storage#%E6%94%AF%E6%8C%81%E7%9A%84%E6%96%87%E4%BB%B6%E5%91%BD%E5%90%8D%E8%A7%84%E5%88%99" target="_blank">documentation</a>',
            ],
            'options' => [
                'label' => 'Custom Configuration',
                'key_label' => 'Configuration Name',
                'value_label' => 'Configuration Value',
                'key_placeholder' => 'Enter configuration name, e.g.: params.ACL',
                'value_placeholder' => 'Enter configuration value, e.g.: public-read',
                'helper_text' => 'Custom configuration, configuration name can use "." symbol to represent configuration hierarchy, for example entering params.ACL will be converted to [\'params\' => [\'ACL\' => \'configuration value\']].',
            ],
        ],
        'thumbnail' => [
            'generate_thumbnail' => [
                'label' => 'Generate Thumbnail',
                'helper_text' => 'When enabled, thumbnails will be generated for uploaded images. When disabled, no thumbnails will be generated and thumbnail-related parameters are not required.',
            ],
            'max_size' => [
                'label' => 'Thumbnail Max Size',
                'placeholder' => 'Enter thumbnail max size (pixels)',
                'helper_text' => 'Maximum width or height of thumbnail, range: 100-4000 pixels, default: 800',
            ],
            'quality' => [
                'label' => 'Thumbnail Quality',
                'placeholder' => 'Enter thumbnail quality (1-100)',
                'helper_text' => 'Compression quality of thumbnail, higher value means better quality, range: 10-100, default: 90',
            ],
        ],
        'local_options' => [
            'root' => [
                'label' => 'Storage Directory',
                'placeholder' => 'Enter storage directory, absolute path',
                'helper_text' => 'Storage directory must be an absolute path.',
            ],
        ],
        's3_options' => [
            'endpoint' => [
                'label' => 'Endpoint',
                'placeholder' => 'Enter Endpoint, e.g.: https://s3.amazonaws.com',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => 'Enter Region, e.g.: us-east-1',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => 'Enter Bucket',
            ],
            'use_path_style_endpoint' => [
                'label' => 'Path Style',
                'helper_text' => '1. Virtual Hosted-Style URL: This is the default URL style based on the bucket and object DNS names. For example, https://bucket-name.s3.amazonaws.com/object-key<br>2. Path-Style URL: This URL style uses the bucket name as part of the path after the hostname. For example, https://s3.amazonaws.com/bucket-name/object-key',
            ],
            'access_key_id' => [
                'label' => 'AccessKeyId',
                'placeholder' => 'Enter AccessKeyId',
            ],
            'secret_access_key' => [
                'label' => 'SecretAccessKey',
                'placeholder' => 'Enter SecretAccessKey',
            ],
        ],
        'oss_options' => [
            'endpoint' => [
                'label' => 'External Endpoint',
                'placeholder' => 'Enter Endpoint, e.g.: oss-cn-shanghai.aliyuncs.com',
            ],
            'internal' => [
                'label' => 'Internal Endpoint',
                'placeholder' => 'Enter Internal Endpoint. Internal upload address, enable by filling, e.g.: oss-cn-shanghai-internal.aliyuncs.com',
            ],
            'is_cname' => [
                'label' => 'Is CNAME',
                'helper_text' => 'If Endpoint is a custom domain, please enable this option.',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => 'Enter Region, e.g.: cn-shanghai',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => 'Enter Bucket',
            ],
            'access_key_id' => [
                'label' => 'AccessKeyId',
                'placeholder' => 'Enter AccessKeyId',
            ],
            'access_key_secret' => [
                'label' => 'AccessKeySecret',
                'placeholder' => 'Enter AccessKeySecret',
            ],
        ],
        'cos_options' => [
            'app_id' => [
                'label' => 'App ID',
                'placeholder' => 'Enter App ID',
            ],
            'secret_id' => [
                'label' => 'Secret ID',
                'placeholder' => 'Enter Secret ID',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'region' => [
                'label' => 'Region',
                'placeholder' => 'Enter Region, e.g.: ap-guangzhou',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => 'Enter Bucket',
            ],
        ],
        'qiniu_options' => [
            'access_key' => [
                'label' => 'Access Key',
                'placeholder' => 'Enter Access Key',
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'placeholder' => 'Enter Secret Key',
            ],
            'bucket' => [
                'label' => 'Bucket',
                'placeholder' => 'Enter Bucket',
            ],
            'domain' => [
                'label' => 'Domain',
                'placeholder' => 'Enter custom domain',
                'helper_text' => 'Not configuring this will cause cloud processing to fail',
            ],
        ],
        'upyun_options' => [
            'service' => [
                'label' => 'Service Name',
                'placeholder' => 'Enter Service Name',
            ],
            'operator' => [
                'label' => 'Operator',
                'placeholder' => 'Enter Operator',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter Password',
            ],
        ],
        'sftp_options' => [
            'host' => [
                'label' => 'Host Address',
                'placeholder' => 'Enter host address',
            ],
            'username' => [
                'label' => 'Username',
                'placeholder' => 'Enter username',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter password',
            ],
            'private_key' => [
                'label' => 'Private Key',
                'placeholder' => 'Enter private key if using private key connection',
            ],
            'passphrase' => [
                'label' => 'Private Key Passphrase',
                'placeholder' => 'Enter private key passphrase if using private key connection',
            ],
            'port' => [
                'label' => 'Port',
                'placeholder' => 'Enter port, default is 22',
            ],
            'use_agent' => [
                'label' => 'Use SSH Agent',
            ],
            'timeout' => [
                'label' => 'Connection Timeout',
                'placeholder' => 'Enter connection timeout, default is 10 seconds',
            ],
            'max_tries' => [
                'label' => 'Maximum Connection Attempts',
                'placeholder' => 'Enter maximum connection attempts, default is 4',
            ],
            'host_fingerprint' => [
                'label' => 'Host Fingerprint',
                'placeholder' => 'Enter host fingerprint',
            ],
        ],
        'ftp_options' => [
            'host' => [
                'label' => 'Host Address',
                'placeholder' => 'Enter host address',
            ],
            'username' => [
                'label' => 'Username',
                'placeholder' => 'Enter username',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter password',
            ],
            'port' => [
                'label' => 'Port',
                'placeholder' => 'Enter port, default is 21',
            ],
            'timeout' => [
                'label' => 'Connection Timeout',
                'placeholder' => 'Enter connection timeout, default is 90 seconds',
            ],
            'ssl' => [
                'label' => 'Use SSL Connection',
            ],
            'passive' => [
                'label' => 'Use Passive Mode',
            ],
            'transfer_mode' => [
                'label' => 'Transfer Mode',
                'placeholder' => 'Please select transfer mode, default is FTP_BINARY',
            ],
            'ignore_passive_address' => [
                'label' => 'Ignore Remote IP Address in Passive Mode',
            ],
            'enable_timestamps_on_unix_listings' => [
                'label' => 'Enable Unix Timestamps',
            ],
            'utf8' => [
                'label' => 'Enable UTF-8 Encoding',
            ],
            'recurse_manually' => [
                'label' => 'Manual Recursion',
            ],
        ],
        'webdav_options' => [
            'base_uri' => [
                'label' => 'Connection Address',
                'placeholder' => 'Enter connection address',
            ],
            'username' => [
                'label' => 'Username',
                'placeholder' => 'Enter username',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => 'Enter password',
            ],
            'auth_type' => [
                'label' => 'Authentication Method',
                'placeholder' => 'Please select authentication method, default is Auto',
            ],
        ],
    ],
];