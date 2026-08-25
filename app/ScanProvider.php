<?php

namespace App;

/**
 * 图片安全驱动枚举
 */
enum ScanProvider: string
{
    /**
     * 阿里云内容安全 1.0
     * @link https://help.aliyun.com/document_detail/2360979.html
     */
    case AliyunV1 = 'aliyun_v1';

    /**
     * 阿里云内容安全增强版
     * @link https://help.aliyun.com/document_detail/467825.html
     */
    case AliyunV2 = 'aliyun_v2';

    /**
     * 腾讯云图片内容安全
     * @link https://cloud.tencent.com/document/product/1125/37105
     */
    case Tencent = 'tencent';

    /**
     * nsfwjs
     * @link https://github.com/infinitered/nsfwjs
     */
    case NsfwJS = 'nsfw_js';

    /**
     * ModerateContent
     * @link https://www.moderatecontent.com
     */
    case ModerateContent = 'moderate_content';
}
