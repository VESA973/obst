<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->brandName('La Quinzaine Obstétricale')
            ->favicon(asset('favicon.ico'))
            // Couleurs reprises du thème du site public.
            ->colors([
                'primary' => Color::hex('#3a00f5'),
                'danger' => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info' => Color::hex('#c62bc8'),
                'gray' => Color::Slate,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                // Sans icône de groupe : les rubriques ont des sous-menus, qui exigent une icône par élément.
                NavigationGroup::make('Gestion des articles'),
                NavigationGroup::make('Agenda')->icon(Heroicon::OutlinedCalendarDays),
                NavigationGroup::make('Contenus')->icon(Heroicon::OutlinedSquares2x2),
                NavigationGroup::make('Communauté')->icon(Heroicon::OutlinedUserGroup),
                NavigationGroup::make('Paramètres')->icon(Heroicon::OutlinedCog6Tooth)->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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
            // Rubriques repliables dans le menu (voir la vue).
            ->renderHook(PanelsRenderHook::BODY_END, fn () => view('filament.sidebar-rubriques'))
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
