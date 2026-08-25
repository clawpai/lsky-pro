<?php

namespace App\Filament\Resources;

use App\Facades\AppService;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Group;
use App\Models\User;
use Filament\Forms\Components\Component as FormComponent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Propaganistas\LaravelPhone\Rules\Phone;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.user');
    }

    public static function getModelLabel(): string
    {
        return __('admin/user.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/user.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = fn($builder) => $builder->where('expired_at', '>', now())->orWhereNull('expired_at');
        return parent::getEloquentQuery()
            ->with(['group' => function ($builder) {
                $builder->with('group')->where('expired_at', '>', now());
            }])
            ->withCount([
                'groups' => $query,
                'capacities' => $query,
                'shares',
                'photos',
                'orders',
            ])
            ->withSum(['capacities' => $query], 'capacity');
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Section::make()->schema([
                self::getAvatarFormComponent(),
                Grid::make()->schema([
                    self::getGroupFormComponent(),
                    self::getCapacityFormComponent(),
                    self::getUsernameFormComponent(),
                    self::getNameFormComponent(),
                    self::getEmailFormComponent(),
                    self::getCountryCodeFormComponent(),
                    self::getPhoneFormComponent(),
                    self::getPasswordFormComponent(),
                ]),
            ]),
        ]);
    }

    /**
     * 头像
     * @return FormComponent
     */
    protected static function getAvatarFormComponent(): FormComponent
    {
        return FileUpload::make('avatar')
            ->label(__('admin/user.form_fields.avatar.label'))
            ->placeholder(__('admin/user.form_fields.avatar.placeholder'))
            ->avatar()
            ->mutateDehydratedStateUsing(fn(?string $state) => (string)$state);
    }

    /**
     * 角色组
     * @return FormComponent
     */
    protected static function getGroupFormComponent(): FormComponent
    {
        return Select::make('group_id')
            ->label(__('admin/user.form_fields.group_id.label'))
            ->placeholder(__('admin/user.form_fields.group_id.placeholder'))
            ->options(Group::all()->pluck('name', 'id'))
            ->searchable()
            ->required();
    }

    /**
     * 初始容量
     * @return FormComponent
     */
    protected static function getCapacityFormComponent(): FormComponent
    {
        return TextInput::make('capacity')
            ->label(__('admin/user.form_fields.capacity.label'))
            ->placeholder(__('admin/user.form_fields.capacity.placeholder'))
            ->minValue(0)
            ->default(1048576)
            ->step(0.01)
            ->required();
    }

    /**
     * 用户名
     * @return FormComponent
     */
    protected static function getUsernameFormComponent(): FormComponent
    {
        return TextInput::make('username')
            ->label(__('admin/user.form_fields.username.label'))
            ->placeholder(__('admin/user.form_fields.username.placeholder'))
            ->maxLength(20)
            ->unique(ignoreRecord: true)
            ->required();
    }

    /**
     * 名称
     * @return FormComponent
     */
    protected static function getNameFormComponent(): FormComponent
    {
        return TextInput::make('name')
            ->label(__('admin/user.form_fields.name.label'))
            ->placeholder(__('admin/user.form_fields.name.placeholder'))
            ->maxLength(80)
            ->required();
    }

    /**
     * 邮箱
     * @return FormComponent
     */
    protected static function getEmailFormComponent(): FormComponent
    {
        return TextInput::make('email')
            ->label(__('admin/user.form_fields.email.label'))
            ->placeholder(__('admin/user.form_fields.email.placeholder'))
            ->email()
            ->unique(ignoreRecord: true)
            ->required();
    }

    /**
     * 国家
     * @return FormComponent
     */
    protected static function getCountryCodeFormComponent(): FormComponent
    {
        return Select::make('country_code')
            ->label(__('admin/user.form_fields.country_code.label'))
            ->placeholder(__('admin/user.form_fields.country_code.placeholder'))
            ->options(collect(AppService::getAllCountryCodes())->map(function (string $name, string $code) {
                return ['code' => $code, 'name' => $name];
            })->pluck('name', 'code')->toArray())
            ->default('cn')
            ->searchable()
            ->native(false)
            ->required(fn(Get $get): bool => (bool)$get('phone'));
    }

    /**
     * 手机号
     * @return FormComponent
     */
    protected static function getPhoneFormComponent(): FormComponent
    {
        return TextInput::make('phone')
            ->label(__('admin/user.form_fields.phone.label'))
            ->placeholder(__('admin/user.form_fields.phone.placeholder'))
            ->rule(function (Get $get) {
                if ($get('country_code')) {
                    return (new Phone())->country($get('country_code'));
                }
                return null;
            })
            ->live()
            ->unique(ignoreRecord: true);
    }

    /**
     * 密码
     * @return FormComponent
     */
    protected static function getPasswordFormComponent(): FormComponent
    {
        return TextInput::make('password')
            ->label(__('admin/user.form_fields.password.label'))
            ->placeholder(function (string $operation) {
                if ($operation === 'create') {
                    return __('admin/user.form_fields.password.placeholder');
                }

                return __('admin/user.form_fields.password.placeholder_for_edit');
            })
            ->password()
            ->revealable()
            ->required(fn (string $operation): bool => $operation === 'create');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}
