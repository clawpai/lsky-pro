<?php

declare(strict_types=1);

namespace App\Drivers\Storage;

use AlphaSnow\Flysystem\Aliyun\AliyunFactory;
use App\Contracts\StorageAbstract;
use Illuminate\Support\Arr;
use League\Flysystem\FilesystemAdapter;

/**
 * 阿里云 OSS
 */
class OssStorage extends StorageAbstract
{
    public function getAdapter(): FilesystemAdapter
    {
        return (new AliyunFactory)->createAdapter([
            "prefix" => trim($this->getPrefix(), '/'),
            "access_key_id" => $this->getConfig('access_key_id'),               // 必填, 阿里云的AccessKeyId
            "access_key_secret" => $this->getConfig('access_key_secret'),           // 必填, 阿里云的AccessKeySecret
            "bucket" => $this->getConfig('bucket'),                      // 必填, 对象存储的Bucket, 示例: my-bucket
            "endpoint" => $this->getConfig('endpoint'),                    // 必填, 对象存储的Endpoint, 示例: oss-cn-shanghai.aliyuncs.com
            "internal" => $this->getConfig('internal'),            // 选填, 内网上传地址,填写即启用 示例: oss-cn-shanghai-internal.aliyuncs.com
            "domain" => null,                                    // 选填, 绑定域名,填写即启用 示例: oss.my-domain.com
            "is_cname" => $this->getConfig('is_cname', false),           // 选填, 若Endpoint为自定义域名，此项要为true，见：https://github.com/aliyun/aliyun-oss-php-sdk/blob/572d0f8e099e8630ae7139ed3fdedb926c7a760f/src/OSS/OssClient.php#L113C1-L122C78
            "signatureVersion" => 'v4',                                    // 选填, 选择使用v1或v4签名版本
            "region" => $this->getConfig('region'),                      // 选填, 仅在使用v4签名版本时启用, 示例: cn-shanghai
            "options" => Arr::undot($this->getConfig('options', [])),   // 选填, 添加全局配置参数, 示例: [\OSS\OssClient::OSS_CHECK_MD5 => false]
        ]);
    }
}