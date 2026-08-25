<?php

declare(strict_types=1);

namespace App\Services;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Result\Result;
use AlibabaCloud\Green\Green;
use AlibabaCloud\SDK\Green\V20220302\Models\ImageModerationRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use App\Exceptions\ServiceException;
use App\Facades\StorageService;
use App\Models\Photo;
use App\ScanProvider;
use App\ScanResultStatus;
use ArrayObject;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Filesystem;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Ims\V20201229\ImsClient;
use TencentCloud\Ims\V20201229\Models\LabelResult;
use Throwable;

class ScanService
{
    /**
     * 同步检测图片，返回审核结果
     * 不同的驱动兼容性不同，如果遇到无法检测的图片则返回 0
     *
     * @param Photo $photo 图片
     * @param ArrayObject $options 驱动配置
     * @return array<ScanResultStatus, array>
     * @throws ServiceException
     */
    public function syncCheck(Photo $photo, ArrayObject $options): array
    {
        return match (ScanProvider::from($options['provider'])) {
            ScanProvider::AliyunV1 => $this->aliyunV1SyncCheck($photo, $options),
            ScanProvider::AliyunV2 => $this->aliyunV2SyncCheck($photo, $options),
            ScanProvider::Tencent => $this->tencentSyncCheck($photo, $options),
            ScanProvider::NsfwJS => $this->nsfwJSSyncCheck($photo, $options),
            ScanProvider::ModerateContent => $this->moderateContentSyncCheck($photo, $options),
        };
    }

