<?php

use Illuminate\Support\Facades\Facade;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        'AppService' => App\Facades\AppService::class,
        'StatService' => App\Facades\StatService::class,
        'UpgradeService' => App\Facades\UpgradeService::class,
        'StorageService' => App\Facades\StorageService::class,
        'PhotoService' => App\Facades\PhotoService::class,
        'PhotoHandleService' => App\Facades\PhotoHandleService::class,
        'VerifyCodeService' => App\Facades\VerifyCodeService::class,
        'SmsService' => App\Facades\SmsService::class,
        'EmailService' => App\Facades\MailService::class,
        'ReviewService' => App\Facades\ScanService::class,
        'UserService' => App\Facades\UserService::class,
        'TicketService' => App\Facades\TicketService::class,
        'ViolationService' => App\Facades\ViolationService::class,
        'ReportService' => App\Facades\ReportService::class,
        'OrderService' => App\Facades\OrderService::class,
        'GroupService' => App\Facades\GroupService::class,
        'OAuthService' => App\Facades\OAuthService::class,
        'AuthService' => App\Facades\AuthService::class,
        'HomeService' => App\Facades\HomeService::class,
        'ShareService' => App\Facades\ShareService::class,
        'NoticeService' => App\Facades\NoticeService::class,
        'PageService' => App\Facades\PageService::class,
        'UploadService' => App\Facades\UploadService::class,
        'PlanService' => App\Facades\PlanService::class,
        'PaymentService' => App\Facades\PaymentService::class,
        'FeedbackService' => App\Facades\FeedbackService::class,
        'ExplorePhotoService' => App\Facades\ExplorePhotoService::class,
        'ExploreAlbumService' => App\Facades\ExploreAlbumService::class,
        'ExploreUserService' => App\Facades\ExploreUserService::class,
        'UserAlbumService' => App\Facades\UserAlbumService::class,
        'UserPhotoService' => App\Facades\UserPhotoService::class,
        'UserShareService' => App\Facades\UserShareService::class,
        'UserTicketService' => App\Facades\UserTicketService::class,
        'UserTokenService' => App\Facades\UserTokenService::class,
        'UserOrderService' => App\Facades\UserOrderService::class,
        'UserGroupService' => App\Facades\UserGroupService::class,
        'UserSubscribeService' => App\Facades\UserCapacityService::class,
    ])->toArray(),

    /**
     * app 版本
     */
    'version' => env('APP_VERSION', '2.2.3'),

    /**
     * 服务接口地址
     */
    'service_api' => env('APP_SERVICE_API', 'https://huohuastudio.com'),
];
