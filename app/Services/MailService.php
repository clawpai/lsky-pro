<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Facades\VerifyCodeService;
use App\MailProvider;
use App\Models\Ticket;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

class MailService
{
    protected array $providers = [];
    protected array $fromMap = [];
    protected array $createdTransports = [];

    /**
     * 初始化邮件服务
     *
     * @param array $providers 提供商配置
     * @return self
     */
    public function instance(array $providers): self
    {
        $this->providers = $providers;
        
        // 预处理发件人信息
        foreach ($providers as $index => $config) {
            $provider = $config['provider'];
            $id = md5($index . $provider . time());
            
            // 保存每个provider的发件人信息
            $fromAddress = $config['from_address'] ?? null;
            $fromName = $config['from_name'] ?? null;
            
            if ($fromAddress) {
                $this->fromMap[$id] = [
                    'id' => $id,
                    'provider' => $provider,
                    'config' => $config,
                    'address' => $fromAddress,
                    'name' => $fromName ?? $fromAddress,
                ];
            } else {
                // 也存储没有发件人的配置
                $this->fromMap[$id] = [
                    'id' => $id,
                    'provider' => $provider,
                    'config' => $config,
                ];
            }
        }

        return $this;
    }

    /**
     * 根据提供商类型创建对应的Transport
     *
     * @param string $provider 提供商类型
     * @param array $config 提供商配置
     * @return TransportInterface|null
     */
    protected function createTransport(string $provider, array $config): ?TransportInterface
    {
        try {
            switch (MailProvider::from($provider)) {
                case MailProvider::Smtp:
                    $host = $config['host'];
                    $username = $config['username'] ?? null;
                    $password = $config['password'] ?? null;
                    $port = $config['port'] ?? 25;
                    $encryption = $config['encryption'] ?? null;
                    
                    $dsnString = "smtp://" . ($username ? urlencode($username) . ":" . urlencode($password) . "@" : "");
                    $dsnString .= $host . ":" . $port;
                    
                    if ($encryption) {
                        $dsnString .= "?encryption=" . $encryption;
                    }
                    
                    return Transport::fromDsn($dsnString);

                case MailProvider::Mailgun:
                    $service = data_get($config, 'service', []);
                    $secret = $service['secret'] ?? null;
                    $domain = $service['domain'] ?? null;
                    $scheme = $service['scheme'] ?? 'https';
                    
                    // Mailgun DSN格式: mailgun+https://KEY:DOMAIN@default
                    $dsnString = "mailgun+{$scheme}://{$secret}:{$domain}@default";
                    return Transport::fromDsn($dsnString);

                case MailProvider::Postmark:
                    $service = data_get($config, 'service', []);
                    $token = $service['token'] ?? null;
                    
                    $dsnString = "postmark://{$token}@default";
                    return Transport::fromDsn($dsnString);

                case MailProvider::Ses:
                    $service = data_get($config, 'service', []);
                    $key = $service['key'] ?? null;
                    $secret = $service['secret'] ?? null;
                    $region = $service['region'] ?? 'us-east-1';
                    $token = $service['token'] ?? null;
                    
                    $dsnString = "ses://{$key}:{$secret}@default?region={$region}";
                    
                    if ($token) {
                        $dsnString .= "&token=" . urlencode($token);
                    }
                    
                    return Transport::fromDsn($dsnString);

                default:
                    return null;
            }
        } catch (Throwable $e) {
            Log::error('创建邮件Transport失败', [
                'provider' => $provider,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * 获取指定邮件服务商配置
     *
     * @param string $provider
     * @return array
     */
    public function getProviderConfig(string $provider): array
    {
        return Arr::first($this->providers, function ($value) use ($provider) {
            return $provider === $value['provider'];
        }) ?? [];
    }

    /**
     * 发送验证码邮件
     *
     * @param string $event
     * @param string $email
     * @return bool
     */
    public function sendCode(string $event, string $email): bool
    {
        if (empty($this->fromMap)) {
            Log::error('邮件服务尚未初始化');
            return false;
        }

        $code = VerifyCodeService::generateCode($this->getCodeKey($event, $email));

        try {
            // 构建邮件内容
            $symfonyEmail = (new Email())
                ->to($email)
                ->subject('验证码')
                ->html($this->renderVerifyCodeTemplate($event, $code));

            // 尝试发送邮件
            $this->send($symfonyEmail);
            
            return true;
        } catch (TransportException $e) {
            Log::warning('验证码邮件发送失败', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'debug' => property_exists($e, 'getDebug') ? $e->getDebug() : null,
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        } catch (Throwable $e) {
            Log::warning('验证码邮件发送异常', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        }
        return false;
    }

    /**
     * 渲染验证码邮件模板
     * 
     * @param string $event
     * @param string $code
     * @return string
     */
    protected function renderVerifyCodeTemplate(string $event, string $code): string
    {
        return view('emails.verify-code', [
            'event' => $event,
            'code' => $code
        ])->render();
    }

    /**
     * 获取验证码键
     *
     * @param string $event
     * @param string $email
     * @return string
     */
    public function getCodeKey(string $event, string $email): string
    {
        return "mail_code:{$event}:{$email}";
    }

    /**
     * 发送工单创建通知邮件
     *
     * @param Ticket $ticket
     * @param array $emails
     * @return bool
     */
    public function sendTicketCreateNotification(Ticket $ticket, array $emails): bool
    {
        if (empty($this->fromMap)) {
            return false;
        }

        try {
            // 构建邮件内容
            $symfonyEmail = (new Email())
                ->subject('工单创建通知')
                ->html($this->renderTicketCreateTemplate($ticket));

            // 添加收件人
            foreach ($emails as $email) {
                $symfonyEmail->addTo($email);
            }

            // 尝试发送邮件
            $this->send($symfonyEmail);
            
            return true;
        } catch (TransportException $e) {
            Log::warning('工单创建邮件发送失败', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'debug' => property_exists($e, 'getDebug') ? $e->getDebug() : null,
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        } catch (Throwable $e) {
            Log::warning('工单创建邮件发送异常', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        }
        return false;
    }

    /**
     * 渲染工单创建邮件模板
     * 
     * @param Ticket $ticket
     * @return string
     */
    protected function renderTicketCreateTemplate(Ticket $ticket): string
    {
        return view('emails.ticket-create', [
            'ticket' => $ticket
        ])->render();
    }

    /**
     * 发送工单回复通知邮件
     *
     * @param Ticket $ticket
     * @param array $emails
     * @return bool
     */
    public function sendTicketReplyNotification(Ticket $ticket, array $emails): bool
    {
        if (empty($this->fromMap)) {
            return false;
        }

        try {
            // 构建邮件内容
            $symfonyEmail = (new Email())
                ->subject('工单回复通知')
                ->html($this->renderTicketReplyTemplate($ticket));

            // 添加收件人
            foreach ($emails as $email) {
                $symfonyEmail->addTo($email);
            }

            // 尝试发送邮件
            $this->send($symfonyEmail);
            
            return true;
        } catch (TransportException $e) {
            Log::warning('工单回复通知邮件发送失败', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'debug' => property_exists($e, 'getDebug') ? $e->getDebug() : null,
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        } catch (Throwable $e) {
            Log::warning('工单回复通知邮件发送异常', [
                'args' => func_get_args(),
                'message' => $e->getMessage(),
                'trade' => $e->getTraceAsString(),
                'previous' => $e->getPrevious()?->getTraceAsString(),
            ]);
        }
        return false;
    }

    /**
     * 渲染工单回复邮件模板
     * 
     * @param Ticket $ticket
     * @return string
     */
    protected function renderTicketReplyTemplate(Ticket $ticket): string
    {
        return view('emails.ticket-reply', [
            'ticket' => $ticket
        ])->render();
    }

    /**
     * 核心发送方法，支持自定义故障转移
     * 
     * @param Email $email
     * @return void
     */
    protected function send(Email $email): void
    {
        if (empty($this->fromMap)) {
            throw new ServiceException('邮件服务尚未初始化');
        }

        // 尝试每个邮件提供商配置
        $exception = null;
        $sent = false;

        foreach ($this->fromMap as $id => $providerInfo) {
            try {
                // 按需创建Transport
                $transport = $this->getOrCreateTransport($id, $providerInfo);
                if (!$transport) {
                    continue;
                }

                // 为当前邮件设置发件人
                $currentEmail = clone $email;
                if (isset($providerInfo['address'])) {
                    $currentEmail->from(new Address($providerInfo['address'], $providerInfo['name'] ?? $providerInfo['address']));
                } else {
                    // 使用全局默认发件人
                    $currentEmail->from(new Address(config('mail.from.address'), config('mail.from.name')));
                }

                // 创建临时Mailer并发送
                $mailer = new Mailer($transport);
                $mailer->send($currentEmail);

                // 发送成功，跳出循环
                $sent = true;
                break;
            } catch (Throwable $e) {
                // 记录异常，继续尝试下一个Transport
                $exception = $e;
                Log::warning('邮件发送失败，尝试下一个邮件提供商', [
                    'provider' => $providerInfo['provider'],
                    'message' => $e->getMessage()
                ]);
            }
        }

        // 如果所有Transport都失败，抛出最后一个异常
        if (!$sent && $exception) {
            throw $exception;
        }
    }

    /**
     * 获取或创建邮件Transport
     * 
     * @param string $id
     * @param array $providerInfo
     * @return TransportInterface|null
     */
    protected function getOrCreateTransport(string $id, array $providerInfo): ?TransportInterface
    {
        // 如果已经创建过，直接返回
        if (isset($this->createdTransports[$id])) {
            return $this->createdTransports[$id];
        }

        // 按需创建
        $transport = $this->createTransport($providerInfo['provider'], $providerInfo['config']);
        
        // 缓存创建的Transport
        if ($transport) {
            $this->createdTransports[$id] = $transport;
        }
        
        return $transport;
    }

    /**
     * 验证短信验证码
     *
     * @param string $event
     * @param string $email
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $event, string $email, string $code): bool
    {
        return VerifyCodeService::verifyCode($this->getCodeKey($event, $email), $code);
    }

    /**
     * 测试邮件连接
     * @param MailProvider $provider
     * @param array $options
     * @return bool
     * @throws ServiceException
     */
    public function testConnection(MailProvider $provider, array $options): bool
    {
        try {
            // 创建传输对象
            $transport = $this->createTransport($provider->value, $options);
            
            if (!$transport) {
                throw new ServiceException(__('admin/mail_driver.actions.test_connection.error_message', ['message' => '无法创建邮件传输对象']));
            }

            // 获取发件人邮箱地址
            $fromAddress = $options['from_address'] ?? config('mail.from.address');
            $fromName = $options['from_name'] ?? config('mail.from.name');
            
            if (!$fromAddress) {
                throw new ServiceException(__('admin/mail_driver.actions.test_connection.error_message', ['message' => '未配置发件人邮箱地址']));
            }

            $testEmail = (new Email())
                ->to($fromAddress)
                ->from(new Address($fromAddress, $fromName ?? $fromAddress))
                ->subject('图床邮件连接测试')
                ->text('这是一封测试邮件，用于验证邮件配置是否正确。如果您收到这封邮件，说明邮件配置成功。');

            // 创建邮件发送器并发送测试邮件
            $mailer = new Mailer($transport);
            $mailer->send($testEmail);
            
            return true;
        } catch (Throwable $e) {
            throw new ServiceException(__('admin/mail_driver.actions.test_connection.error_message', ['message' => $e->getMessage()]));
        }
    }
}
