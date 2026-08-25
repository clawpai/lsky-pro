<?php

namespace App\Events;

use App\DriverType;
use App\Models\Photo;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadFinished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Photo $photo,
        /**
         * @var array<DriverType>
         */
        public array $processedDrivers = [], // 已经经过处理的驱动
    )
    {
    }
}
