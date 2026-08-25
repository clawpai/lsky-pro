<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\UserSettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

class User extends Page implements HasForms
{
    use InteractsWithForms;

    protected const FormGeneral = 'general';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.clusters.settings.pages.user';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.user.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.user.title');
    }

    public function mount(): void
    {
        $configs = app(UserSettings::class)->toArray();

        /** @var Form $form */
        foreach ($this->getForms() as $form) {
            $form->fill($configs);
        }
    }

    protected function getForms(): array
    {
        return [
            self::FormGeneral => $this->makeForm()->schema([
                Section::make(__('admin/setting.user.forms.general.title'))
                    ->schema([
                        $this->getUserInitialCapacityFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormGeneral),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
        ];
    }

    /**
     * 用户初始容量(kb)
     * @return Component
     */
    protected function getUserInitialCapacityFormComponent(): Component
    {
        return TextInput::make('initial_capacity')
            ->label(__('admin/setting.basic.fields.initial_capacity.label'))
            ->placeholder(__('admin/setting.basic.fields.initial_capacity.placeholder'))
            ->numeric(60)
            ->step(0.01)
            ->minValue(0)
            ->default(5120)
            ->suffix('KB')
            ->required();
    }

    public function submit(string $form): void
    {
        $userSettings = app(UserSettings::class);
        $userSettings->fill($this->getForm($form)->getState());
        $userSettings->save();

        Notification::make()->success()->title(__('admin/setting.saved'))->send();
    }
}
