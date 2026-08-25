<?php

namespace App\Filament\Resources\StorageResource\Pages;

use App\Facades\StorageService;
use App\Filament\Resources\StorageResource;
use App\Models\Driver;
use App\Models\Storage;
use App\StorageProvider;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Throwable;

class EditStorage extends EditRecord
{
    protected static string $resource = StorageResource::class;

    /**
     * @param Driver $record
     * @param array $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $prefix = $record->prefix;
        $root = data_get($data, 'options.root');

        if (data_get($data, 'provider') === StorageProvider::Local->value) {
            StorageService::makeStorageDirectory($record->options->getArrayCopy());

            // 如果是本地储存并且未开启云处理，创建符号链接
            // 如果存在符号链接会导致优先读取符号链接中的图片
            if (! data_get($data, 'process_driver_id', false)) {
                $newPrefix = public_path(data_get($data, 'prefix'));

                // 判断是否更改了符号链接
                if ($prefix != data_get($data, 'prefix')) {
                    // 删除旧的符号链接
                    File::delete(public_path($prefix));
                }

                // 判断符号链接是否存在
                if (!File::exists(public_path($prefix))) {
                    // 创建新的符号链接
                    File::link($root, $newPrefix);
                }
            } else {
                // 删除符号链接
                File::delete(public_path($prefix));
            }
        }

        return parent::handleRecordUpdate($record, $data);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label(__('admin/storage.actions.test_connection.label'))
                ->icon('heroicon-o-link')
                ->requiresConfirmation()
                ->modalDescription(__('admin/storage.actions.test_connection.confirm'))
                ->action(function () {
                    /** @var Storage $record */
                    $record = $this->getRecord();
                    try {
                        $result = StorageService::testConnection($record->provider, $record->options->getArrayCopy());
                        
                        if ($result) {
                            Notification::make()
                                ->title(__('admin/storage.actions.test_connection.success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin/storage.actions.test_connection.failed'))
                                ->danger()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title(__('admin/storage.actions.test_connection.failed'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
