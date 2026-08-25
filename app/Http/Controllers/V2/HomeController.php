<?php

namespace App\Http\Controllers\V2;

use App\ApiPermission;
use App\Console\Commands\InstallCommand;
use App\Facades\AppService;
use App\Facades\FeedbackService;
use App\Facades\HomeService;
use App\Facades\UploadService;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailCodeSendRequest;
use App\Http\Requests\FeedbackStoreRequest;
use App\Http\Requests\InstallRequest;
use App\Http\Requests\SmsCodeSendRequest;
use App\Http\Requests\UploadRequest;
use App\Models\User;
use App\Support\R;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Mews\Captcha\Facades\Captcha;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * 检测程序是否安装
     */
    public function checkInstallation(): Response
    {
        return R::success(data: ['installed' => AppService::isInstalled()]);
    }

    /**
     * 获取软件免责声明
     */
    public function license(): Response
    {
        return R::success(data: ['content' => AppService::getAgreement()]);
    }

    /**
     * 软件更新日志
     */
    public function changelog(): Response
    {
        return R::success(data: ['content' => file_get_contents(base_path('CHANGELOG.md'))]);
    }

    /**
     * 安装程序
     */
    public function install(InstallRequest $request): Response
    {
        $output = new BufferedOutput();

        $options = collect($request->validated())->transform(function ($item, $key) {
            return ['--' . Str::slug($key) => $item];
        })->collapse()->toArray();

        Artisan::call(InstallCommand::class, $options, $output);

        return R::success(data: [
            'content' => $output->fetch(),
        ])->setStatusCode(201);
    }

    /**
     * 获取系统配置
     */
    public function configs(): Response
    {
        return R::success(data: HomeService::getConfigs());
    }

    /**
     * 当前所在组信息，包含储存
     */
    public function group(): Response
    {
        return R::success(data: HomeService::getDefaultGroup());
    }

    /**
     * 提交反馈
     */
    public function feedback(FeedbackStoreRequest $request): Response
    {
        FeedbackService::store([...$request->validated(), ...['ip_address' => AppService::getRequestIp($request)]]);

        return R::success()->setStatusCode(201);
    }

    /**
     * 发送短信验证码
     */
    public function smsCodeSend(SmsCodeSendRequest $request): Response
    {
        HomeService::smsCodeSend($request);
        return R::success()->setStatusCode(201);
    }

    /**
     * 发送邮箱验证码
     */
    public function mailCodeSend(EmailCodeSendRequest $request): Response
    {
        HomeService::mailCodeSend($request);
        return R::success()->setStatusCode(201);
    }

    /**
     * 生成二维码
     */
    public function qrcode(Request $request): Response
    {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($request->query('content'))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($request->query('size', 300))
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->validateResult(false)
            ->build();

        return \response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
    }

    /**
     * 返回图形验证码
     */
    public function captcha(): Response
    {
        return R::success(data: Captcha::create('math', true));
    }

    /**
     * 上传图片
     */
    public function upload(UploadRequest $request): Response
    {
        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        $image = UploadService::uploadImage(
            file: $request->file('file'),
            user: $user,
            merge: Arr::only($request->validated(), [
                'storage_id',
                'album_id',
                'expired_at',
                'is_public',
            ]),
            tags: $request->validated('tags') ?: []
        );

        return R::success(data: $image->only([
            'id', 'name', 'filename', 'pathname', 'mimetype', 'extension',
            'md5', 'sha1', 'width', 'height', 'ip_address', 'public_url',
        ]));
    }

    /**
     * 获取所有可用的API权限
     */
    public function permissions(): Response
    {
        return R::success('success', [
            'permissions' => ApiPermission::getPermissionList(),
            'groups' => ApiPermission::getPermissionGroups(),
        ]);
    }
}
