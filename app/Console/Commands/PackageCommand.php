<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;
use ZipArchive;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;

/**
 * 打包安装包，需要先生成 hashes.json 文件，@see CompileCommand
 */
#[AsCommand(name: 'app:package', description: 'Package the application into an upgrade.zip file')]
class PackageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:package';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package the application into an upgrade.zip file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $baseDir = base_path();
            $hashFilePath = base_path('hashes.json');
            $zipFilePath = base_path('upgrade.zip');

            if (!File::exists($hashFilePath)) {
                throw new Exception('Hash file not found.');
            }

            $hashes = json_decode(File::get($hashFilePath), true);

            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception('Failed to create zip file.');
            }

            $progress = progress(label: 'Packing...', steps: count($hashes));
            $progress->start();

            foreach ($hashes as $relativePath => $hash) {
                $fullPath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;

                if (File::exists($fullPath)) {
                    // Add file to the zip archive
                    $zip->addFile($fullPath, $relativePath);
                }

                $progress->advance();
            }

            $zip->close();

            $progress->finish();

            info('Upgrade package created successfully.');
        } catch (Throwable $e) {
            error($e->getMessage());
            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }
}
