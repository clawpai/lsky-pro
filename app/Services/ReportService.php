<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Report;
use App\ReportStatus;

class ReportService
{
    /**
     * 处理举报
     *
     * @param Report $report
     * @return bool
     */
    public function handle(Report $report): bool
    {
        $report->status = ReportStatus::Handled;
        $report->handled_at = now();
        return $report->save();
    }
}
