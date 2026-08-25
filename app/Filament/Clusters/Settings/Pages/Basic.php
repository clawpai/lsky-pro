<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Facades\AppService;
use App\Filament\Clusters\Settings;
use App\Settings\AppSettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Basic extends Page implements HasForms
{
    use InteractsWithForms;

    protected const FormGeneral = 'general';

    protected const OtherSystem = 'other';

    protected static ?string $navigationIcon = 'heroicon-o-bars-3-bottom-right';

    protected static string $view = 'filament.clusters.settings.pages.basic';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.basic.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.basic.title');
    }

    public function mount(): void
    {
        $configs = app(AppSettings::class)->toArray();

        /** @var Form $form */
        foreach ($this->getForms() as $form) {
            $form->fill($configs);
        }
    }

    protected function getForms(): array
    {
        return [
            self::FormGeneral => $this->makeForm()->schema([
                Section::make(__('admin/setting.basic.forms.app.title'))
                    ->schema([
                        Grid::make()->schema([
                            $this->getAppNameFormComponent(),
                            $this->getAppUrlFormComponent(),
                            $this->getAppLicenseKeyFormComponent(),
                            $this->getAppTimezoneComponent(),
                            $this->getAppLocaleComponent(),
                            $this->getAppIpGainMethodComponent(),
                            $this->getAppCurrencyFormComponent(),
                            $this->getImageDriverComponent(),
                        ]),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormGeneral),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
            self::OtherSystem => $this->makeForm()->schema([
                Section::make(__('admin/setting.basic.forms.other.title'))
                    ->schema([
                        $this->getAppIcpNoFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::OtherSystem),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
        ];
    }

    /**
     * 应用名称
     * @return Component
     */
    protected function getAppNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/setting.basic.fields.name.label'))
            ->placeholder(__('admin/setting.basic.fields.name.placeholder'))
            ->maxLength(60)
            ->minLength(1)
            ->required();
    }

    /**
     * 系统 URL
     * @return Component
     */
    protected function getAppUrlFormComponent(): Component
    {
        return TextInput::make('url')
            ->label(__('admin/setting.basic.fields.url.label'))
            ->placeholder(__('admin/setting.basic.fields.url.placeholder'))
            ->url()
            ->required();
    }

    /**
     * App 序列号
     * @return Component
     */
    protected function getAppLicenseKeyFormComponent(): Component
    {
        return TextInput::make('license_key')
            ->label(__('admin/setting.basic.fields.license_key.label'))
            ->placeholder(__('admin/setting.basic.fields.license_key.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * 系统时区
     * @return Component
     */
    protected function getAppTimezoneComponent(): Component
    {
        return Select::make('timezone')
            ->label(__('admin/setting.basic.fields.timezone.label'))
            ->placeholder(__('admin/setting.basic.fields.timezone.placeholder'))
            ->options(AppService::getAllTimezones())
            ->default(app(AppSettings::class)->timezone)
            ->searchable()
            ->required()
            ->native(false);
    }

    /**
     * 系统默认语言
     * @return Component
     */
    protected function getAppLocaleComponent(): Component
    {
        return Select::make('locale')
            ->label(__('admin/setting.basic.fields.locale.label'))
            ->placeholder(__('admin/setting.basic.fields.locale.placeholder'))
            ->options(AppService::getAllLanguages())
            ->default(app(AppSettings::class)->locale)
            ->required()
            ->native(false);
    }

    /**
     * ip 获取方式
     * @return Component
     */
    protected function getAppIpGainMethodComponent(): Component
    {
        return Select::make('ip_gain_method')
            ->label(__('admin/setting.basic.fields.ip_gain_method.label'))
            ->placeholder(__('admin/setting.basic.fields.ip_gain_method.placeholder'))
            ->options(AppService::getAllIpGainMethods())
            ->default(app(AppSettings::class)->ip_gain_method)
            ->required()
            ->native(false);
    }

    /**
     * 系统货币
     * @return Component
     */
    protected function getAppCurrencyFormComponent(): Component
    {
        return TextInput::make('currency')
            ->label(__('admin/setting.basic.fields.currency.label'))
            ->placeholder(__('admin/setting.basic.fields.currency.placeholder'))
            ->maxLength(60)
            ->minLength(1)
            ->required();
    }

    /**
     * 备案号
     * @return Component
     */
    protected function getAppIcpNoFormComponent(): Component
    {
        return TextInput::make('icp_no')
            ->label(__('admin/setting.basic.fields.icp_no.label'))
            ->placeholder(__('admin/setting.basic.fields.icp_no.placeholder'))
            ->maxLength(128);
    }

    /**
     * 系统图片处理驱动
     * @return Component
     */
    protected function getImageDriverComponent(): Component
    {
        return Select::make('image_driver')
            ->label(__('admin/setting.basic.fields.image_driver.label'))
            ->placeholder(__('admin/setting.basic.fields.image_driver.placeholder'))
            ->helperText(new HtmlString(__('admin/setting.basic.fields.image_driver.helper_text')))
            ->options(AppService::getInterventionImageDrivers())
            ->default(app(AppSettings::class)->image_driver)
            ->required()
            ->native(false);
    }

    public function submit(string $form): void
    {
        $appSettings = app(AppSettings::class);
        $appSettings->fill($this->getForm($form)->getState());
        $appSettings->save();

        Notification::make()->success()->title(__('admin/setting.saved'))->send();
    }
}
