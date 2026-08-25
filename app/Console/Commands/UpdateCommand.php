<?php

namespace App\Console\Commands;

use App\Facades\AppService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

/**
 * 下载最新版本安装包
 */
#[AsCommand(name: 'app:update')]
class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest installation package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $version = urlencode(config('app.version'));

            $versions = AppService::getLskyProVersions();

            $lastVersion = $versions[0] ?? null;
            if ($lastVersion) {
                info('Latest version: ' . $lastVersion['version']);

                if (!version_compare($lastVersion['version'], $version, '>')) {
                    info('You are using the latest version');
                    return CommandAlias::SUCCESS;
                }

                spin(
                    function () use ($lastVersion) {
                        $download = Http::withoutVerifying()->get($lastVersion['download_url']);

                        if (!$download->ok()) {
                            throw new Exception($download->reason());
                        }

                        File::put(base_path('upgrade.zip'), $download->body());
                    },
                    'Download...'
                );

                info('The download is complete. To upgrade, run php artisan app:upgrade');
            } else {
                info('No new version');
            }

        } catch (Throwable $e) {
            // 删除下载文件
            File::delete(base_path('upgrade.zip'));
            error($e->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }
}
