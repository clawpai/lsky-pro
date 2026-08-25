<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use App\PlanType;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 13;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/plan.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/plan.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('admin/plan.sections.general'))->columnSpan(1)->schema([
                self::getIsUpFormComponent(),
                self::getTypeComponent(),
                Grid::make()->schema([
                    self::getNameFormComponent(),
                    self::getSortFormComponent(),
                ]),
                self::getGroupFormComponent()->visible(fn(Get $get): bool => $get('type') === PlanType::Vip->value),
                self::getCapacityFormComponent()->visible(fn(Get $get): bool => $get('type') === PlanType::Storage->value),
                self::getBadgeFormComponent(),
                self::getIntroFormComponent(),
                self::getFeaturesFormComponent(),
            ]),
            Section::make(__('admin/plan.sections.price'))->columnSpan(1)->schema([
                self::getPricesFormComponent(),
            ]),
        ]);
    }

    /**
     * 是否上架
     * @return Component
     */
    protected static function getIsUpFormComponent(): Component
    {
        return Toggle::make('is_up')
            ->label(__('admin/plan.form_fields.is_up.label'))
            ->default(false);
    }

    /**
     * 套餐类型
     * @return Component
     */
    protected static function getTypeComponent(): Component
    {
        $types = collect(PlanType::cases())->map(fn (PlanType $type) => [
            $type->value => __("admin.plan_types.{$type->value}")
        ])->collapse()->toArray();

        return Radio::make('type')
            ->label(__('admin/plan.form_fields.type.label'))
            ->options($types)
            ->inline()
            ->live()
            ->inlineLabel(false);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/plan.form_fields.name.label'))
            ->placeholder(__('admin/plan.form_fields.name.placeholder'))
            ->minLength(1)
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
            ->label(__('admin/plan.form_fields.sort.label'))
            ->placeholder(__('admin/plan.form_fields.sort.placeholder'))
            ->numeric()
            ->default(0)
            ->required();
    }

    /**
     * 角色组
     * @return Component
     */
    protected static function getGroupFormComponent(): Component
    {
        return Group::make()
            ->relationship('group')
            ->schema([
                Select::make('group_id')
                    ->label(__('admin/plan.form_fields.group_id.label'))
                    ->placeholder(__('admin/plan.form_fields.group_id.placeholder'))
                    ->helperText(__('admin/plan.form_fields.group_id.helper_text'))
                    ->options(fn(): array => \App\Models\Group::get()->pluck('name', 'id')->toArray())
                    ->native(false)
            ]);
    }

    /**
     * 容量
     * @return Component
     */
    protected static function getCapacityFormComponent(): Component
    {
        return Group::make()
            ->relationship('capacity')
            ->schema([
                TextInput::make('capacity')
                    ->label(__('admin/plan.form_fields.capacity.label'))
                    ->placeholder(__('admin/plan.form_fields.capacity.placeholder'))
                    ->helperText(__('admin/plan.form_fields.capacity.helper_text'))
                    ->numeric()
                    ->step(0.01)
                    ->suffix('KB')
            ]);
    }

    /**
     * 角标内容
     * @return Component
     */
    protected static function getBadgeFormComponent(): Component
    {
        return TextInput::make('badge')
            ->label(__('admin/plan.form_fields.badge.label'))
            ->placeholder(__('admin/plan.form_fields.badge.placeholder'))
            ->minLength(1)
            ->maxLength(60)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 简介
     * @return Component
     */
    protected static function getIntroFormComponent(): Component
    {
        return Textarea::make('intro')
            ->label(__('admin/plan.form_fields.intro.label'))
            ->placeholder(__('admin/plan.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 套餐权益
     * @return Component
     */
    protected static function getFeaturesFormComponent(): Component
    {
        return Repeater::make('features')
            ->label(__('admin/plan.form_fields.features.label'))
            ->simple(
                TextInput::make('feature')->placeholder(__('admin/plan.form_fields.features.placeholder'))->required()
            )
            ->required();
    }

    /**
     * 阶梯价格表单
     * @return Component
     */
    protected static function getPricesFormComponent(): Component
    {
        return Repeater::make('prices')
            ->label(__('admin/plan.form_fields.prices.label'))
            ->helperText(__('admin/plan.form_fields.prices.helper_text'))
            ->relationship()
            ->columns(3)
            ->schema([
                self::getPricesNameFormComponent(),
                self::getPricesDurationFormComponent(),
                self::getPricesPriceFormComponent(),
            ])
            ->reorderable()
            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
            ->required();
    }

    /**
     * 价格名称
     * @return Component
     */
    protected static function getPricesNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/plan.form_fields.prices.children.name.label'))
            ->placeholder(__('admin/plan.form_fields.prices.children.name.placeholder'))
            ->maxLength(80)
            ->required()
            ->live();
    }

    /**
     * 时长
     * @return Component
     */
    protected static function getPricesDurationFormComponent(): Component
    {
        return TextInput::make('duration')
            ->label(__('admin/plan.form_fields.prices.children.duration.label'))
            ->placeholder(__('admin/plan.form_fields.prices.children.duration.placeholder'))
            ->numeric()
            ->default(1)
            ->minValue(1)
            ->required();
    }

    /**
     * 阶梯价格
     * @return Component
     */
    protected static function getPricesPriceFormComponent(): Component
    {
        return TextInput::make('price')
            ->label(__('admin/plan.form_fields.prices.children.price.label'))
            ->placeholder(__('admin/plan.form_fields.prices.children.price.placeholder'))
            ->numeric()
            ->step(0.01)
            ->default(0)
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
