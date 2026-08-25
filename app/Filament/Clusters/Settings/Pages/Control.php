<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Settings\AppSettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Illuminate\Contracts\Support\Htmlable;

class Control extends Page implements HasForms
{
    use InteractsWithForms;

    protected const FormGeneral = 'general';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string $view = 'filament.clusters.settings.pages.control';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.control.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.control.title');
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
                Section::make(__('admin/setting.control.forms.general.title'))
                    ->schema([
                        $this->getAppEnableSiteFormComponent(),
                        $this->getAppEnableRegistrationFormComponent(),
                        $this->getAppGuestUploadFormComponent(),
                        $this->getAppUserPhoneVerifyFormComponent(),
                        $this->getAppUserEmailVerifyFormComponent(),
                        $this->getAppEnableGalleryFormComponent(),
                        $this->getAppEnableStatApiFormComponent(),
                        $this->getAppStatApiKeyFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormGeneral),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
        ];
    }

    /**
     * 是否启用站点
     * @return Component
     */
    protected function getAppEnableSiteFormComponent(): Component
    {
        return Toggle::make('enable_site')
            ->label(__('admin/setting.control.fields.enable_site.label'))
            ->helperText(__('admin/setting.control.fields.enable_site.helper_text'))
            ->onColor('success')
            ->offColor('danger')
            ->default(true);
    }

    /**
     * 是否启用注册
     * @return Component
     */
    protected function getAppEnableRegistrationFormComponent(): Component
    {
        return Toggle::make('enable_registration')
            ->label(__('admin/setting.control.fields.enable_registration.label'))
            ->onColor('success')
            ->offColor('danger')
            ->helperText(__('admin/setting.control.fields.enable_registration.helper_text'));
    }

    /**
     * 是否启用游客上传
     * @return Component
     */
    protected function getAppGuestUploadFormComponent(): Component
    {
        return Toggle::make('guest_upload')
            ->label(__('admin/setting.control.fields.guest_upload.label'))
            ->onColor('success')
            ->offColor('danger')
            ->helperText(__('admin/setting.control.fields.guest_upload.helper_text'));
    }

    /**
     * 是否启用手机号验证
     * @return Component
     */
    protected function getAppUserPhoneVerifyFormComponent(): Component
    {
        return Toggle::make('user_phone_verify')
            ->label(__('admin/setting.control.fields.user_phone_verify.label'))
            ->onColor('success')
            ->offColor('danger')
            ->helperText(__('admin/setting.control.fields.user_phone_verify.helper_text'));
    }

    /**
     * 是否启用邮箱验证
     * @return Component
     */
    protected function getAppUserEmailVerifyFormComponent(): Component
    {
        return Toggle::make('user_email_verify')
            ->label(__('admin/setting.control.fields.user_email_verify.label'))
            ->onColor('success')
            ->offColor('danger')
            ->helperText(__('admin/setting.control.fields.user_email_verify.helper_text'));
    }

    /**
     * 是否启用统计API
     * @return Component
     */
    protected function getAppEnableStatApiFormComponent(): Component
    {
        return Toggle::make('enable_stat_api')
            ->label(__('admin/setting.control.fields.enable_stat_api.label'))
            ->onColor('success')
            ->offColor('danger')
            ->helperText(__('admin/setting.control.fields.enable_stat_api.helper_text'))
            ->live();
    }

    /**
     * 是否启用图片广场
     * @return Component
     */
    protected function getAppEnableGalleryFormComponent(): Component
    {
        return Toggle::make('enable_explore')
            ->label(__('admin/setting.control.fields.enable_explore.label'))
            ->helperText(__('admin/setting.control.fields.enable_explore.helper_text'))
            ->onColor('success')
            ->offColor('danger')
            ->default(true);
    }

    /**
     * 统计API密钥
     * @return Component
     */
    protected function getAppStatApiKeyFormComponent(): Component
    {
        return TextInput::make('enable_stat_api_key')
            ->label(__('admin/setting.control.fields.enable_stat_api_key.label'))
            ->helperText(__('admin/setting.control.fields.enable_stat_api_key.helper_text'))
            ->visible(fn (callable $get) => $get('enable_stat_api'))
            ->placeholder(__('admin/setting.control.fields.enable_stat_api_key.placeholder'));
    }

    public function submit(string $form): void
    {
        $adminSettings = app(AppSettings::class);
        $adminSettings->fill($this->getForm($form)->getState());
        $adminSettings->save();

        Notification::make()->success()->title(__('admin/setting.saved'))->send();
    }
}
