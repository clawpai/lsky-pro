<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[AsCommand(name: 'app:reset-user-password', description: '重置用户密码')]
class ResetPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-user-password
        {--email= : 用户邮箱}
        {--password= : 新密码}
        {--password_confirmation= : 确认密码}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置用户密码';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // 获取用户邮箱
            $email = $this->option('email') ?: text(
                label: '请输入用户邮箱',
                placeholder: 'user@example.com',
                required: true,
                validate: fn (string $value) => match (true) {
                    ! filter_var($value, FILTER_VALIDATE_EMAIL) => '请输入有效的邮箱地址',
                    default => null
                }
            );

            // 查找用户
            $user = User::where('email', $email)->first();

            if (! $user) {
                error('未找到邮箱为 ' . $email . ' 的用户');
                return CommandAlias::FAILURE;
            }

            // 获取新密码
            $newPassword = $this->option('password') ?: password(
                label: '请输入新密码',
                placeholder: '输入新密码',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 6 => '密码长度不能少于6位',
                    default => null
                }
            );

            // 获取确认密码
            $confirmPassword = $this->option('password_confirmation') ?: password(
                label: '请确认新密码',
                placeholder: '再次输入新密码',
                required: true
            );

            // 验证密码
            $validator = Validator::make([
                'password' => $newPassword,
                'password_confirmation' => $confirmPassword,
            ], [
                'password' => 'required|min:6|confirmed',
            ], [
                'password.required' => '密码不能为空',
                'password.min' => '密码长度不能少于6位',
                'password.confirmed' => '两次输入的密码不一致',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $errorMessage) {
                    error($errorMessage);
                }
                return CommandAlias::FAILURE;
            }

            // 更新用户密码
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            info('用户密码重置成功！');
            info('用户信息：');
            $this->table(['字段', '值'], [
                ['用户ID', $user->id],
                ['用户名', $user->username ?? '未设置'],
                ['姓名', $user->name],
                ['邮箱', $user->email],
                ['更新时间', $user->updated_at->format('Y-m-d H:i:s')],
            ]);

            return CommandAlias::SUCCESS;

        } catch (\Exception $e) {
            error('密码重置失败：' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
} 