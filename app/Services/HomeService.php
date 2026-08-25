<?php

declare(strict_types=1);

namespace App\Services;

use App\DriverType;
use App\Exceptions\ServiceException;
use App\Facades\AppService;
use App\Facades\PaymentService;
use App\Http\Requests\EmailCodeSendRequest;
use App\Http\Requests\SmsCodeSendRequest;
use App\Jobs\SendCodeMailJob;
use App\Jobs\SendCodeSmsJob;
use App\Models\Driver;
use App\Models\Group;
use App\Models\Photo;
use App\PaymentProvider;
use App\Settings\AppSettings;
use App\Settings\SiteSettings;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Storage;

class HomeService
{
    /**
     * 获取系统配置
     *
     * @return array
     */
    public function getConfigs(): array
    {
        $appSettings = Arr::mapWithKeys(app(AppSettings::class)->toArray(), function ($value, string $key) {
            return ["app.{$key}" => $value];
        });

        $siteSettings = Arr::mapWithKeys(app(SiteSettings::class)->toArray(), function ($value, string $key) {
            return ["site.{$key}" => $value];
        });

        $configs = Arr::only(array_merge($appSettings, $siteSettings), [
            'app.name',
            'app.url',
            'app.debug',
            'app.icp_no',
            'app.currency',
            'app.enable_registration',
            'app.guest_upload',
            'app.user_email_verify',
            'app.user_phone_verify',
            'app.enable_site',
            'app.enable_explore',
            'site.title',
            'site.subtitle',
            'site.homepage_title',
            'site.homepage_description',
            'site.notice',
            'site.custom_css',
            'site.custom_js',
            'site.homepage_background_image_url',
            'site.homepage_background_images',
            'site.auth_page_background_image_url',
            'site.auth_page_background_images',
        ]);

        foreach ($configs as $key => &$value) {
            if (in_array($key, ['site.homepage_background_images', 'site.auth_page_background_images']) && $value) {
                $value = Arr::map($value, fn($val) => Storage::url($val));
            }
        }

        $socialites = Driver::where('type', DriverType::Socialite)->get();

        // 补充更多信息
        $configs = array_merge($configs, [
            'app.timestamp' => now()->timestamp,                                    // 系统时间戳
            'app.is_logged_in' => Auth::guard('sanctum')->check(),                  // 是否登录
            'app.photo_count' => Photo::count(),                                    // 系统图片数量
            'app.photo_size' => (float)(Photo::sum('size')),                        // 图片占用字节(kb)
            'app.countries' => AppService::getAllCountries(),                       // 支持的国家代码
            'app.socialites' => $socialites->map(function (Driver $socialite) {     // 社会化登录驱动
                $socialite->provider = $socialite->options['provider'];
                return $socialite->only([
                    'id', 'name', 'intro', 'provider',
                ]);
            }),
        ]);

        return Arr::undot($configs);
    }

    /**
     * 获取当前使用组信息
     *
     * @return array
     */
    public function getDefaultGroup(): array
    {
        /** @var Group $group */
        $group = Context::get('group');

        $storages = $group->storages()->get();
        $payments = $group->paymentDrivers()->get();

        return [
            'group' => $group->only(['id', 'name', 'intro', 'is_default', 'is_guest', 'options']),
            'storages' => $storages->map(function (\App\Models\Storage $storage) {
                return $storage->only(['id', 'name', 'intro', 'provider']);
            }),
            'payments' => $payments->map(function (Driver $payment) {
                $platform = PaymentProvider::from($payment->options['provider']);
                return array_merge($payment->only('id', 'name', 'intro'), [
                    'platform' => $platform,
                    'channels' => $payment->options['channels'] ?? PaymentService::getPlatformChannels($platform),
                    'methods' => PaymentService::getPlatformMethods($platform),
                ]);
            })
        ];
    }

    /**
     * 发送短信验证码
     * @throws ServiceException
     */
    public function smsCodeSend(SmsCodeSendRequest $request): void
    {
        $ip = AppService::getRequestIp($request);
        $event = $request->validated('event');
        $phone = $request->validated('phone');

        $cacheKey = "sms_code_count:{$ip}";
        $sendCount = Cache::get($cacheKey, 0);

        if ($sendCount >= 3) {
            throw new ServiceException('发送过于频繁，请稍后再试');
        }

        /** @var Group $group */
        $group = Context::get('group');

        dispatch(new SendCodeSmsJob(
            groupId: $group->id,
            event: $event,
            phone: $phone,
            countryCode: $request->validated('country_code', 'CN')
        ));

        Cache::put($cacheKey, $sendCount + 1, 120);
    }

    /**
     * 发送邮箱验证码
     * @throws ServiceException
     */
    public function mailCodeSend(EmailCodeSendRequest $request): void
    {
        $event = $request->validated('event');
        $email = $request->validated('email');

        $ip = AppService::getRequestIp($request);
        $cacheKey = "mail_code_count:{$ip}";
        $sendCount = Cache::get($cacheKey, 0);

        if ($sendCount >= 3) {
            throw new ServiceException('发送过于频繁，请稍后再试');
        }

        /** @var Group $group */
        $group = Context::get('group');

        dispatch(new SendCodeMailJob(
            groupId: $group->id,
            event: $event,
            email: $email,
        ));

        Cache::put($cacheKey, $sendCount + 1, 120);
    }
}
