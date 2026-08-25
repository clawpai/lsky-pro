<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\AppService;
use App\Filament\Resources\SocialiteDriverResource\Pages;
use App\Models\Driver;
use App\SocialiteProvider;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class SocialiteDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-left-on-rectangle';

    protected static ?int $navigationSort = 26;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/socialite_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/socialite_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Socialite);
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->schema([
                Grid::make()->schema([
                    self::getNameFormComponent(),
                    self::getOptionProviderFormComponent(),
                ]),
                self::getIntroFormComponent(),
                ...self::getGithubOptionFormComponents(),
                ...self::getQQOptionFormComponents(),
                self::getOptionRedirectFormComponent(),
            ]),
        ]);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/socialite_driver.form_fields.name.label'))
            ->placeholder(__('admin/socialite_driver.form_fields.name.placeholder'))
            ->minLength(1)
            ->maxLength(200)
            ->required();
    }

    /**
     * 驱动器
     * @return Component
     */
    protected static function getOptionProviderFormComponent(): Component
    {
        return Select::make('options.provider')
            ->label(__('admin/socialite_driver.form_fields.options.provider.label'))
            ->placeholder(__('admin/socialite_driver.form_fields.options.provider.placeholder'))
            ->options(AppService::getAllSocialiteProviders())
            ->live()
            ->required()
            ->default(SocialiteProvider::Github->value)
            ->afterStateUpdated(function (Get $get, Set $set) {
                $set('options.redirect', AppService::getSocialiteRedirectUrl($get('options.provider')));
            })
            ->native(false);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/socialite_driver.form_fields.intro.label'))
            ->placeholder(__('admin/socialite_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * github 配置表单
     * @return array<Component>
     */
    protected static function getGithubOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                self::getOptionClientIdFormComponent(),
                self::getOptionClientSecretFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SocialiteProvider::Github->value)
        ];
    }

    /**
     * Client ID
     * @return Component
     */
    protected static function getOptionClientIdFormComponent(): Component
    {
        return TextInput::make('options.client_id')
            ->label(__('admin/socialite_driver.form_fields.options.client_id.label'))
            ->placeholder(__('admin/socialite_driver.form_fields.options.client_id.placeholder'))
            ->required();
    }

    /**
     * Client Secret
     * @return Component
     */
    protected static function getOptionClientSecretFormComponent(): Component
    {
        return TextInput::make('options.client_secret')
            ->label(__('admin/socialite_driver.form_fields.options.client_secret.label'))
            ->placeholder(__('admin/socialite_driver.form_fields.options.client_secret.placeholder'))
            ->password()
            ->revealable()
            ->required();
    }

    /**
     * QQ 配置表单
     * @return array
     */
    protected static function getQQOptionFormComponents(): array
    {
        return [
            Grid::make()->schema([
                self::getOptionClientIdFormComponent(),
                self::getOptionClientSecretFormComponent(),
            ])->visible(fn(Get $get): bool => $get('options.provider') === SocialiteProvider::QQ->value)
        ];
    }

    /**
     * 回调地址
     * @return Component
     */
    protected static function getOptionRedirectFormComponent(): Component
    {
        return Textarea::make('options.redirect')
            ->label(__('admin/socialite_driver.form_fields.options.redirect.label'))
            ->readOnly()
            ->formatStateUsing(fn (Get $get): string => AppService::getSocialiteRedirectUrl($get('options.provider')));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialiteDrivers::route('/'),
            'create' => Pages\CreateSocialiteDriver::route('/create'),
            'edit' => Pages\EditSocialiteDriver::route('/{record}/edit'),
        ];
    }
}
