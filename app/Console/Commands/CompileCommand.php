<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;

/**
 * 生成安装包 hash 索引文件
 */
#[AsCommand(name: 'app:compile', description: 'Generate hashes.json file for the application')]
class CompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate hashes.json file for the application';

    /**
     * 要忽略的文件夹和文件列表
     *
     * 如果是文件夹，需要结尾有 * 或 /*
     *
     * @var array|string[]
     */
    protected array $ignoreList = [
        '.git/*',
        '.github/*',
        'storage/*',
        '.idea/*',
        '.vscode/*',
        '*.DS_Store*',
        'npm-debug.log',
        'yarn-error.log',
        'node_modules/*',
        'web/*',
        'data/*',
        'database/*.sqlite',
        '.env',
        '.env.backup',
        '.env.production',
        'installed.lock',
        'upgrade.zip',
        'public/hot',
        '.phpunit.cache/*',
        '.phpunit.result.cache',
        'Homestead.json',
        'Homestead.yaml',
        'auth.json',
        'npm-debug.log',
        'yarn-error.log',
    ];

    /**
     * 要包括的文件夹和文件列表
     *
     * @var array|string[]
     */
    protected array $includeList = [
        'hashes.json',
        'storage/*/.gitignore',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $baseDir = base_path();
            $hashFilePath = base_path('hashes.json');

            $files = $this->getAllFiles($baseDir);
            $hashes = [];

            $progress = progress(label: 'Generating...', steps: count($files));
            $progress->start();

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $hashes[$file->getRelativePathname()] = File::hash($file->getRealPath());
                $progress->advance();
            }

            File::put($hashFilePath, json_encode($hashes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $progress->finish();

            info('Hash file generated successfully.');
        } catch (Throwable $e) {
            error($e->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }

    protected function getAllFiles($directory): array
    {
        $files = [];

        $allFiles = File::allFiles($directory, true);
        foreach ($allFiles as $file) {
            $relativePath = $file->getRelativePathname();

            // 跳过符号链接
            if ($file->isLink()) {
                continue;
            }

            if ($this->shouldInclude($relativePath) || !$this->shouldIgnore($relativePath)) {
                $files[] = $file;
            }
        }

        return $files;
    }

    protected function shouldInclude(string $path): bool
    {
        foreach ($this->includeList as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }
        return false;
    }

    protected function shouldIgnore(string $path): bool
    {
        foreach ($this->ignoreList as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
