<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Facades\AppService;
use App\Filament\Clusters\Settings;
use App\Settings\SiteSettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class Site extends Page implements HasForms
{
    use InteractsWithForms;

    protected const FormGeneral = 'general';

    protected const FormBackground = 'background';

    protected const FormAdvanced = 'advanced';

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static string $view = 'filament.clusters.settings.pages.site';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 3;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('admin/setting.site.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin/setting.site.title');
    }

    public function mount(): void
    {
        $configs = app(SiteSettings::class)->toArray();

        /** @var Form $form */
        foreach ($this->getForms() as $form) {
            $form->fill($configs);
        }
    }

    protected function getForms(): array
    {
        return [
            self::FormGeneral => $this->makeForm()->schema([
                Section::make(__('admin/setting.site.forms.general.title'))
                    ->schema([
                        $this->getSiteThemeFormComponent(),
                        Grid::make()->schema([
                            $this->getSiteTitleFormComponent(),
                            $this->getSiteSubtitleFormComponent(),
                        ]),
                        $this->getSiteHomepageTitleFormComponent(),
                        $this->getSiteHomepageDescriptionFormComponent(),
                        $this->getSiteNoticeFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormGeneral),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
            self::FormBackground => $this->makeForm()->schema([
                Section::make(__('admin/setting.site.forms.background.title'))
                    ->schema([
                        $this->getSiteHomepageBackgroundImageUrlFormComponent(),
                        $this->getSiteAuthPageBackgroundImageUrlFormComponent(),
                        $this->getSiteHomepageBackgroundImagesFormComponent(),
                        $this->getSiteAuthPageBackgroundImagesFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormBackground),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
            self::FormAdvanced => $this->makeForm()->schema([
                Section::make(__('admin/setting.site.forms.advanced.title'))
                    ->schema([
                        $this->getSiteCustomCssFormComponent(),
                        $this->getSiteCustomJsFormComponent(),
                    ])->footerActions([
                        Action::make(__('admin/setting.submit'))->submit(self::FormAdvanced),
                    ])->footerActionsAlignment(Alignment::End),
            ])->statePath('data'),
        ];
    }

    /**
     * 网站主题
     * @return Component
     */
    protected function getSiteThemeFormComponent(): Component
    {
        return Select::make('theme')
            ->label(__('admin/setting.site.fields.theme.label'))
            ->options(Arr::pluck(AppService::getThemes(), 'name', 'id'))
            ->required()
            ->placeholder(__('admin/setting.site.fields.theme.placeholder'));
    }

    /**
     * 网站标题
     * @return Component
     */
    protected function getSiteTitleFormComponent(): Component
    {
        return TextInput::make('title')
            ->label(__('admin/setting.site.fields.title.label'))
            ->maxLength(60)
            ->minLength(1)
            ->required()
            ->placeholder(__('admin/setting.site.fields.title.placeholder'));
    }

    /**
     * 网站副标题
     * @return Component
     */
    protected function getSiteSubtitleFormComponent(): Component
    {
        return TextInput::make('subtitle')
            ->label(__('admin/setting.site.fields.subtitle.label'))
            ->maxLength(60)
            ->placeholder(__('admin/setting.site.fields.subtitle.placeholder'));
    }

    /**
     * 首页横幅标题
     * @return Component
     */
    protected function getSiteHomepageTitleFormComponent(): Component
    {
        return TextInput::make('homepage_title')
            ->label(__('admin/setting.site.fields.homepage_title.label'))
            ->maxLength(60)
            ->placeholder(__('admin/setting.site.fields.homepage_title.placeholder'));
    }

    /**
     * 首页横幅描述
     * @return Component
     */
    protected function getSiteHomepageDescriptionFormComponent(): Component
    {
        return Textarea::make('homepage_description')
            ->label(__('admin/setting.site.fields.homepage_description.label'))
            ->maxLength(400)
            ->placeholder(__('admin/setting.site.fields.homepage_description.placeholder'));
    }

    /**
     * 弹出公告
     * @return Component
     */
    protected function getSiteNoticeFormComponent(): Component
    {
        return TiptapEditor::make('notice')
            ->label(__('admin/setting.site.fields.notice.label'))
            ->placeholder(__('admin/setting.site.fields.notice.placeholder'));
    }

    /**
     * 首页背景图地址
     * @return Component
     */
    protected function getSiteHomepageBackgroundImageUrlFormComponent(): Component
    {
        return TextInput::make('homepage_background_image_url')
            ->label(__('admin/setting.site.fields.homepage_background_image_url.label'))
            ->url()
            ->placeholder(__('admin/setting.site.fields.homepage_background_image_url.placeholder'));
    }

    /**
     * 授权页背景图地址
     * @return Component
     */
    protected function getSiteAuthPageBackgroundImageUrlFormComponent(): Component
    {
        return TextInput::make('auth_page_background_image_url')
            ->label(__('admin/setting.site.fields.auth_page_background_image_url.label'))
            ->url()
            ->placeholder(__('admin/setting.site.fields.auth_page_background_image_url.placeholder'));
    }

    /**
     * 首页背景图
     * @return Component
     */
    protected function getSiteHomepageBackgroundImagesFormComponent(): Component
    {
        return FileUpload::make('homepage_background_images')
            ->label(__('admin/setting.site.fields.homepage_background_images.label'))
            ->multiple()
            ->image()
            ->imageEditor()
            ->placeholder(__('admin/setting.site.fields.homepage_background_images.placeholder'));
    }

    /**
     * 授权页背景图地址
     * @return Component
     */
    protected function getSiteAuthPageBackgroundImagesFormComponent(): Component
    {
        return FileUpload::make('auth_page_background_images')
            ->label(__('admin/setting.site.fields.auth_page_background_images.label'))
            ->multiple()
            ->image()
            ->imageEditor()
            ->placeholder(__('admin/setting.site.fields.auth_page_background_images.placeholder'));
    }

    /**
     * 自定义CSS
     * @return Component
     */
    protected function getSiteCustomCssFormComponent(): Component
    {
        return Textarea::make('custom_css')
            ->label(__('admin/setting.site.fields.custom_css.label'))
            ->placeholder(__('admin/setting.site.fields.custom_css.placeholder'))
            ->rows(10)
            ->columnSpanFull();
    }

    /**
     * 自定义JavaScript
     * @return Component
     */
    protected function getSiteCustomJsFormComponent(): Component
    {
        return Textarea::make('custom_js')
            ->label(__('admin/setting.site.fields.custom_js.label'))
            ->placeholder(__('admin/setting.site.fields.custom_js.placeholder'))
            ->rows(10)
            ->columnSpanFull();
    }

    public function submit(string $form): void
    {
        $adminSettings = app(SiteSettings::class);
        $adminSettings->fill($this->getForm($form)->getState());
        $adminSettings->save();

        Notification::make()->success()->title(__('admin/setting.saved'))->send();
    }
}
