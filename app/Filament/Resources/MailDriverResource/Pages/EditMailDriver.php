<?php

namespace App\Filament\Resources\MailDriverResource\Pages;

use App\Filament\Resources\MailDriverResource;
use App\MailProvider;
use App\Models\Driver;
use App\Services\MailService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditMailDriver extends EditRecord
{
    protected static string $resource = MailDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label(__('admin/mail_driver.actions.test_connection.label'))
                ->icon('heroicon-o-envelope')
                ->requiresConfirmation()
                ->modalDescription(__('admin/mail_driver.actions.test_connection.confirm'))
                ->action(function () {
                    /** @var Driver $record */
                    $record = $this->getRecord();
                    try {
                        $provider = MailProvider::from($record->options['provider']);
                        $mailService = new MailService();
                        $result = $mailService->testConnection($provider, $record->options->getArrayCopy());
                        
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
            Actions\DeleteAction::make(),
        ];
    }
}
