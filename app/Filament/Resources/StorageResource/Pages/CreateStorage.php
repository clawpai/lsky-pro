<?php

namespace App\Filament\Resources\StorageResource\Pages;

use App\Facades\StorageService;
use App\Filament\Resources\StorageResource;
use App\Models\Driver;
use App\StorageProvider;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Throwable;

class CreateStorage extends CreateRecord
{
    protected static string $resource = StorageResource::class;

    public function mount(): void
    {
        $this->form->fill([
            'options' => Driver::getStorageDefaultOptions()
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $prefix = data_get($data, 'prefix');
        $root = data_get($data, 'options.root');

        if (data_get($data, 'provider') === StorageProvider::Local->value) {
            StorageService::makeStorageDirectory(data_get($data, 'options', []));

            // 如果是本地储存并且未开启云处理，创建符号链接
            // 如果存在符号链接会导致优先读取符号链接中的图片
            if (!data_get($data, 'process_driver_id', false)) {
                if (!File::exists(public_path($prefix))) {
                    // 创建符号链接
                    File::link($root, $prefix);
                }
            } else {
                // 删除符号链接
                File::delete($prefix);
            }
        }

        return parent::handleRecordCreation($data);
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
                    try {
                        // 捕获可能的验证错误
                        try {
                            $formData = $this->form->getState();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title(__('admin/storage.actions.test_connection.validation_error'))
                                ->body(__('admin/storage.actions.test_connection.please_fill_form'))
                                ->warning()
                                ->send();
                            return;
                        }

                        $provider = StorageProvider::from($formData['provider']);
                        $result = StorageService::testConnection($provider, $formData['options'] ?? []);
                        
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
