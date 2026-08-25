<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Facades\AppService;
use App\Filament\Clusters\Settings;
use App\Settings\AdminSettings;
use Filament\Enums\ThemeMode;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

class Admin extends Page implements HasForms
{
    use InteractsWithForms;

    protected const FormGeneral = 'general';

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static string $view = 'filament.clusters.settings.pages.admin';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.admin.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.admin.title');
    }

    public function mount(): void
    {
        $configs = app(AdminSettings::class)->toArray();

        /** @var Form $form */
        foreach ($this->getForms() as $form) {
            $form->fill($configs);
        }
    }

    protected function getForms(): array
    {
        return [
            self::FormGeneral => $this->makeForm()->schema([
                Section::make(__('admin/setting.admin.forms.general.title'))
                    ->schema([
                        $this->getAdminTopNavigationFormComponent(),
                        $this->getAdminDarkModeFormComponent(),
                        $this->getAdminDefaultThemeModeFormComponent(),
                        $this->getAdminPrimaryColorFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormGeneral),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
        ];
    }

    /**
     * 是否使用顶部导航
     * @return Component
     */
    protected function getAdminTopNavigationFormComponent(): Component
    {
        return Toggle::make('top_navigation')
            ->label(__('admin/setting.admin.fields.top_navigation.label'))
            ->inline(false)
            ->default(false);
    }

    /**
     * 是否启用浅色模式
     * @return Component
     */
    protected function getAdminDarkModeFormComponent(): Component
    {
        return Toggle::make('dark_mode')
            ->label(__('admin/setting.admin.fields.dark_mode.label'))
            ->inline(false)
            ->default(true);
    }

    /**
     * 默认主题模式
     * @return Component
     */
    protected function getAdminDefaultThemeModeFormComponent(): Component
    {
        return Select::make('default_theme_mode')
            ->label(__('admin/setting.admin.fields.default_theme_mode.label'))
            ->placeholder(__('admin/setting.admin.fields.default_theme_mode.placeholder'))
            ->options([
                ThemeMode::System->value => __('admin/setting.admin.fields.default_theme_mode.options.system'),
                ThemeMode::Light->value => __('admin/setting.admin.fields.default_theme_mode.options.light'),
                ThemeMode::Dark->value => __('admin/setting.admin.fields.default_theme_mode.options.dark'),
            ])
            ->default(ThemeMode::System)
            ->required()
            ->native(false);
    }

    /**
     * 主色调
     * @return Component
     */
    protected function getAdminPrimaryColorFormComponent(): Component
    {
        return Select::make('primary_color')
            ->label(__('admin/setting.admin.fields.primary_color.label'))
            ->placeholder(__('admin/setting.admin.fields.primary_color.placeholder'))
            ->options(AppService::getAllColors())
            ->default('sky')
            ->required()
            ->searchable()
            ->native(false);
    }

    public function submit(string $form): void
    {
        $adminSettings = app(AdminSettings::class);
        $adminSettings->fill($this->getForm($form)->getState());
        $adminSettings->save();

        Notification::make()->success()->title(__('admin/setting.saved'))->send();
    }
}
