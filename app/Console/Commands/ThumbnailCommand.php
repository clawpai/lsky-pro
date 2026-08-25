<?php

namespace App\Console\Commands;

use App\Facades\PhotoService;
use App\Models\Photo;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;
use function Laravel\Prompts\progress;

#[AsCommand(name: 'app:thumbnail')]
class ThumbnailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:thumbnail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate photo thumbnail';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $progress = progress(label: 'Generating...', steps: Photo::count());
        $progress->start();

        /** @var Photo $photo */
        foreach (Photo::cursor() as $photo) {
            $photo->loadMissing('storage');
            
            // 检查储存配置是否需要生成缩略图
            $generateThumbnail = data_get($photo->storage->options, 'generate_thumbnail', true);
            
            // 判断缩略图是否存在且需要生成缩略图
            if ($generateThumbnail && !$photo->thumbnailFilesystem()->fileExists($photo->thumbnail_pathname)) {

                try {
                    $maxSize = data_get($photo->storage->options, 'thumbnail_max_size', 800);
                    $quality = data_get($photo->storage->options, 'thumbnail_quality', 90);
                    
                    PhotoService::generateThumbnail($photo, $maxSize, $quality);
                } catch (Throwable $e) {
                    //...
                }
            }
            $progress->advance();
        }

        $progress->finish();
    }
}
