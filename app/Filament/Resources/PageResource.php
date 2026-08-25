<?php

namespace App\Filament\Resources;

use App\Facades\AppService;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use App\PageType;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use FilamentTiptapEditor\TiptapEditor;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 33;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin/page.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/page.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->columns(1)->schema([
                self::getTypeFormComponent(),
                Grid::make()->schema([
                    self::getIconFormComponent(),
                    self::getNameFormComponent(),
                ]),
                self::getUrlFormComponent()->visible(fn(Get $get): bool => $get('type') == PageType::External->value),
                self::getUrlSlugFormComponent(),
                Grid::make()->columns(1)->schema([
                    self::getTitleFormComponent(),
                    self::getContentFormComponent(),
                ])->visible(fn(Get $get): bool => $get('type') == PageType::Internal->value),
                self::getSortFormComponent(),
                self::getIsShowFormComponent(),
            ]),
        ]);
    }

    /**
     * 页面类型
     * @return Component
     */
    protected static function getTypeFormComponent(): Component
    {
        return Radio::make('type')
            ->label(__('admin/page.form_fields.type.label'))
            ->options(AppService::getAllPageTypes())
            ->default(PageType::Internal->value)
            ->live()
            ->inline()
            ->inlineLabel(false)
            ->required();
    }

    /**
     * 图标
     * @return Component
     */
    protected static function getIconFormComponent(): Component
    {
        return TextInput::make('icon')
            ->label(__('admin/page.form_fields.icon.label'))
            ->placeholder(__('admin/page.form_fields.icon.placeholder'))
            ->helperText(__('admin/page.form_fields.icon.helper_text'))
            ->default('fa-file-alt')
            ->required();
    }

    /**
     * 页面名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/page.form_fields.name.label'))
            ->placeholder(__('admin/page.form_fields.name.placeholder'))
            ->maxLength(80)
            ->required();
    }

    /**
     * 外链 url
     * @return Component
     */
    protected static function getUrlFormComponent(): Component
    {
        return TextInput::make('url')
            ->label(__('admin/page.form_fields.url.label'))
            ->placeholder(__('admin/page.form_fields.url.placeholder'))
            ->required();
    }

    /**
     * 链接 Slug
     * @return Component
     */
    protected static function getUrlSlugFormComponent(): Component
    {
        return TextInput::make('slug')
            ->label(__('admin/page.form_fields.slug.label'))
            ->placeholder(__('admin/page.form_fields.slug.placeholder'))
            ->unique(ignoreRecord: true)
            ->required(fn(Get $get): bool => $get('type') === PageType::Internal->value)
            ->visible(fn(Get $get): bool => $get('type') === PageType::Internal->value);
    }

    /**
     * 页面标题
     * @return Component
     */
    protected static function getTitleFormComponent(): Component
    {
        return TextInput::make('title')
            ->label(__('admin/page.form_fields.title.label'))
            ->placeholder(__('admin/page.form_fields.title.placeholder'))
            ->mutateDehydratedStateUsing(fn(?string $state) => (string)$state)
            ->maxLength(200);
    }

    /**
     * 页面内容
     * @return Component
     */
    protected static function getContentFormComponent(): Component
    {
        return TiptapEditor::make('content')
            ->label(__('admin/page.form_fields.content.label'))
            ->placeholder(__('admin/page.form_fields.content.placeholder'))
            ->required();
    }

    /**
     * 排序值
     * @return Component
     */
    protected static function getSortFormComponent(): Component
    {
        return TextInput::make('sort')
            ->label(__('admin/page.form_fields.sort.label'))
            ->placeholder(__('admin/page.form_fields.sort.placeholder'))
            ->numeric()
            ->default(0)
            ->required();
    }

    /**
     * 是否显示
     * @return Component
     */
    protected static function getIsShowFormComponent(): Component
    {
        return Toggle::make('is_show')
            ->label(__('admin/page.form_fields.is_show.label'))
            ->default(true)
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
