<?php

use App\Http\Controllers\V2\UserTokenController;
use App\Http\Middleware\CheckExploreEnabled;
use App\Http\Middleware\CheckTokenPermission;
use App\Support\R;
use App\Http\Controllers\V2\AuthController;
use App\Http\Controllers\V2\ExploreAlbumController;
use App\Http\Controllers\V2\ExplorePhotoController;
use App\Http\Controllers\V2\ExploreUserController;
use App\Http\Controllers\V2\NoticeController;
use App\Http\Controllers\V2\NotifyController;
use App\Http\Controllers\V2\PageController;
use App\Http\Controllers\V2\PlanController;
use App\Http\Controllers\V2\ShareController;
use App\Http\Controllers\V2\UserAlbumController;
use App\Http\Controllers\V2\HomeController;
use App\Http\Controllers\V2\OAuthController;
use App\Http\Controllers\V2\UserGroupController;
use App\Http\Controllers\V2\UserOrderController;
use App\Http\Controllers\V2\UserPhotoController;
use App\Http\Controllers\V2\UserShareController;
use App\Http\Controllers\V2\UserController;
use App\Http\Controllers\V2\UserCapacityController;
use App\Http\Controllers\V2\UserTicketController;
use App\Http\Middleware\Initialize;
use App\Http\Middleware\UploadFrequencyLimit;
use App\Http\Middleware\UploadVerify;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\{
    StrategyController as V1StrategyController,
    ImageController as V1ImageController,
    AlbumController as V1AlbumController,
    UserController as V1UserController,
};
use App\Http\Controllers\V2\StatController;

// 兼容旧版本
Route::group([
    'prefix' => 'v1',
    'middleware' => Initialize::class,
], function () {

    Route::get('strategies', [V1StrategyController::class, 'index']);
    Route::post('upload', [V1ImageController::class, 'upload']);

    Route::group([
        'middleware' => ['auth:sanctum', CheckTokenPermission::class],
    ], function () {
        Route::post('images/tokens', [V1ImageController::class, 'tokens']);
        Route::get('images', [V1ImageController::class, 'index']);
        Route::delete('images/{key}', [V1ImageController::class, 'destroy']);
        Route::get('albums', [V1AlbumController::class, 'index']);
        Route::delete('albums/{id}', [V1AlbumController::class, 'destroy']);
        Route::get('profile', [V1UserController::class, 'index']);
    });
});

