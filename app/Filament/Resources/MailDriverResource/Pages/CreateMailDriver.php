<?php

namespace App\Filament\Resources\MailDriverResource\Pages;

use App\DriverType;
use App\Filament\Resources\MailDriverResource;
use App\MailProvider;
use App\Services\MailService;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class CreateMailDriver extends CreateRecord
{
    protected static string $resource = MailDriverResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return parent::handleRecordCreation([...$data, ...['type' => DriverType::Mail->value]]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label(__('admin/mail_driver.actions.test_connection.label'))
                ->icon('heroicon-o-envelope')
                ->requiresConfirmation()
                ->modalDescription(__('admin/mail_driver.actions.test_connection.confirm'))
                ->action(function () {
                    try {
                        // 捕获可能的验证错误
                        try {
                            $formData = $this->form->getState();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title(__('admin/mail_driver.actions.test_connection.validation_error'))
                                ->body(__('admin/mail_driver.actions.test_connection.please_fill_form'))
                                ->warning()
                                ->send();
                            return;
                        }

                        if (!isset($formData['options']['provider'])) {
                            Notification::make()
                                ->title(__('admin/mail_driver.actions.test_connection.validation_error'))
                                ->body(__('admin/mail_driver.actions.test_connection.select_provider'))
                                ->warning()
                                ->send();
                            return;
                        }

                        $provider = MailProvider::from($formData['options']['provider']);
                        $mailService = new MailService();
                        $result = $mailService->testConnection($provider, $formData['options'] ?? []);
                        
                        if ($result) {
                            Notification::make()
                                ->title(__('admin/mail_driver.actions.test_connection.success'))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('admin/mail_driver.actions.test_connection.failed'))
                                ->danger()
                                ->send();
                        }
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title(__('admin/mail_driver.actions.test_connection.failed'))
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
