<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Feedback;

class FeedbackService
{
    /**
     * 创建反馈与建议
     */
    public function store(array $data): Feedback
    {
        return Feedback::create($data);
    }
}