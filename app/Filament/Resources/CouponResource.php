<?php

namespace App\Filament\Resources;

use App\CouponType;
use App\Facades\AppService;
use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Str;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-yen';

    protected static ?int $navigationSort = 14;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.operate');
    }

    public static function getModelLabel(): string
    {
        return __('admin/coupon.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/coupon.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                self::getTypeFormComponent(),
                self::getNameFormComponent(),
                Grid::make()->schema([
                    self::getCodeFormComponent(),
                    self::getUsageLimitFormComponent(),
                    self::getValueFormComponent(),
                    self::getExpiredAtFormComponent(),
                ]),
            ]),
        ]);
    }

    /**
     * 类型
     * @return Component
     */
    protected static function getTypeFormComponent(): Component
    {
        return Radio::make('type')
            ->label(__('admin/coupon.form_fields.type.label'))
            ->options(AppService::getAllCouponTypes())
            ->default(CouponType::Direct->value)
            ->live()
            ->required();
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/coupon.form_fields.name.label'))
            ->placeholder(__('admin/coupon.form_fields.name.placeholder'))
            ->maxLength(200)
            ->required();
    }

    /**
     * 券码
     * @return Component
     */
    protected static function getCodeFormComponent(): Component
    {
        return TextInput::make('code')
            ->label(__('admin/coupon.form_fields.code.label'))
            ->placeholder(__('admin/coupon.form_fields.code.placeholder'))
            ->maxLength(20)
            ->unique(ignoreRecord: true)
            ->default(Str::upper(Str::random(6)))
            ->required()
            ->suffixAction(
                Action::make('refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->action(fn(Set $set) => $set('code', Str::upper(Str::random(6))))
            );
    }

    /**
     * 可使用次数
     * @return Component
     */
    protected static function getUsageLimitFormComponent(): Component
    {
        return TextInput::make('usage_limit')
            ->label(__('admin/coupon.form_fields.usage_limit.label'))
            ->placeholder(__('admin/coupon.form_fields.usage_limit.placeholder'))
            ->numeric()
            ->minLength(1)
            ->maxLength(999)
            ->default(1)
            ->required();
    }

    /**
     * 抵扣金额/折扣率
     * @return Component
     */
    protected static function getValueFormComponent(): Component
    {
        return Group::make()->schema(function (Get $get) {
            if ($get('type') === CouponType::Direct->value) {
                return [
                    TextInput::make('value')
                        ->label(__('admin/coupon.form_fields.direct_value.label'))
                        ->placeholder(__('admin/coupon.form_fields.direct_value.placeholder'))
                        ->helperText(__('admin/coupon.form_fields.direct_value.helper_text'))
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->required()
                ];
            }
            return [
                TextInput::make('value')
                    ->label(__('admin/coupon.form_fields.percent_value.label'))
                    ->placeholder(__('admin/coupon.form_fields.percent_value.placeholder'))
                    ->helperText(__('admin/coupon.form_fields.percent_value.helper_text'))
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(1)
                    ->required()
            ];
        });
    }

    /**
     * 到期时间
     * @return Component
     */
    protected static function getExpiredAtFormComponent(): Component
    {
        return DateTimePicker::make('expired_at')
            ->label(__('admin/coupon.form_fields.expired_at.label'))
            ->placeholder(__('admin/coupon.form_fields.expired_at.placeholder'))
            ->required();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
        ];
    }
}
