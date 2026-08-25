<?php

namespace AlphaSnow\LaravelFilesystem\Aliyun\Macros;

use AlphaSnow\Flysystem\Aliyun\AliyunException;
use AlphaSnow\LaravelFilesystem\Aliyun\OssClientAdapter;
use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use OSS\Core\OssException;

class ProcessObject implements AliyunMacro
{
    /**
     * @return string
     */
    public function name(): string
    {
        return "processObject";
    }

    /**
     * @return Closure
     */
    public function macro(): Closure
    {
        $base64UrlEncode = function ($data): string {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        };

        return function (string $path, string $process, string $savePath = "", array $options = []) use ($base64UrlEncode) {
            /**
             * @var FilesystemAdapter $this
             */
            $adapter = new OssClientAdapter($this);

            $savePath == "" && $savePath = $path;
            $saveObject = $base64UrlEncode($adapter->path($savePath));
            $saveBucket = $base64UrlEncode($adapter->bucket());
            $process .= sprintf("|sys/saveas,o_%s,b_%s", $saveObject, $saveBucket);

            try {
                $result = $adapter->client()->processObject(
                    $adapter->bucket(),
                    $adapter->path($path),
                    $process,
                    $adapter->options($options)
                );
                return json_decode($result, true);
            } catch (OssException $exception) {
                throw new AliyunException($exception->getErrorMessage(), 0, $exception);
            }
        };
    }
}