// V2.x
Route::prefix('v2')->group(function () {
    Route::get('check-installation', [HomeController::class, 'checkInstallation']);
    Route::get('license', [HomeController::class, 'license']);
    Route::get('changelog', [HomeController::class, 'changelog']);
    Route::post('install', [HomeController::class, 'install']);
    Route::get('qrcode', [HomeController::class, 'qrcode']);
    Route::get('captcha', [HomeController::class, 'captcha']);
    Route::get('stat/{key?}', [StatController::class, 'index']);

    Route::group([
        'middleware' => Initialize::class,
    ], function () {
        Route::post('upload', [HomeController::class, 'upload'])->middleware([
            UploadVerify::class, UploadFrequencyLimit::class,
        ]);

        Route::get('configs', [HomeController::class, 'configs']);
        Route::get('group', [HomeController::class, 'group']);

        // 验证码
        Route::middleware('throttle:5,1')->group(function () {
            Route::post('sms/send', [HomeController::class, 'smsCodeSend']);
            Route::post('mail/send', [HomeController::class, 'mailCodeSend']);
        });

        // 找回密码
        Route::post('sms/reset_password', [AuthController::class, 'smsResetPassword']);
        Route::post('mail/reset_password', [AuthController::class, 'mailResetPassword']);

        // 支付回调
        Route::any('payment/callback/{id}/{out_trade_no}', NotifyController::class)->name('payment.notify');

        // oauth
        Route::get('oauth/{id}/redirect', [OAuthController::class, 'redirect']);
        Route::post('oauth/{id}/login', [OAuthController::class, 'login']);
        Route::middleware(['auth:sanctum', CheckTokenPermission::class])->group(function () {
            Route::get('oauth/binds', [OAuthController::class, 'binds']);
            Route::post('oauth/{id}/bind', [OAuthController::class, 'bind']);
            Route::delete('oauth/{id}/unbind', [OAuthController::class, 'unbind']);
        });

        // 分享
        Route::get('shares/{slug}', [ShareController::class, 'show']);
        Route::get('shares/{slug}/photos', [ShareController::class, 'photos']);
        Route::post('shares/{slug}/report', [ShareController::class, 'report']);
        Route::middleware(['auth:sanctum', CheckTokenPermission::class])->group(function () {
            Route::post('shares/{slug}/like', [ShareController::class, 'like']);
            Route::delete('shares/{slug}/unlike', [ShareController::class, 'unlike']);
        });

        // 站内公告
        Route::apiResource('notices', NoticeController::class)->only('index', 'show');

        // 页面
        Route::apiResource('pages', PageController::class)->only('index', 'show');

        // 套餐
        Route::apiResource('plans', PlanController::class)->only('index', 'show');

        // 令牌权限
        Route::get('token_permissions', [HomeController::class, 'permissions']);

        // 反馈
        Route::post('feedback', [HomeController::class, 'feedback']);

        // 广场
        Route::prefix('explore')->middleware(CheckExploreEnabled::class)->group(function () {
            // 图片广场
            Route::prefix('photos')->group(function () {
                Route::get('', [ExplorePhotoController::class, 'photos']);
                Route::get('{id}', [ExplorePhotoController::class, 'photo']);
                Route::post('{id}/report', [ExplorePhotoController::class, 'report']);
                Route::middleware(['auth:sanctum', CheckTokenPermission::class])->group(function () {
                    Route::post('{id}/like', [ExplorePhotoController::class, 'like']);
                    Route::delete('{id}/unlike', [ExplorePhotoController::class, 'unlike']);
                });
            });

            // 用户广场
            Route::prefix('users')->group(function () {
                Route::get('{username}', [ExploreUserController::class, 'profile']);
                Route::get('{username}/photos', [ExploreUserController::class, 'photos']);
                Route::get('{username}/albums', [ExploreUserController::class, 'albums']);
                Route::post('{username}/report', [ExploreUserController::class, 'report']);
            });

            // 相册广场
            Route::prefix('albums')->group(function () {
                Route::get('', [ExploreAlbumController::class, 'albums']);
                Route::get('{id}/photos', [ExploreAlbumController::class, 'photos']);
                Route::get('{id}', [ExploreAlbumController::class, 'album']);
                Route::post('{id}/report', [ExploreAlbumController::class, 'report']);
                Route::middleware(['auth:sanctum', CheckTokenPermission::class])->group(function () {
                    Route::post('{id}/like', [ExploreAlbumController::class, 'like']);
                    Route::delete('{id}/unlike', [ExploreAlbumController::class, 'unlike']);
                });
            });
        });

        // 登录用户
        Route::prefix('user')->middleware(['auth:sanctum', CheckTokenPermission::class])->group(function () {
            Route::get('profile', [UserController::class, 'profile']);
            Route::post('profile', [UserController::class, 'updateProfile']);
            Route::post('setting', [UserController::class, 'updateSetting']);
            Route::post('bind_phone', [UserController::class, 'bindPhone']);
            Route::post('bind_email', [UserController::class, 'bindEmail']);

            // 用户的角色组
            Route::apiResource('groups', UserGroupController::class)->only('index', 'destroy');

            // 用户的容量
            Route::apiResource('capacities', UserCapacityController::class)->only('index', 'destroy');

            // tokens
            Route::get('tokens', [UserTokenController::class, 'index']);
            Route::post('tokens', [UserTokenController::class, 'store']);
            Route::delete('tokens/{id}', [UserTokenController::class, 'destroy']);
            Route::get('tokens/permissions', [UserTokenController::class, 'permissions']);

            // 相册
            Route::apiResource('albums', UserAlbumController::class);
            Route::post('albums/{id}/photos', [UserAlbumController::class, 'addPhotos']);
            Route::delete('albums/{id}/photos', [UserAlbumController::class, 'removePhotos']);
            Route::post('albums/{id}/tags', [UserAlbumController::class, 'attachTags']);
            Route::delete('albums/{id}/tags', [UserAlbumController::class, 'removeTags']);

            // 照片
            Route::apiResource('photos', UserPhotoController::class)->except('store', 'destroy', 'update');
            Route::put('photos/update', [UserPhotoController::class, 'update']);
            Route::delete('photos', [UserPhotoController::class, 'destroy']);
            Route::post('photos/{id}/tags', [UserPhotoController::class, 'attachTags']);
            Route::delete('photos/{id}/tags', [UserPhotoController::class, 'removeTags']);

            // 分享
            Route::apiResource('shares', UserShareController::class)->except('destroy');
            Route::delete('shares', [UserShareController::class, 'destroy']);

            // 工单
            Route::apiResource('tickets', UserTicketController::class)->except('update');
            Route::get('tickets/{issue_no}/replies', [UserTicketController::class, 'replies']);
            Route::post('tickets/{issue_no}/reply', [UserTicketController::class, 'reply']);
            Route::put('tickets/{issue_no}/close', [UserTicketController::class, 'close']);

            // 订单
            Route::apiResource('orders', UserOrderController::class)->except('update');
            Route::post('orders/preview', [UserOrderController::class, 'preview']);
            Route::put('orders/{trade_no}/cancel', [UserOrderController::class, 'cancel']);
            Route::post('orders/{trade_no}/pay', [UserOrderController::class, 'pay']);
        });
    });

    Route::fallback(fn() => R::error('Not Found')->setStatusCode(404));
});
