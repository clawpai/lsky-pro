<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\ProcessDriverResource\Pages;
use App\Forms\Components\Code;
use App\Http\Responses\SymfonyResponseFactory;
use App\Http\Responses\XSendfileResponseFactory;
use App\Models\Driver;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class ProcessDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 23;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/process_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/process_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Process);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()->columns(1)->schema([
                self::getTabsComponent(),
            ])
        ]);
    }

    protected static function getTabsComponent(): Tabs
    {
        return Tabs::make()->tabs([
            self::getBasicSettingComponent(),
            self::getPresetSettingComponent(),
            self::getSupportedParamsComponent(),
        ])
            ->persistTab()
            ->id('process-driver-tabs');
    }

    /**
     * 基础设置 Tab
     * @return Tabs\Tab
     */
    protected static function getBasicSettingComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/process_driver.form_tabs.basic'))->id('basic')->schema([
            self::getNameFormComponent(),
            self::getIntroFormComponent(),
            self::getOptionCacheFormComponent(),
            self::getOptionResponseFormComponent(),
            self::getOptionRewriteFormComponent(),
        ]);
    }

    /**
     * 预设版本 Tab
     * @return Tabs\Tab
     */
    protected static function getPresetSettingComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/process_driver.form_tabs.preset'))->id('preset')->schema([
            self::getOptionPresetsComponent(),
        ]);
    }

    /**
     * 支持参数 Tab
     * @return Tabs\Tab
     */
    protected static function getSupportedParamsComponent(): Tabs\Tab
    {
        return Tabs\Tab::make(__('admin/process_driver.form_tabs.supported_param'))->id('supported-param')->schema([
            self::getOptionSupportedParamsComponent(),
        ]);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/process_driver.form_fields.name.label'))
            ->placeholder(__('admin/process_driver.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/process_driver.form_fields.intro.label'))
            ->placeholder(__('admin/process_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 云处理缓存文件根目录
     * @return Component
     */
    protected static function getOptionCacheFormComponent(): Component
    {
        return TextInput::make('options.cache')
            ->label(__('admin/process_driver.form_fields.options_cache.label'))
            ->placeholder(__('admin/process_driver.form_fields.options_cache.placeholder'))
            ->helperText(__('admin/process_driver.form_fields.options_cache.helper_text'))
            ->live()
            ->default(storage_path('app/cache'))
            ->required();
    }

    /**
     * 云处理文件输出方式
     * @return Component
     */
    protected static function getOptionResponseFormComponent(): Component
    {
        return Select::make('options.response')
            ->label(__('admin/process_driver.form_fields.options_response.label'))
            ->placeholder(__('admin/process_driver.form_fields.options_response.placeholder'))
            ->helperText(__('admin/process_driver.form_fields.options_response.helper_text'))
            ->options([
                SymfonyResponseFactory::class => 'StreamedResponse',
                XSendfileResponseFactory::class => 'X-Sendfile',
            ])
            ->default(SymfonyResponseFactory::class)
            ->native(false)
            ->live()
            ->required();
    }

    /**
     * 云处理 X-Sendfile 配置
     * @return Component
     */
    protected static function getOptionRewriteFormComponent(): Component
    {
        return Tabs::make('rewrites')->schema([
            Tabs\Tab::make('Nginx')->id('nginx')->schema(fn(): array => [
                Code::make()->content(function (Get $get) {
                    return <<<EOF
location /i {
  internal;
  alias {$get('options.cache')};
}
EOF;
                }),
            ]),
        ])->visible(fn(Get $get): bool => $get('options.response') === XSendfileResponseFactory::class);
    }

    /**
     * 支持的参数配置
     * @return Component
     */
    protected static function getOptionPresetsComponent(): Component
    {
        return Repeater::make('options.presets')
            ->label(__('admin/process_driver.form_fields.options_presets.label'))
            ->helperText(__('admin/process_driver.form_fields.options_presets.helper_text'))
            ->schema([
                TextInput::make('name')
                    ->label(__('admin/process_driver.form_fields.options_presets.form_fields.name.label'))
                    ->placeholder(__('admin/process_driver.form_fields.options_presets.form_fields.name.placeholder'))
                    ->distinct()
                    ->alphaDash()
                    ->required(),
                Toggle::make('is_default')
                    ->inline(false)
                    ->label(__('admin/process_driver.form_fields.options_presets.form_fields.is_default.label'))
                    ->helperText(__('admin/process_driver.form_fields.options_presets.form_fields.is_default.helper_text'))
                    ->fixIndistinctState(),
                KeyValue::make('params')
                    ->label(__('admin/process_driver.form_fields.options_presets.form_fields.params.label'))
                    ->helperText(new HtmlString(__('admin/process_driver.form_fields.options_presets.form_fields.params.helper_text')))
                    ->keyLabel(__('admin/process_driver.form_fields.options_presets.form_fields.params.key_label'))
                    ->valueLabel(__('admin/process_driver.form_fields.options_presets.form_fields.params.value_label'))
                    ->keyPlaceholder(__('admin/process_driver.form_fields.options_presets.form_fields.params.key_placeholder'))
                    ->keyPlaceholder(__('admin/process_driver.form_fields.options_presets.form_fields.params.key_placeholder'))
                    ->valuePlaceholder(__('admin/process_driver.form_fields.options_presets.form_fields.params.value_placeholder'))
                    ->required(),
            ]);
    }

    /**
     * 支持的参数配置
     * @return Component
     */
    protected static function getOptionSupportedParamsComponent(): Component
    {
        $descriptions = collect(AppService::getProcessSupportedParams())->pluck('description', 'name')
            ->transform(fn ($item) => new HtmlString($item));
        return CheckboxList::make('options.supported_params')
            ->columns(4)
            ->label(__('admin/process_driver.form_fields.options_supported_params.label'))
            ->options(Arr::pluck(AppService::getProcessSupportedParams(), 'name', 'name'))
            ->descriptions($descriptions)
            ->helperText(__('admin/process_driver.form_fields.options_supported_params.helper_text'))
            ->bulkToggleable();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcessDrivers::route('/'),
            'create' => Pages\CreateProcessDriver::route('/create'),
            'edit' => Pages\EditProcessDriver::route('/{record}/edit'),
        ];
    }
}