    /**
     * 阿里云图片内容安全 v1.0 同步检测
     *
     * 图片支持以下格式：PNG、JPG、JPEG、BMP、GIF、WEBP。
     * 图片大小限制为20 MB以内（适用于同步和异步调用），高度或者宽度不能超过30,000像素（px），且图像总像素不超过2.5亿（px）。
     * 其中，GIF格式的图片，图像总像素不超过4,194,304（px），高度或者宽度不能超过30,000像素（px）。
     * 图片下载时间限制为3秒内，如果下载时间超过3秒，返回下载超时。
     * 图片像素建议不低于256*256（px），像素过低可能会影响识别效果。
     * 图片检测接口的响应时间依赖图片的下载时间。请保证被检测图片所在的存储服务稳定可靠，建议您使用阿里云OSS存储或者CDN缓存等。
     *
     * @link https://help.aliyun.com/document_detail/70292.html
     * @param Photo $photo
     * @param ArrayObject $options
     * @return array
     * @throws ServiceException
     */
    private function aliyunV1SyncCheck(Photo $photo, ArrayObject $options): array
    {
        $supportedFormats = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'];
        if (!in_array($photo->extension, $supportedFormats) || $photo->size > 20971520) {
            return $this->result(ScanResultStatus::Normal);
        }

        try {
            AlibabaCloud::accessKeyClient($options['access_key_id'], $options['access_key_secret'])
                ->timeout(10) // 超时10秒，使用该客户端且没有单独设置的请求都使用此设置。
                ->connectTimeout(3) // 连接超时3秒，当小于1秒时，则自动转换为毫秒，使用该客户端且没有单独设置的请求都使用此设置。
                ->regionId($options['region_id'])
                ->asDefaultClient();

            $task = ['dataId' => (string)$photo->id, 'url' => $photo->public_url];

            $response = Green::v20180509()->imageSyncScan()
                ->timeout(10) // 超时10秒，request超时设置，仅对当前请求有效。
                ->connectTimeout(3) // 连接超时3秒，当小于1秒时，则自动转换为毫秒，request超时设置，仅对当前请求有效。
                ->body(json_encode(array_filter([
                    'tasks' => [$task],
                    'scenes' => $options['scenes'],
                    'bizType' => $options['biz_type'],
                ])))
                ->request();

            if (200 != $response->get('code')) {
                throw new ServiceException($response->get('msg'));
            }

            /** @var array $result */
            $data = $response->get('data')[0];
            if ($data['code'] !== 200) {
                throw new ServiceException($data['msg']);
            }

            $status = ScanResultStatus::Normal;
            $reasons = [];
            foreach ($data['results'] as $result) {
                $suggestion = $result['suggestion'];
                $scene = $result['scene'];

                if ($suggestion === 'block') {
                    $status = ScanResultStatus::Violation;
                    $reasons[] = $scene;
                } else if ($suggestion === 'review') {
                    $reasons[] = $scene;
                    if ($status !== ScanResultStatus::Violation) {
                        $status = ScanResultStatus::Suspected;
                    }
                }
            }

            return $this->result($status, $reasons);
        } catch (Throwable $e) {
            Log::warning('阿里云图片内容安全同步检测失败', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new ServiceException("阿里云图片内容安全同步检测失败，{$e->getMessage()}", 500);
        }
    }

    /**
     * 返回审核结果
     *
     * @param ScanResultStatus $status 状态值
     * @param array $reasons 违规原因
     * @return array
     */
    private function result(ScanResultStatus $status, array $reasons = []): array
    {
        return [$status, $reasons];
    }

    /**
     * 阿里云图片内容安全增强版同步检测
     *
     * 图片支持以下格式：PNG、JPG、JPEG、BMP、WEBP、TIFF、SVG、HEIC（该格式最长边需小于8192 px）、GIF（取第一帧）、ICO（取最后一图）。
     * 图片大小限制在20 MB以内，高或者宽不能超过16,384 px，且总像素不能超过1.67亿 px。像素建议大于200*200（px），像素过低会影响内容安全检测算法的效果。
     * 图片下载时间限制为3秒内，如果下载时间超过3秒，返回下载超时。
     *
     * @link https://help.aliyun.com/document_detail/467829.html
     * @param Photo $photo
     * @param ArrayObject $options
     * @return array
     * @throws ServiceException
     */
    private function aliyunV2SyncCheck(Photo $photo, ArrayObject $options): array
    {
        $supportedFormats = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp', 'svg', 'heic', 'ico'];
        if (!in_array($photo->extension, $supportedFormats) || $photo->size > 20971520) {
            return $this->result(ScanResultStatus::Normal);
        }

        try {
            $client = new \AlibabaCloud\SDK\Green\V20220302\Green(new Config(array_filter([
                'accessKeyId' => $options['access_key_id'],
                'accessKeySecret' => $options['access_key_secret'],
                'httpProxy' => $options['http_proxy'],
                'httpsProxy' => $options['https_proxy'],
                'endpoint' => $options['endpoint'],
            ])));
            $runtime = new RuntimeOptions([]);
            $request = new ImageModerationRequest();
            $serviceParameters = ['imageUrl' => $photo->public_url, 'dataId' => (string)$photo->id];
            $request->service = $options['service'];
            $request->serviceParameters = json_encode($serviceParameters);
            $response = $client->imageModerationWithOptions($request, $runtime);
            if (200 != $response->body->code) {
                throw new ServiceException($response->body->msg);
            }

            $status = ScanResultStatus::Normal;
            $reasons = [];

            /** @var Result $result */
            foreach ($response->body->data->result as $result) {
                // nonLabel 表示没有风险，nonLabel_lib 表示命中了配置的免审图库
                if (!in_array($result->label, ['nonLabel', 'nonLabel_lib'])) {
                    if ($result->confidence >= 80) {
                        $status = ScanResultStatus::Violation;
                        $reasons[] = $result->label;
                    } else if ($result->confidence >= 50) {
                        $reasons[] = $result->label;
                        if ($status !== ScanResultStatus::Violation) {
                            $status = ScanResultStatus::Suspected;
                        }
                    }
                }
            }

            return $this->result($status, $reasons);
        } catch (Throwable $e) {
            Log::warning('阿里云图片内容安全增强版同步检测失败', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new ServiceException("阿里云图片内容安全增强版同步检测失败，{$e->getMessage()}", 500);
        }
    }

    /**
     * 腾讯云图片内容安全同步检测
     *
     * 图片文件大小支持：文件 < 30M
     * 图片默认尺寸支持：长和宽 需>50分辨率且<10000分辨率，并且图片长宽比<90:1；
     * 图片文件分辨率支持：建议分辨率大于256x256，否则可能会影响识别效果；
     * 图片文件支持格式：PNG、JPG、JPEG、BMP、GIF、WEBP格式；
     * 若传入图片文件的访问链接，则需要注意图片下载时间限制为3秒，为保障被检测图片的稳定性和可靠性，建议您使用腾讯云COS存储或者CDN缓存等；
     *
     * @link https://cloud.tencent.com.cn/document/product/1125/53273
     * @param Photo $photo
     * @param ArrayObject $options
     * @return array
     * @throws ServiceException
     */
    private function tencentSyncCheck(Photo $photo, ArrayObject $options): array
    {
        $supportedFormats = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'];
        if (!in_array($photo->extension, $supportedFormats) || $photo->size > 31457280) {
            return $this->result(ScanResultStatus::Normal);
        }

        try {
            $cred = new Credential($options['secret_id'], $options['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($options['endpoint']);
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new ImsClient($cred, $options['region_id'], $clientProfile);
            $req = new \TencentCloud\Ims\V20201229\Models\ImageModerationRequest();
            $req->fromJsonString(json_encode(array_filter([
                'DataId' => (string)$photo->id,
                'BizType' => $options['biz_type'],
                'FileUrl' => $photo->public_url,
            ])));
            $resp = $client->ImageModeration($req);
            $reasons = [];
            /** @var LabelResult $result */
            foreach ($resp->getLabelResults() as $result) {
                if ('Pass' !== $result->getSuggestion()) {
                    $reasons[] = $result->getLabel();
                }
            }

            if ('Block' === $resp->getSuggestion()) {
                return $this->result(ScanResultStatus::Violation, $reasons);
            }

            if ('Review' === $resp->getSuggestion()) {
                return $this->result(ScanResultStatus::Suspected, $reasons);
            }

            return $this->result(ScanResultStatus::Normal);
        } catch (Throwable $e) {
            Log::warning('腾讯云图片内容安全同步检测失败', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new ServiceException("腾讯云图片内容安全同步检测失败，{$e->getMessage()}", 500);
        }
    }

    /**
     * NswfJS
     *
     * 支持 PNG、JPG、JPEG、GIF、WEBP 格式
     *
     * @link https://github.com/infinitered/nsfwjs
     * @param Photo $photo
     * @param ArrayObject $options
     * @return array
     * @throws ServiceException
     */
    private function nsfwJSSyncCheck(Photo $photo, ArrayObject $options): array
    {
        $supportedFormats = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        if (!in_array($photo->extension, $supportedFormats)) {
            return $this->result(ScanResultStatus::Normal);
        }

        try {
            // 获取图片储存
            $filesystem = new Filesystem(StorageService::getAdapter($photo->storage->provider, $photo->storage->options));

            $contents = $filesystem->read($photo->pathname);
            $response = Http::withoutVerifying()->attach($options['attr_name'] ?? 'image', $contents, $photo->filename)
                ->post($options['endpoint']);

            $status = ScanResultStatus::Normal;
            $reasons = [];

            foreach ($response->json() as $scene => $confidence) {
                if (in_array($scene, $options['scenes'])) {
                    if ($confidence >= ($options['threshold'] / 100)) {
                        $status = ScanResultStatus::Violation;
                        $reasons[] = $scene;
                    } else if ($confidence >= ($options['threshold'] / 100 / 1.5)) {
                        $reasons[] = $scene;
                        if ($status !== ScanResultStatus::Violation) {
                            $status = ScanResultStatus::Suspected;
                        }
                    }
                }
            }

            return $this->result($status, $reasons);
        } catch (Throwable $e) {
            Log::warning('NswfJS 同步检测失败', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new ServiceException("NswfJS 同步检测失败，{$e->getMessage()}", 500);
        }
    }

    /**
     * ModerateContent
     *
     * 支持 GIF、JPEG、JPG、PNG、BMP、WEBP 格式
     *
     * @link https://moderatecontent.com
     * @param Photo $photo
     * @param ArrayObject $options
     * @return array
     * @throws ServiceException
     */
    private function moderateContentSyncCheck(Photo $photo, ArrayObject $options): array
    {
        $supportedFormats = ['png', 'jpg', 'jpeg', 'bmp', 'gif', 'webp'];
        if (!in_array($photo->extension, $supportedFormats) || $photo->size > 10485760) {
            return $this->result(ScanResultStatus::Normal);
        }

        try {
            $response = Http::withoutVerifying()->get("https://api.moderatecontent.com/moderate/?key={$options['api_key']}&url={$photo->public_url}");

            if (0 != $response->json('error_code')) {
                throw new ServiceException($response->json('error'));
            }

            $status = ScanResultStatus::Normal;
            $reasons = [];
            if ($response->json('rating_label') === 'teen') {
                $status = ScanResultStatus::Suspected;
                $reasons[] = 'teen';
            }

            if ($response->json('rating_label') === 'adult') {
                $status = ScanResultStatus::Violation;
                $reasons[] = 'adult';
            }

            return $this->result($status, $reasons);
        } catch (Throwable $e) {
            Log::warning('ModerateContent 同步检测失败', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new ServiceException("ModerateContent 同步检测失败，{$e->getMessage()}", 500);
        }
    }
}
