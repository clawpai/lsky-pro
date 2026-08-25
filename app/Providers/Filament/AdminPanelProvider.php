<?php

namespace App\Providers\Filament;

use App\Facades\AppService;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\License;
use App\Http\Middleware\Initialize;
use App\Settings\AdminSettings;
use Filament\Enums\ThemeMode;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(AppService::isAgreeAgreement() ? Login::class : License::class)
            ->authGuard('admin')
            ->colors(function () {
                $adminSettings = app(AdminSettings::class);
                return [
                    'primary' => data_get(Color::all(), $adminSettings->primary_color ?: 'sky', Color::Sky),
                ];
            })
            ->sidebarWidth("15rem")
            ->maxContentWidth(MaxWidth::Full)
            ->profile(isSimple: false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([Initialize::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ])->bootUsing(function (Panel $panel) {
                $adminSettings = app(AdminSettings::class);

                $panel->topNavigation((bool)$adminSettings->top_navigation)
                    ->darkMode((bool)$adminSettings->dark_mode)
                    ->defaultThemeMode(ThemeMode::tryFrom($adminSettings->default_theme_mode ?: ThemeMode::System->value));
            })
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn (): View => \view('filament.menus.home'))
            ->databaseTransactions()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->assets([
                Css::make('github-markdown', __DIR__ . '/../../../resources/css/github-markdown.css')
            ]);
    }

    public function boot(): void
    {
        // 后台取消防止不可填充属性被静默丢弃的限制，否则在创建或编辑数据时，由于关联属性值的存在会导致更新失败
        Model::preventSilentlyDiscardingAttributes(false);

        // 未同意协议，注销登录
        if (! AppService::isAgreeAgreement()) {
            Auth::guard('admin')->logout();
        }

        Column::configureUsing(function (Column $column): void {
            $column->toggleable();
        });

        Table::configureUsing(function (Table $table): void {
            $table::$defaultCurrency = 'cny';
            $table::$defaultDateDisplayFormat = 'Y/m/d';
            $table::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';

            $table->paginationPageOptions([10, 25, 50, 100, 200, 500, 1000])
                ->selectCurrentPageOnly()
                ->defaultSort('id', 'desc');
        });

        TextEntry::configureUsing(function (TextEntry $entry): void {
            $entry->extraAttributes(['class' => 'overflow-hidden break-all']);
        });

        Infolist::configureUsing(function (Infolist $infolist): void {
            $infolist::$defaultCurrency = 'cny';
            $infolist::$defaultDateDisplayFormat = 'Y/m/d';
            $infolist::$defaultDateTimeDisplayFormat = 'Y/m/d H:i:s';
        });

        DateTimepicker::configureUsing(function (DateTimepicker $dateTimepicker): void {
            $dateTimepicker::$defaultDateDisplayFormat = 'Y/m/d';
            $dateTimepicker::$defaultDateTimeDisplayFormat = 'Y/m/d H:i';
            $dateTimepicker::$defaultDateTimeWithSecondsDisplayFormat = 'Y/m/d H:i:s';
        });

        DatePicker::configureUsing(function (DatePicker $datePicker): void {
            $datePicker::$defaultDateDisplayFormat = 'Y/m/d';
            $datePicker::$defaultDateTimeDisplayFormat = 'Y/m/d H:i';
            $datePicker::$defaultDateTimeWithSecondsDisplayFormat = 'Y/m/d H:i:s';
        });
    }
}
