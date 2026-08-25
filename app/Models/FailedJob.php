<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

/**
 * 失败队列
 *
 * @property int $id
 * @property string $uuid
 * @property string $connection
 * @property string $queue
 * @property \ArrayObject<array-key, mixed> $payload
 * @property string $exception
 * @property \Illuminate\Support\Carbon $failed_at
 * @method static Builder<static>|FailedJob newModelQuery()
 * @method static Builder<static>|FailedJob newQuery()
 * @method static Builder<static>|FailedJob query()
 * @method static Builder<static>|FailedJob whereConnection($value)
 * @method static Builder<static>|FailedJob whereException($value)
 * @method static Builder<static>|FailedJob whereFailedAt($value)
 * @method static Builder<static>|FailedJob whereId($value)
 * @method static Builder<static>|FailedJob wherePayload($value)
 * @method static Builder<static>|FailedJob whereQueue($value)
 * @method static Builder<static>|FailedJob whereUuid($value)
 * @mixin \Eloquent
 */
class FailedJob extends Model
{
    protected $table = 'failed_jobs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at'
    ];
    
    protected $casts = [
        'payload' => AsArrayObject::class,
        'failed_at' => 'datetime',
    ];
} 