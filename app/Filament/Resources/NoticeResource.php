<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticeResource\Pages;
use App\Models\Notice;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use FilamentTiptapEditor\TiptapEditor;

class NoticeResource extends Resource
{
    protected static ?string $model = Notice::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?int $navigationSort = 32;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.system');
    }

    public static function getModelLabel(): string
    {
        return __('admin/notice.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/notice.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->columns(1)->schema([
                Grid::make()->schema([
                    self::getTitleFormComponent(),
                    self::getSortFormComponent(),
                ]),
                self::getContentComponent(),
            ]),
        ]);
    }

    /**
     * 标题
     * @return Component
     */
    protected static function getTitleFormComponent(): Component
    {
        return TextInput::make('title')
            ->label(__('admin/notice.form_fields.title.label'))
            ->placeholder(__('admin/notice.form_fields.title.placeholder'))
            ->maxLength(200)
            ->required();
    }

    /**
     * 排序值
     * @return Component
     */
    protected static function getSortFormComponent(): Component
    {
        return TextInput::make('sort')
            ->label(__('admin/notice.form_fields.sort.label'))
            ->placeholder(__('admin/notice.form_fields.sort.placeholder'))
            ->integer()
            ->default(0)
            ->required();
    }

    /**
     * 内容
     * @return Component
     */
    protected static function getContentComponent(): Component
    {
        return TiptapEditor::make('content')
            ->label(__('admin/notice.form_fields.content.label'))
            ->placeholder(__('admin/notice.form_fields.content.placeholder'))
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotices::route('/'),
            'create' => Pages\CreateNotice::route('/create'),
            'edit' => Pages\EditNotice::route('/{record}/edit'),
        ];
    }
}
