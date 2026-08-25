<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Photo;
use App\Models\Violation;
use App\PhotoStatus;
use App\ViolationStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ViolationService
{
    /**
     * 删除图片记录以及物理文件
     *
     * @param Violation $violation
     * @return bool
     */
    public function deletePhoto(Violation $violation): bool
    {
        $this->handle($violation);
        return $violation->photo->delete();
    }

    /**
     * 处理工单
     *
     * @param Violation $violation
     * @return bool
     */
    public function handle(Violation $violation): bool
    {
        $violation->status = ViolationStatus::Handled;
        $violation->handled_at = now();
        return $violation->save();
    }

    /**
     * 删除违规记录并恢复关联图片状态（支持单个或批量）
     *
     * @param Violation|array|Collection $violations
     * @return int 删除成功的数量
     */
    public function deleteViolationsAndRestorePhotos($violations): int
    {
        if ($violations instanceof Violation) {
            $violations = collect([$violations]);
        } elseif (is_array($violations)) {
            $violations = collect($violations);
        } elseif (!$violations instanceof Collection) {
            $violations = collect($violations);
        }

        if ($violations->isEmpty()) {
            return 0;
        }

        $deletedCount = 0;
        
        $groupedByPhoto = $violations->groupBy('photo_id');
        
        DB::transaction(function () use ($groupedByPhoto, &$deletedCount) {
            foreach ($groupedByPhoto as $photoId => $photoViolations) {
                if (empty($photoId)) {
                    continue;
                }

                $violationIds = $photoViolations->pluck('id')->toArray();

                Violation::whereIn('id', $violationIds)->update([
                    'status' => ViolationStatus::Handled,
                    'handled_at' => now(),
                ]);

                $remainingViolations = Violation::where('photo_id', $photoId)
                    ->whereNotIn('id', $violationIds)
                    ->where('status', ViolationStatus::Unhandled)
                    ->count();

                if ($remainingViolations === 0) {
                    Photo::where('id', $photoId)
                        ->where('status', PhotoStatus::Violation)
                        ->update(['status' => PhotoStatus::Normal]);
                }

                $deleted = Violation::whereIn('id', $violationIds)->delete();
                $deletedCount += $deleted;
            }
        });
        
        return $deletedCount;
    }

    /**
     * 删除违规记录并恢复关联图片状态（单个记录的便捷方法）
     *
     * @param Violation $violation
     * @return bool
     */
    public function deleteViolationAndRestorePhoto(Violation $violation): bool
    {
        return $this->deleteViolationsAndRestorePhotos($violation) > 0;
    }
}
