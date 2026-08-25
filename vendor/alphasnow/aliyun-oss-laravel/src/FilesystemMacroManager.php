<?php

namespace AlphaSnow\LaravelFilesystem\Aliyun;

use AlphaSnow\Flysystem\Aliyun\AliyunException;
use AlphaSnow\LaravelFilesystem\Aliyun\Macros\AliyunMacro;
use AlphaSnow\LaravelFilesystem\Aliyun\Macros\AppendFile;
use AlphaSnow\LaravelFilesystem\Aliyun\Macros\AppendObject;
use AlphaSnow\LaravelFilesystem\Aliyun\Macros\ProcessObject;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Contracts\Foundation\Application;

class FilesystemMacroManager
{
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var FilesystemAdapter
     */
    protected FilesystemAdapter $filesystemAdapter;

    /**
     * @var array
     */
    protected array $defaultMacros = [
        AppendFile::class,
        AppendObject::class,
        ProcessObject::class,
    ];

    /**
     * @param Application $app
     * @param FilesystemAdapter $filesystemAdapter
     */
    public function __construct(Application $app, FilesystemAdapter $filesystemAdapter)
    {
        $this->app = $app;
        $this->filesystemAdapter = $filesystemAdapter;
    }

    /**
     * @return $this
     */
    public function defaultRegister(): FilesystemMacroManager
    {
        $this->register($this->defaultMacros);
        return $this;
    }

    /**
     * @param array $macros
     * @return $this
     */
    public function register(array $macros): FilesystemMacroManager
    {
        foreach ($macros as $macro) {
            $filesystemMacro = $this->app->make($macro);
            if (!$filesystemMacro instanceof AliyunMacro) {
                throw new AliyunException("FilesystemMacroManager register want AliyunMacro, But got ".$filesystemMacro::class, 0);
            }

            $this->filesystemAdapter::macro($filesystemMacro->name(), $filesystemMacro->macro());
        }
        return $this;
    }
}
