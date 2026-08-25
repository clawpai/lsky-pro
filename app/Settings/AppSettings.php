<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AppSettings extends Settings
{
    /** @var null|string 应用名称 */
    public ?string $name = null;

    /** @var null|string 应用 url */
    public ?string $url = null;

    /** @var null|string 授权key */
    public ?string $license_key = null;

    /** @var null|string 系统时区 */
    public ?string $timezone = null;

    /** @var null|string 语言 */
    public ?string $locale = null;

    /** @var null|string 货币 */
    public ?string $currency = null;

    /** @var null|string ICP 备案号 */
    public ?string $icp_no = null;

    /** @var null|string IP 获取方式 */
    public ?string $ip_gain_method = null;

    /** @var bool|null 是否开启注册 */
    public ?bool $enable_registration = null;

    /** @var bool|null 是否允许游客上传 */
    public ?bool $guest_upload = null;

    /** @var bool|null 是否强制用户邮箱验证 */
    public ?bool $user_email_verify = null;

    /** @var bool|null 是否强制用户手机验证 */
    public ?bool $user_phone_verify = null;

    /** @var null|string 发件人邮件地址 */
    public ?string $mail_from_address = null;

    /** @var null|string 发件人名称 */
    public ?string $mail_from_name = null;

    /** @var string|null 系统图片处理器驱动 */
    public ?string $image_driver = null;

    /** @var bool|null 是否启用站点(仅混合部署有效) */
    public ?bool $enable_site = null;

    /** @var bool|null 是否启用统计 api 接口 */
    public ?bool $enable_stat_api = null;

    /** @var string|null 统计 api 接口访问密钥 */
    public ?string $enable_stat_api_key = null;

    /** @var bool|null 是否启用图片广场 */
    public ?bool $enable_explore = null;

    public static function group(): string
    {
        return 'app';
    }

    public static function encrypted(): array
    {
        return ['license_key'];
    }
}