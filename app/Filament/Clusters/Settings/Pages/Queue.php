<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\FailedJob;
use App\Models\Job;
use App\Models\JobBatch;
use Carbon\Carbon;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Queue extends Page implements HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static string $view = 'filament.clusters.settings.pages.queue';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 6;

    public string $activeTab = 'jobs';

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.queue.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.queue.title');
    }

    public function jobsInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('admin/setting.queue.tabs.jobs'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                $this->getTotalJobsTextEntry(),
                                $this->getPendingJobsTextEntry(),
                                $this->getReservedJobsTextEntry(),
                                $this->getQueuesTextEntry(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->paginated([10, 25, 50])
            ->defaultSort('id', 'desc')
            ->poll('5s');
    }

    protected function getTotalJobsTextEntry(): TextEntry
    {
        return TextEntry::make('total')
            ->label(__('admin/setting.queue.stats.total'))
            ->weight(FontWeight::Bold)
            ->color('primary')
            ->state(fn() => Job::count());
    }

    protected function getPendingJobsTextEntry(): TextEntry
    {
        return TextEntry::make('pending')
            ->label(__('admin/setting.queue.stats.pending'))
            ->weight(FontWeight::Bold)
            ->color('warning')
            ->state(fn() => Job::whereNull('reserved_at')->count());
    }

    protected function getReservedJobsTextEntry(): TextEntry
    {
        return TextEntry::make('reserved')
            ->label(__('admin/setting.queue.stats.reserved'))
            ->weight(FontWeight::Bold)
            ->color('info')
            ->state(fn() => Job::whereNotNull('reserved_at')->count());
    }

    protected function getQueuesTextEntry(): TextEntry
    {
        return TextEntry::make('queues')
            ->label(__('admin/setting.queue.stats.queues'))
            ->weight(FontWeight::Bold)
            ->color('success')
            ->state(fn() => Job::distinct()->count('queue'));
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->activeTab) {
            'failed_jobs' => $this->getFailedJobsQuery(),
            'job_batches' => $this->getJobBatchesQuery(),
            default => $this->getJobsQuery(),
        };
    }

    protected function getJobsQuery(): Builder
    {
        return Job::query()->orderBy('id', 'desc');
    }

    protected function getFailedJobsQuery(): Builder
    {
        return FailedJob::query()->orderBy('id', 'desc');
    }

    protected function getJobBatchesQuery(): Builder
    {
        return JobBatch::query()->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return match ($this->activeTab) {
            'failed_jobs' => $this->getFailedJobsColumns(),
            'job_batches' => $this->getJobBatchesColumns(),
            default => $this->getJobsColumns(),
        };
    }

    protected function getJobsColumns(): array
    {
        return [
            $this->getJobIdColumn(),
            $this->getJobQueueColumn(),
            $this->getJobPayloadColumn(),
            $this->getJobAttemptsColumn(),
            $this->getJobAvailableAtColumn(),
            $this->getJobCreatedAtColumn(),
        ];
    }

    protected function getFailedJobsColumns(): array
    {
        return [
            $this->getFailedJobIdColumn(),
            $this->getFailedJobUuidColumn(),
            $this->getFailedJobQueueColumn(),
            $this->getFailedJobPayloadColumn(),
            $this->getFailedJobExceptionColumn(),
            $this->getFailedJobFailedAtColumn(),
        ];
    }

    protected function getJobBatchesColumns(): array
    {
        return [
            $this->getBatchIdColumn(),
            $this->getBatchNameColumn(),
            $this->getBatchTotalJobsColumn(),
            $this->getBatchPendingJobsColumn(),
            $this->getBatchFailedJobsColumn(),
            $this->getBatchCreatedAtColumn(),
            $this->getBatchFinishedAtColumn(),
        ];
    }

    protected function getJobIdColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label(__('admin/setting.queue.fields.id'));
    }

    protected function getJobQueueColumn(): TextColumn
    {
        return TextColumn::make('queue')
            ->label(__('admin/setting.queue.fields.queue'))
            ->badge()
            ->color('info');
    }

    protected function getJobPayloadColumn(): TextColumn
    {
        return TextColumn::make('payload')
            ->label(__('admin/setting.queue.messages.task_type'))
            ->formatStateUsing(function (Job $record) {
                if (isset($record->payload['displayName'])) {
                    return Str::afterLast($record->payload['displayName'], '\\');
                }
                if (isset($record->payload['job'])) {
                    return Str::afterLast($record->payload['job'], '\\');
                }

                return __('admin/setting.queue.messages.unknown_task');
            })
            ->badge()
            ->color('primary')
            ->icon('heroicon-o-cog-6-tooth');
    }

    protected function getJobAttemptsColumn(): TextColumn
    {
        return TextColumn::make('attempts')
            ->label(__('admin/setting.queue.fields.attempts'))
            ->badge()
            ->color(fn ($state) => $state > 0 ? 'warning' : 'success');
    }

    protected function getJobAvailableAtColumn(): TextColumn
    {
        return TextColumn::make('available_at')
            ->label(__('admin/setting.queue.fields.available_at'))
            ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s') : '-');
    }

    protected function getJobCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('admin/setting.queue.fields.created_at'))
            ->formatStateUsing(fn ($state) => Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s'));
    }

    protected function getFailedJobIdColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label(__('admin/setting.queue.fields.id'));
    }

    protected function getFailedJobUuidColumn(): TextColumn
    {
        return TextColumn::make('uuid')
            ->label(__('admin/setting.queue.fields.uuid'))
            ->limit(20)
            ->copyable();
    }

    protected function getFailedJobQueueColumn(): TextColumn
    {
        return TextColumn::make('queue')
            ->label(__('admin/setting.queue.fields.queue'))
            ->badge()
            ->color('info');
    }

    protected function getFailedJobPayloadColumn(): TextColumn
    {
        return TextColumn::make('payload')
            ->label(__('admin/setting.queue.messages.task_type'))
            ->formatStateUsing(function (FailedJob $record) {
                if (isset($record->payload['displayName'])) {
                    return Str::afterLast($record->payload['displayName'], '\\');
                }
                if (isset($record->payload['job'])) {
                    return Str::afterLast($record->payload['job'], '\\');
                }
                return __('admin/setting.queue.messages.unknown_task');
            })
            ->badge()
            ->color('danger')
            ->icon('heroicon-o-exclamation-triangle');
    }

    protected function getFailedJobExceptionColumn(): TextColumn
    {
        return TextColumn::make('exception')
            ->label(__('admin/setting.queue.fields.exception'))
            ->limit(50)
            ->wrap();
    }

    protected function getFailedJobFailedAtColumn(): TextColumn
    {
        return TextColumn::make('failed_at')
            ->label(__('admin/setting.queue.fields.failed_at'))
            ->dateTime('Y-m-d H:i:s');
    }

    protected function getBatchIdColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label(__('admin/setting.queue.fields.id'))
            ->limit(20)
            ->copyable();
    }

    protected function getBatchNameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label(__('admin/setting.queue.fields.name'))
            ->default(__('admin/setting.queue.messages.no_name_batch'));
    }

    protected function getBatchTotalJobsColumn(): TextColumn
    {
        return TextColumn::make('total_jobs')
            ->label(__('admin/setting.queue.fields.total_jobs'))
            ->badge()
            ->color('info');
    }

    protected function getBatchPendingJobsColumn(): TextColumn
    {
        return TextColumn::make('pending_jobs')
            ->label(__('admin/setting.queue.fields.pending_jobs'))
            ->badge()
            ->color('warning');
    }

    protected function getBatchFailedJobsColumn(): TextColumn
    {
        return TextColumn::make('failed_jobs')
            ->label(__('admin/setting.queue.fields.failed_jobs'))
            ->badge()
            ->color('danger');
    }

    protected function getBatchCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('admin/setting.queue.fields.created_at'))
            ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s') : '-');
    }

    protected function getBatchFinishedAtColumn(): TextColumn
    {
        return TextColumn::make('finished_at')
            ->label(__('admin/setting.queue.fields.finished_at'))
            ->formatStateUsing(fn ($state) => $state ? Carbon::createFromTimestamp($state)->format('Y-m-d H:i:s') : __('admin/setting.queue.messages.not_finished'))
            ->default(__('admin/setting.queue.messages.not_finished'));
    }

    protected function getTableActions(): array
    {
        return match ($this->activeTab) {
            'failed_jobs' => [
                $this->getRetryFailedJobAction(),
                $this->getViewExceptionAction(),
                $this->getDeleteFailedJobAction(),
            ],
            default => [],
        };
    }

    protected function getRetryFailedJobAction(): TableAction
    {
        return TableAction::make('retry')
            ->label(__('admin/setting.queue.actions.retry'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('admin/setting.queue.actions.retry'))
            ->modalDescription(__('admin/setting.queue.messages.retry_confirmation'))
            ->modalSubmitActionLabel(__('admin/setting.queue.messages.retry_confirm_button'))
            ->modalCancelActionLabel(__('admin/setting.queue.messages.retry_cancel_button'))
            ->action(function (FailedJob $record) {
                try {
                    // 使用 Artisan 命令重试失败的任务
                    Artisan::call('queue:retry', ['id' => $record->uuid]);
                    
                    Notification::make()
                        ->title(__('admin/setting.queue.messages.retry_success'))
                        ->body(__('admin/setting.queue.messages.retry_success_message', ['id' => $record->id]))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('admin/setting.queue.messages.retry_failed'))
                        ->body(__('admin/setting.queue.messages.retry_failed_message', ['error' => $e->getMessage()]))
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getViewExceptionAction(): TableAction
    {
        return TableAction::make('view_exception')
            ->label(__('admin/setting.queue.actions.view_exception'))
            ->icon('heroicon-o-exclamation-triangle')
            ->color('danger')
            ->modalHeading(__('admin/setting.queue.messages.queue_exception_details'))
            ->modalDescription(fn ($record) => __('admin/setting.queue.messages.failed_task_info', [
                'id' => $record->id,
                'uuid' => $record->uuid
            ]))
            ->modalContent(function ($record) {
                return view('filament.modals.queue-exception', [
                    'record' => $record,
                ]);
            })
            ->modalCancelAction(false)
            ->modalSubmitAction(false);
    }

    protected function getDeleteFailedJobAction(): TableAction
    {
        return TableAction::make('delete')
            ->label(__('admin/setting.queue.actions.delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('admin/setting.queue.actions.delete'))
            ->modalDescription(__('admin/setting.queue.messages.delete_confirmation'))
            ->modalSubmitActionLabel(__('admin/setting.queue.messages.delete_confirm_button'))
            ->modalCancelActionLabel(__('admin/setting.queue.messages.delete_cancel_button'))
            ->action(function (FailedJob $record) {
                $record->delete();
                Notification::make()
                    ->title(__('admin/setting.queue.messages.delete_success'))
                    ->body(__('admin/setting.queue.messages.delete_success_message', ['id' => $record->id]))
                    ->success()
                    ->send();
            });
    }

    protected function getTableBulkActions(): array
    {
        return match ($this->activeTab) {
            'failed_jobs' => [
                $this->getBulkRetryFailedJobsAction(),
                $this->getBulkDeleteFailedJobsAction(),
                $this->getClearFailedJobsAction(),
            ],
            default => [],
        };
    }

    protected function getBulkRetryFailedJobsAction(): BulkAction
    {
        return BulkAction::make('retry')
            ->label(__('admin/setting.queue.actions.bulk_retry'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('admin/setting.queue.actions.bulk_retry'))
            ->modalDescription(__('admin/setting.queue.messages.bulk_retry_confirmation'))
            ->modalSubmitActionLabel(__('admin/setting.queue.messages.retry_confirm_button'))
            ->modalCancelActionLabel(__('admin/setting.queue.messages.retry_cancel_button'))
            ->action(function (Collection $records) {
                $successCount = 0;
                $failedCount = 0;
                
                foreach ($records as $record) {
                    try {
                        Artisan::call('queue:retry', ['id' => $record->uuid]);
                        $successCount++;
                    } catch (\Exception $e) {
                        $failedCount++;
                    }
                }
                
                if ($successCount > 0) {
                    Notification::make()
                        ->title(__('admin/setting.queue.messages.bulk_retry_success'))
                        ->body(__('admin/setting.queue.messages.bulk_retry_success_message', [
                            'success' => $successCount,
                            'failed' => $failedCount
                        ]))
                        ->success()
                        ->send();
                }
                
                if ($failedCount > 0 && $successCount === 0) {
                    Notification::make()
                        ->title(__('admin/setting.queue.messages.bulk_retry_failed'))
                        ->body(__('admin/setting.queue.messages.bulk_retry_failed_message', ['count' => $failedCount]))
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getBulkDeleteFailedJobsAction(): BulkAction
    {
        return BulkAction::make('delete')
            ->label(__('admin/setting.queue.actions.bulk_delete'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('admin/setting.queue.actions.bulk_delete'))
            ->modalDescription(__('admin/setting.queue.messages.bulk_delete_confirmation'))
            ->modalSubmitActionLabel(__('admin/setting.queue.messages.delete_confirm_button'))
            ->modalCancelActionLabel(__('admin/setting.queue.messages.delete_cancel_button'))
            ->action(function (Collection $records) {
                $count = $records->count();
                $records->each->delete();
                Notification::make()
                    ->title(__('admin/setting.queue.messages.bulk_delete_success'))
                    ->body(__('admin/setting.queue.messages.bulk_delete_success_message', ['count' => $count]))
                    ->success()
                    ->send();
            });
    }

    protected function getClearFailedJobsAction(): BulkAction
    {
        return BulkAction::make('clear_all_failed')
            ->label(__('admin/setting.queue.actions.clear_all_failed'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('admin/setting.queue.actions.clear_all_failed'))
            ->modalDescription(__('admin/setting.queue.messages.clear_all_failed_confirmation'))
            ->modalSubmitActionLabel(__('admin/setting.queue.messages.clear_confirm_button'))
            ->modalCancelActionLabel(__('admin/setting.queue.messages.clear_cancel_button'))
            ->action(function () {
                try {
                    DB::transaction(function () {
                        $count = FailedJob::count();
                        
                        if ($count > 0) {
                            FailedJob::query()->delete();
                            
                            Notification::make()
                                ->title(__('admin/setting.queue.messages.clear_all_success'))
                                ->body(__('admin/setting.queue.messages.clear_all_success_message', ['count' => $count]))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin/setting.queue.messages.no_failed_jobs'))
                                ->body(__('admin/setting.queue.messages.no_failed_jobs_message'))
                                ->info()
                                ->send();
                        }
                    });
                        
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('admin/setting.queue.messages.clear_all_failed'))
                        ->body(__('admin/setting.queue.messages.clear_all_error', ['error' => $e->getMessage()]))
                        ->danger()
                        ->send();
                }
            });
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }
} 