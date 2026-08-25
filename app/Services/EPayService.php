<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EPayService
{
    protected static ?EPayService $instance = null;

    protected string $apiUrl;
    protected string $pid;
    protected string $platformPublicKey;
    protected string $merchantPrivateKey;

    protected string $signType = 'RSA';

    public static function make(array $config = []): EPayService
    {
        if (is_null(self::$instance)) {
            return new EPayService($config);
        }

        return self::$instance;
    }

    /**
     * EPay 服务类构造函数
     *
     * @param array $config 配置参数
     */
    public function __construct(array $config)
    {
        $this->apiUrl = rtrim(data_get($config, 'api_url', ''), '/');
        $this->pid = data_get($config, 'pid');
        $this->platformPublicKey = data_get($config, 'platform_public_key');
        $this->merchantPrivateKey = data_get($config, 'merchant_private_key');
    }

    /**
     * 发起支付（获取链接）
     *
     * @param array $params 支付参数
     *
     * @return string
     */
    public function getPayLink(array $params): string
    {
        $requestUrl = "{$this->apiUrl}/api/pay/submit";
        $requestParams = $this->buildRequestParams($params);
        $query = http_build_query($requestParams);
        return "{$requestUrl}?{$query}";
    }

    /**
     * 发起API支付
     *
     * @param array $params 支付参数
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public function apiPay(array $params): array
    {
        return $this->execute('api/pay/create', $params);
    }

    /**
     * 执行API请求
     *
     * @param string $path   API路径
     * @param array  $params 请求参数
     *
     * @return array
     *
     * @throws RuntimeException
     */
    protected function execute(string $path, array $params): array
    {
        $path = ltrim($path, '/');
        $requestUrl = "{$this->apiUrl}/{$path}";
        $requestParams = $this->buildRequestParams($params);

        try {
            $response = Http::asForm()->post($requestUrl, $requestParams);
        } catch (Exception $e) {
            Log::error('EPay API 请求失败', [
                'url' => $requestUrl,
                'params' => $requestParams,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('请求外部支付服务失败');
        }

        if ($response->failed()) {
            Log::error('EPay API 响应失败', [
                'url' => $requestUrl,
                'params' => $requestParams,
                'response' => $response->body(),
            ]);
            throw new RuntimeException('请求失败');
        }

        $data = $response->json();

        if ($data && isset($data['code']) && $data['code'] == 0) {
            if (!$this->verify($data)) {
                Log::warning('EPay 返回数据验签失败', ['data' => $data]);
                throw new RuntimeException('返回数据验签失败');
            }
            return $data;
        } else {
            $message = $data['msg'] ?? '请求失败';
            Log::error('EPay API 返回错误', ['data' => $data]);
            throw new RuntimeException($message);
        }
    }

    /**
     * 回调验证
     *
     * @param array $data 回调数据
     *
     * @return bool
     */
    public function verify(array $data): bool
    {
        if (empty($data) || empty($data['sign'])) {
            return false;
        }

        if (empty($data['timestamp']) || abs(time() - (int)$data['timestamp']) > 300) {
            return false;
        }

        $sign = $data['sign'];
        $dataToVerify = $this->getSignContent($data);

        return $this->rsaPublicVerify($dataToVerify, $sign);
    }

    /**
     * 查询订单支付状态
     *
     * @param string $tradeNo 交易号
     *
     * @return bool
     *
     * @throws Exception
     */
    public function orderStatus(string $tradeNo): bool
    {
        $result = $this->queryOrder($tradeNo);
        return isset($result['status']) && $result['status'] == 1;
    }

    /**
     * 查询订单
     *
     * @param string $tradeNo 交易号
     *
     * @return array
     *
     * @throws Exception
     */
    public function queryOrder(string $tradeNo): array
    {
        $params = ['trade_no' => $tradeNo];
        return $this->execute('api/pay/query', $params);
    }

    /**
     * 订单退款
     *
     * @param string $outRefundNo 外部退款号
     * @param string $tradeNo     交易号
     * @param float  $amount      退款金额
     *
     * @return array
     *
     * @throws Exception
     */
    public function refund(string $outRefundNo, string $tradeNo, float $amount): array
    {
        $params = [
            'trade_no' => $tradeNo,
            'money' => number_format($amount, 2, '.', ''),
            'out_refund_no' => $outRefundNo,
        ];
        return $this->execute('api/pay/refund', $params);
    }

    /**
     * 构建请求参数并生成签名
     *
     * @param array $params 原始参数
     *
     * @return array
     */
    protected function buildRequestParams(array $params): array
    {
        $params['pid'] = $this->pid;
        $params['timestamp'] = time();

        $params['sign'] = $this->getSign($params);
        $params['sign_type'] = $this->signType;

        return $params;
    }

    /**
     * 生成签名
     *
     * @param array $params 参数数组
     *
     * @return string 签名字符串
     */
    protected function getSign(array $params): string
    {
        $dataToSign = $this->getSignContent($params);
        return $this->rsaPrivateSign($dataToSign);
    }

    /**
     * 获取待签名字符串
     *
     * @param array $params 参数数组
     *
     * @return string
     */
    protected function getSignContent(array $params): string
    {
        ksort($params);
        $pairs = [];

        foreach ($params as $key => $value) {
            if (is_array($value) || $this->isEmpty($value) || in_array($key, ['sign', 'sign_type'], true)) {
                continue;
            }
            $pairs[] = "{$key}={$value}";
        }

        return implode('&', $pairs);
    }

    /**
     * 判断值是否为空
     *
     * @param mixed $value 值
     *
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        return is_null($value) || trim((string)$value) === '';
    }

    /**
     * 使用商户私钥签名
     *
     * @param string $data 待签名数据
     *
     * @return string 签名结果
     *
     * @throws Exception
     */
    protected function rsaPrivateSign(string $data): string
    {
        $privateKeyFormatted = $this->formatPrivateKey($this->merchantPrivateKey);
        $privateKey = openssl_pkey_get_private($privateKeyFormatted);

        if (!$privateKey) {
            throw new Exception('签名失败，商户私钥错误');
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('签名失败');
        }

        return base64_encode($signature);
    }

    /**
     * 使用平台公钥验证签名
     *
     * @param string $data 待验证数据
     * @param string $sign 签名
     *
     * @return bool 验证结果
     *
     * @throws Exception
     */
    protected function rsaPublicVerify(string $data, string $sign): bool
    {
        $publicKeyFormatted = $this->formatPublicKey($this->platformPublicKey);
        $publicKey = openssl_pkey_get_public($publicKeyFormatted);

        if (!$publicKey) {
            throw new Exception('验签失败，平台公钥错误');
        }

        $result = openssl_verify($data, base64_decode($sign, true), $publicKey, OPENSSL_ALGO_SHA256);

        return $result === 1;
    }

    /**
     * 格式化私钥为 PEM 格式
     *
     * @param string $privateKey 原始私钥
     *
     * @return string 格式化后的私钥
     */
    protected function formatPrivateKey(string $privateKey): string
    {
        return "-----BEGIN PRIVATE KEY-----\n" .
            wordwrap($privateKey, 64, "\n", true) .
            "\n-----END PRIVATE KEY-----";
    }

    /**
     * 格式化公钥为 PEM 格式
     *
     * @param string $publicKey 原始公钥
     *
     * @return string 格式化后的公钥
     */
    protected function formatPublicKey(string $publicKey): string
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    }
}