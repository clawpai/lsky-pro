<?php

namespace App\Filament\Resources;

use App\DriverType;
use App\Facades\PhotoHandleService;
use App\Filament\Resources\HandleDriverResource\Pages;
use App\Models\Driver;
use Filament\Forms\Components\Builder as FormBuilder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Intervention\Image\Encoders\AutoEncoder;

class HandleDriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 24;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation_groups.driver');
    }

    public static function getModelLabel(): string
    {
        return __('admin/handle_driver.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin/handle_driver.plural_model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', DriverType::Handle);
    }

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Tabs::make()->id('handle_driver-tabs')->persistTab()->schema([
                // 基础设置
                Tabs\Tab::make(__('admin/handle_driver.tabs.basic'))->id('basic')->schema([
                    self::getNameFormComponent(),
                    self::getIntroFormComponent(),
                    self::getOptionsIsSyncFormComponent(),
                ]),
                // 裁剪
                Tabs\Tab::make(__('admin/handle_driver.tabs.crop'))->id('crop')->schema([
                    self::getOperationFormBuilderComponent('options.operations.crop', self::getOperationBlockComponents('crop')),
                ]),
                // 尺寸
                Tabs\Tab::make(__('admin/handle_driver.tabs.resize'))->id('resize')->schema([
                    self::getOperationFormBuilderComponent('options.operations.resize', self::getOperationBlockComponents('resize')),
                ]),
                // 滤镜
                Tabs\Tab::make(__('admin/handle_driver.tabs.filter'))->id('filter')->schema([
                    self::getOperationFormBuilderComponent('options.operations.filter', self::getOperationBlockComponents('filter'))
                        ->blockPickerWidth(MaxWidth::ExtraSmall->value),
                ]),
                // 旋转
                Tabs\Tab::make(__('admin/handle_driver.tabs.rotate'))->id('rotate')->schema([
                    self::getOperationFormBuilderComponent('options.operations.rotate', self::getOperationBlockComponents('rotate'))
                        ->blockPickerWidth(MaxWidth::ExtraSmall->value),
                ]),
                // 文本
                Tabs\Tab::make(__('admin/handle_driver.tabs.text'))->id('text')->schema([
                    self::getOperationFormBuilderComponent('options.operations.text', self::getOperationBlockComponents('text'))
                        ->blockNumbers()
                        ->blockPickerWidth(MaxWidth::ExtraSmall->value),
                ]),
                // 水印
                Tabs\Tab::make(__('admin/handle_driver.tabs.watermark'))->id('watermark')->schema([
                    self::getOperationFormBuilderComponent('options.operations.watermark', self::getOperationBlockComponents('watermark'))
                        ->blockPickerWidth(MaxWidth::ExtraSmall->value),
                ]),
                // 输出
                Tabs\Tab::make(__('admin/handle_driver.tabs.output'))
                    ->id('output')
                    ->columns(2)
                    ->schema(self::getOutputFormComponents()),
            ]),
            // TODO 实现预览？
        ]);
    }

    /**
     * 名称
     * @return Component
     */
    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin/handle_driver.form_fields.name.label'))
            ->placeholder(__('admin/handle_driver.form_fields.name.placeholder'))
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
            ->label(__('admin/handle_driver.form_fields.intro.label'))
            ->placeholder(__('admin/handle_driver.form_fields.intro.placeholder'))
            ->maxLength(2000)
            ->dehydrateStateUsing(fn($state) => (string)$state);
    }

    /**
     * 是否同步处理
     * @return Component
     */
    protected static function getOptionsIsSyncFormComponent(): Component
    {
        return Toggle::make('options.is_sync')
            ->label(__('admin/handle_driver.form_fields.options_is_sync.label'))
            ->helperText(__('admin/handle_driver.form_fields.options_is_sync.helper_text'))
            ->default(false)
            ->live();
    }

    /**
     * 获取图片处理表单
     *
     * @param string $name
     * @param array $blocks
     * @return FormBuilder
     */
    protected static function getOperationFormBuilderComponent(string $name, array $blocks): FormBuilder
    {
        return FormBuilder::make($name)
            ->label('')
            ->blocks($blocks)
            ->blockPickerColumns(1)
            ->blockNumbers(false)
            ->blockPickerWidth('lg')
            ->blockIcons()
            ->collapsible()
            ->collapsed()
            ->addActionLabel(__('admin/handle_driver.form_fields.operations.add_action_label'))
            ->reorderableWithButtons();
    }

    /**
     * 获取处理表单
     *
     * @return FormBuilder\Block[]
     */
    protected static function getOperationBlockComponents(string $type): array
    {
        return match ($type) {
            'crop' => [
                self::getCoverOperationBlockFormComponent(),
                self::getCoverDownOperationBlockFormComponent(),
                self::getCropDownOperationBlockFormComponent(),
                self::getTrimOperationBlockFormComponent(),
            ],
            'resize' => [
                self::getResizeOperationBlockFormComponent(),
                self::getResizeDownOperationBlockFormComponent(),
                self::getScaleOperationBlockFormComponent(),
                self::getScaleDownOperationBlockFormComponent(),
                self::getPadOperationBlockFormComponent(),
                self::getContainOperationBlockFormComponent(),
                self::getResizeCanvasOperationBlockFormComponent(),
                self::getResizeCanvasRelativeOperationBlockFormComponent(),
            ],
            'filter' => [
                self::getBrightnessOperationBlockFormComponent(),
                self::getContrastOperationBlockFormComponent(),
                self::getGammaOperationBlockFormComponent(),
                self::getColorizeOperationBlockFormComponent(),
                self::getGreyscaleOperationBlockFormComponent(),
                self::getFlopOperationBlockFormComponent(),
                self::getFlipOperationBlockFormComponent(),
                self::getBlurOperationBlockFormComponent(),
                self::getSharpenOperationBlockFormComponent(),
                self::getInvertOperationBlockFormComponent(),
                self::getPixelateOperationBlockFormComponent(),
            ],
            'rotate' => [
                self::getRotateOperationBlockFormComponent(),
            ],
            'text' => [
                self::getTextOperationBlockFormComponent(),
            ],
            'watermark' => [
                self::getPlaceOperationBlockFormComponent(),
            ],
            default => [],
        };
    }

    /**
     * [裁剪]裁剪并缩放
     * @return FormBuilder\Block
     */
    protected static function getCoverOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('cover')
            ->label(__('admin/handle_driver.operations.cover.label'))
            ->columns(3)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.cover.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.cover.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.cover.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.cover.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.cover.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false)
            ]);
    }

    /**
     * [裁剪]裁剪并缩放（不超出原尺寸）
     * @return FormBuilder\Block
     */
    protected static function getCoverDownOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('coverDown')
            ->label(__('admin/handle_driver.operations.cover_down.label'))
            ->columns(3)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.cover_down.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.cover_down.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.cover_down.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.cover_down.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.cover_down.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false)
            ]);
    }

    /**
     * [裁剪]裁剪图片
     * @return FormBuilder\Block
     */
    protected static function getCropDownOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('crop')
            ->label(__('admin/handle_driver.operations.crop.label'))
            ->columns(3)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.crop.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.crop.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.crop.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.crop.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.crop.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('top-left')
                    ->required()
                    ->native(false),
                TextInput::make('offset_x')
                    ->label(__('admin/handle_driver.operations.crop.fields.offset_x.label'))
                    ->placeholder(__('admin/handle_driver.operations.crop.fields.offset_x.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                TextInput::make('offset_y')
                    ->label(__('admin/handle_driver.operations.crop.fields.offset_y.label'))
                    ->placeholder(__('admin/handle_driver.operations.crop.fields.offset_y.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.crop.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.crop.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required()
            ]);
    }

    /**
     * [裁剪]修剪图像
     * @return FormBuilder\Block
     */
    protected static function getTrimOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('trim')
            ->label(__('admin/handle_driver.operations.trim.label'))
            ->columns(1)
            ->schema([
                TextInput::make('tolerance')
                    ->label(__('admin/handle_driver.operations.trim.fields.tolerance.label'))
                    ->placeholder(__('admin/handle_driver.operations.trim.fields.tolerance.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [尺寸]调整为指定尺寸
     * @return FormBuilder\Block
     */
    protected static function getResizeOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('resize')
            ->label(__('admin/handle_driver.operations.resize.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.resize.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize.fields.width.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.resize.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize.fields.height.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
            ]);
    }

    /**
     * [尺寸]调整为指定尺寸（不超出原尺寸）
     * @return FormBuilder\Block
     */
    protected static function getResizeDownOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('resizeDown')
            ->label(__('admin/handle_driver.operations.resize_down.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.resize.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize.fields.width.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.resize.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize.fields.height.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
            ]);
    }

    /**
     * [尺寸]按比例缩放图片
     * @return FormBuilder\Block
     */
    protected static function getScaleOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('scale')
            ->label(__('admin/handle_driver.operations.scale.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.scale.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.scale.fields.width.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.scale.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.scale.fields.height.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
            ]);
    }

    /**
     * [尺寸]按比例缩放图片（不超出原尺寸）
     * @return FormBuilder\Block
     */
    protected static function getScaleDownOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('scaleDown')
            ->label(__('admin/handle_driver.operations.scale_down.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.scale_down.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.scale_down.fields.width.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.scale_down.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.scale_down.fields.height.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
            ]);
    }

    /**
     * [尺寸]填充缩放
     * @return FormBuilder\Block
     */
    protected static function getPadOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('pad')
            ->label(__('admin/handle_driver.operations.pad.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.pad.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.pad.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.pad.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.pad.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.pad.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.pad.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required(),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.pad.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false),
            ]);
    }

    /**
     * [尺寸]填充缩放
     * @return FormBuilder\Block
     */
    protected static function getContainOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('contain')
            ->label(__('admin/handle_driver.operations.contain.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.contain.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.contain.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.contain.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.contain.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) =>  (int)$state),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.contain.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.contain.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required(),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.contain.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false),
            ]);
    }

    /**
     * [尺寸]调整图片画布
     * @return FormBuilder\Block
     */
    protected static function getResizeCanvasOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('resizeCanvas')
            ->label(__('admin/handle_driver.operations.resize_canvas.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.resize_canvas.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas.fields.width.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.resize_canvas.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas.fields.height.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.resize_canvas.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required(),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.resize_canvas.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false),
            ]);
    }

    /**
     * [尺寸]相对调整画布
     * @return FormBuilder\Block
     */
    protected static function getResizeCanvasRelativeOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('resizeCanvasRelative')
            ->label(__('admin/handle_driver.operations.resize_canvas_relative.label'))
            ->columns(2)
            ->schema([
                TextInput::make('width')
                    ->label(__('admin/handle_driver.operations.resize_canvas_relative.fields.width.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas_relative.fields.width.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                TextInput::make('height')
                    ->label(__('admin/handle_driver.operations.resize_canvas_relative.fields.height.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas_relative.fields.height.placeholder'))
                    ->integer()
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.resize_canvas_relative.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.resize_canvas_relative.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required(),
                Select::make('position')
                    ->label(__('admin/handle_driver.operations.resize_canvas_relative.fields.position.label'))
                    ->options(PhotoHandleService::getPositions())
                    ->default('center')
                    ->required()
                    ->native(false),
            ]);
    }

    /**
     * [滤镜]改变亮度
     * @return FormBuilder\Block
     */
    protected static function getBrightnessOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('brightness')
            ->label(__('admin/handle_driver.operations.brightness.label'))
            ->columns(1)
            ->schema([
                TextInput::make('level')
                    ->label(__('admin/handle_driver.operations.brightness.fields.level.label'))
                    ->placeholder(__('admin/handle_driver.operations.brightness.fields.level.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]改变对比度
     * @return FormBuilder\Block
     */
    protected static function getContrastOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('contrast')
            ->label(__('admin/handle_driver.operations.contrast.label'))
            ->columns(1)
            ->schema([
                TextInput::make('level')
                    ->label(__('admin/handle_driver.operations.contrast.fields.level.label'))
                    ->placeholder(__('admin/handle_driver.operations.contrast.fields.level.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]伽马校正
     * @return FormBuilder\Block
     */
    protected static function getGammaOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('gamma')
            ->label(__('admin/handle_driver.operations.gamma.label'))
            ->columns(1)
            ->schema([
                TextInput::make('gamma')
                    ->label(__('admin/handle_driver.operations.gamma.fields.gamma.label'))
                    ->placeholder(__('admin/handle_driver.operations.gamma.fields.gamma.placeholder'))
                    ->integer()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]颜色校正
     * @return FormBuilder\Block
     */
    protected static function getColorizeOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('colorize')
            ->label(__('admin/handle_driver.operations.colorize.label'))
            ->columns(3)
            ->schema([
                TextInput::make('red')
                    ->label(__('admin/handle_driver.operations.colorize.fields.red.label'))
                    ->placeholder(__('admin/handle_driver.operations.colorize.fields.red.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                TextInput::make('green')
                    ->label(__('admin/handle_driver.operations.colorize.fields.green.label'))
                    ->placeholder(__('admin/handle_driver.operations.colorize.fields.green.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                TextInput::make('blue')
                    ->label(__('admin/handle_driver.operations.colorize.fields.blue.label'))
                    ->placeholder(__('admin/handle_driver.operations.colorize.fields.blue.placeholder'))
                    ->integer()
                    ->default(0)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]转换为灰度版本
     * @return FormBuilder\Block
     */
    protected static function getGreyscaleOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('greyscale')
            ->label(__('admin/handle_driver.operations.greyscale.label'));
    }

    /**
     * [滤镜]水平镜像
     * @return FormBuilder\Block
     */
    protected static function getFlopOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('flop')
            ->label(__('admin/handle_driver.operations.flop.label'));
    }

    /**
     * [滤镜]垂直镜像
     * @return FormBuilder\Block
     */
    protected static function getFlipOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('flip')
            ->label(__('admin/handle_driver.operations.flip.label'));
    }

    /**
     * [滤镜]模糊效果
     * @return FormBuilder\Block
     */
    protected static function getBlurOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('blur')
            ->label(__('admin/handle_driver.operations.blur.label'))
            ->columns(1)
            ->schema([
                TextInput::make('amount')
                    ->label(__('admin/handle_driver.operations.blur.fields.amount.label'))
                    ->placeholder(__('admin/handle_driver.operations.blur.fields.amount.placeholder'))
                    ->integer()
                    ->default(5)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]锐化效果
     * @return FormBuilder\Block
     */
    protected static function getSharpenOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('sharpen')
            ->label(__('admin/handle_driver.operations.sharpen.label'))
            ->columns(1)
            ->schema([
                TextInput::make('amount')
                    ->label(__('admin/handle_driver.operations.sharpen.fields.amount.label'))
                    ->placeholder(__('admin/handle_driver.operations.sharpen.fields.amount.placeholder'))
                    ->integer()
                    ->default(10)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]像素化效果
     * @return FormBuilder\Block
     */
    protected static function getPixelateOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('pixelate')
            ->label(__('admin/handle_driver.operations.pixelate.label'))
            ->columns(1)
            ->schema([
                TextInput::make('size')
                    ->label(__('admin/handle_driver.operations.pixelate.fields.size.label'))
                    ->placeholder(__('admin/handle_driver.operations.pixelate.fields.size.placeholder'))
                    ->integer()
                    ->default(12)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
            ]);
    }

    /**
     * [滤镜]反转颜色
     * @return FormBuilder\Block
     */
    protected static function getInvertOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('invert')
            ->label(__('admin/handle_driver.operations.invert.label'));
    }

    /**
     * [旋转]图像旋转
     * @return FormBuilder\Block
     */
    protected static function getRotateOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('rotate')
            ->label(__('admin/handle_driver.operations.rotate.label'))
            ->columns(2)
            ->schema([
                TextInput::make('angle')
                    ->label(__('admin/handle_driver.operations.rotate.fields.angle.label'))
                    ->placeholder(__('admin/handle_driver.operations.rotate.fields.angle.placeholder'))
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (float)$state),
                ColorPicker::make('background')
                    ->label(__('admin/handle_driver.operations.rotate.fields.background.label'))
                    ->placeholder(__('admin/handle_driver.operations.rotate.fields.background.placeholder'))
                    ->default('#ffffff')
                    ->rgba()
                    ->required(),
            ]);
    }

    /**
     * [文本]文本配置
     * @return FormBuilder\Block
     */
    protected static function getTextOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('text')
            ->label(__('admin/handle_driver.operations.text.label'))
            ->schema([
                Textarea::make('text')
                    ->label(__('admin/handle_driver.operations.text.fields.text.label'))
                    ->placeholder(__('admin/handle_driver.operations.text.fields.text.placeholder'))
                    ->required()
                    ->mutateDehydratedStateUsing(fn($state) => (string)$state),
                Grid::make()->columns(3)->schema([
                    Select::make('position')
                        ->label(__('admin/handle_driver.operations.text.fields.position.label'))
                        ->options(PhotoHandleService::getPositions())
                        ->default('top-left')
                        ->required()
                        ->native(false)
                        ->disabled(fn (Get $get): bool => $get('is_tiled')),
                    TextInput::make('offset_x')
                        ->label(__('admin/handle_driver.operations.text.fields.offset_x.label'))
                        ->placeholder(__('admin/handle_driver.operations.text.fields.offset_x.placeholder'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                    TextInput::make('offset_y')
                        ->label(__('admin/handle_driver.operations.text.fields.offset_y.label'))
                        ->placeholder(__('admin/handle_driver.operations.text.fields.offset_y.placeholder'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                ]),
                TextInput::make('ratio')
                    ->label(__('admin/handle_driver.operations.text.fields.ratio.label'))
                    ->placeholder(__('admin/handle_driver.operations.text.fields.ratio.placeholder'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(10)
                    ->required()
                    ->suffix('%')
                    ->helperText(__('admin/handle_driver.operations.text.fields.ratio.helper_text'))
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                Toggle::make('is_tiled')
                    ->label(__('admin/handle_driver.operations.text.fields.is_tiled.label'))
                    ->helperText(__('admin/handle_driver.operations.text.fields.is_tiled.helper_text'))
                    ->default(false)
                    ->live(),
                Fieldset::make()->label(__('admin/handle_driver.operations.text.fields.font.label'))
                    ->columns(3)
                    ->schema([
                        FileUpload::make('font.filename.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.filename.fields.value.label'))
                            ->previewable(false)
                            ->disk('local')
                            ->moveFiles()
                            ->orientImagesFromExif(false)
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('font.size.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.size.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.size.fields.value.placeholder'))
                            ->numeric()
                            ->step(0.01)
                            ->default(12)
                            ->required()
                            ->mutateDehydratedStateUsing(fn($state) => (float)$state),
                        ColorPicker::make('font.color.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.color.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.color.fields.value.placeholder'))
                            ->default('#000000')
                            ->rgba()
                            ->required(),
                        Select::make('font.align.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.align.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.align.fields.value.placeholder'))
                            ->options(['center' => '中间', 'left' => '左边', 'right' => '右边'])
                            ->default('left')
                            ->required()
                            ->native(false),
                        Select::make('font.valign.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.valign.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.valign.fields.value.placeholder'))
                            ->options(['center' => '中间', 'top' => '上边', 'bottom' => '下边'])
                            ->default('bottom')
                            ->required()
                            ->native(false),
                        TextInput::make('font.lineHeight.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.line_height.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.line_height.fields.value.placeholder'))
                            ->numeric()
                            ->step(0.01)
                            ->default(1.25)
                            ->required()
                            ->mutateDehydratedStateUsing(fn($state) => (float)$state),
                        TextInput::make('font.wrap.width')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.wrap.fields.width.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.wrap.fields.width.placeholder'))
                            ->integer()
                            ->mutateDehydratedStateUsing(fn($state) => $state ? (int)$state : null),
                        TextInput::make('font.angle.value')
                            ->label(__('admin/handle_driver.operations.text.fields.font.fields.angle.fields.value.label'))
                            ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.angle.fields.value.placeholder'))
                            ->numeric()
                            ->step(0.01)
                            ->default(0)
                            ->required()
                            ->columnSpanFull()
                            ->mutateDehydratedStateUsing(fn($state) => (float)$state),
                        Fieldset::make()->label(__('admin/handle_driver.operations.text.fields.font.fields.stroke.label'))->schema([
                            ColorPicker::make('font.stroke.color')
                                ->label(__('admin/handle_driver.operations.text.fields.font.fields.stroke.fields.color.label'))
                                ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.stroke.fields.color.placeholder'))
                                ->default('#ffffff')
                                ->rgba()
                                ->required(),
                            TextInput::make('font.stroke.width')
                                ->label(__('admin/handle_driver.operations.text.fields.font.fields.stroke.fields.width.label'))
                                ->placeholder(__('admin/handle_driver.operations.text.fields.font.fields.stroke.fields.width.placeholder'))
                                ->integer()
                                ->default(1)
                                ->required()
                                ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                        ]),
                    ])
            ]);
    }

    /**
     * [水印]水印设置
     * @return FormBuilder\Block
     */
    protected static function getPlaceOperationBlockFormComponent(): FormBuilder\Block
    {
        return FormBuilder\Block::make('place')
            ->label(__('admin/handle_driver.operations.watermark.label'))
            ->schema([
                FileUpload::make('element')
                    ->label(__('admin/handle_driver.operations.watermark.fields.element.label'))
                    ->disk('local')
                    ->moveFiles()
                    ->image()
                    ->imageEditor()
                    ->previewable(false)
                    ->orientImagesFromExif(false)
                    ->required()
                    ->columnSpanFull(),
                Grid::make()->schema([
                    Select::make('position')
                        ->label(__('admin/handle_driver.operations.watermark.fields.position.label'))
                        ->options(PhotoHandleService::getPositions())
                        ->default('top-left')
                        ->native(false)
                        ->disabled(fn (Get $get): bool => $get('is_tiled')),
                    TextInput::make('opacity')
                        ->label(__('admin/handle_driver.operations.watermark.fields.opacity.label'))
                        ->placeholder(__('admin/handle_driver.operations.watermark.fields.opacity.placeholder'))
                        ->integer()
                        ->default(100)
                        ->required()
                        ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                    TextInput::make('offset_x')
                        ->label(__('admin/handle_driver.operations.watermark.fields.offset_x.label'))
                        ->placeholder(__('admin/handle_driver.operations.watermark.fields.offset_x.placeholder'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                    TextInput::make('offset_y')
                        ->label(__('admin/handle_driver.operations.watermark.fields.offset_y.label'))
                        ->placeholder(__('admin/handle_driver.operations.watermark.fields.offset_y.placeholder'))
                        ->integer()
                        ->default(0)
                        ->required()
                        ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                ]),
                TextInput::make('ratio')
                    ->label(__('admin/handle_driver.operations.watermark.fields.ratio.label'))
                    ->placeholder(__('admin/handle_driver.operations.watermark.fields.ratio.placeholder'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(10)
                    ->required()
                    ->suffix('%')
                    ->helperText(__('admin/handle_driver.operations.watermark.fields.ratio.helper_text'))
                    ->mutateDehydratedStateUsing(fn($state) => (int)$state),
                Toggle::make('is_tiled')
                    ->label(__('admin/handle_driver.operations.watermark.fields.is_tiled.label'))
                    ->helperText(__('admin/handle_driver.operations.watermark.fields.is_tiled.helper_text'))
                    ->default(false)
                    ->live(),
            ]);
    }

    /**
     * [输出]输出设置表单
     * @return array
     */
    protected static function getOutputFormComponents(): array
    {
        return [
            Select::make('options.output.encoder')
                ->label(__('admin/handle_driver.form_fields.options_output.fields.encoder.label'))
                ->placeholder(__('admin/handle_driver.form_fields.options_output.fields.encoder.placeholder'))
                ->helperText(__('admin/handle_driver.form_fields.options_output.fields.encoder.helper_text'))
                ->options(PhotoHandleService::getEncoders())
                ->default(AutoEncoder::class)
                ->native(false)
                ->required()
                ->disabled(function (Get $get, Set $set): bool {
                    $isSync = (bool)$get('options.is_sync');
                    if (! $isSync) {
                        $set('options.output.encoder', AutoEncoder::class);
                    }
                    return ! $isSync;
                }),
            TextInput::make('options.output.quality')
                ->label(__('admin/handle_driver.form_fields.options_output.fields.quality.label'))
                ->placeholder(__('admin/handle_driver.form_fields.options_output.fields.quality.placeholder'))
                ->helperText(__('admin/handle_driver.form_fields.options_output.fields.quality.helper_text'))
                ->integer()
                ->default(75)
                ->required()
                ->mutateDehydratedStateUsing(fn($state) => (int)$state),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHandleDrivers::route('/'),
            'create' => Pages\CreateHandleDriver::route('/create'),
            'edit' => Pages\EditHandleDriver::route('/{record}/edit'),
        ];
    }
}
