<?php

namespace App\Providers;

use App\Models\PageSetting;
use App\Support\EditorialSection;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('fr');
        Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        View::composer('layouts.site', function ($view): void {
            $view->with('siteMenuPages', PageSetting::menuPages());
        });

        // Section éditoriale en cours (Actualités ou Santé de la femme) et ses rubriques,
        // pour les vues partagées news.* (barre, menu mobile, pied de page, barre latérale).
        View::composer(['layouts.news', 'news.*'], function ($view): void {
            $editorial = EditorialSection::current();

            $view->with([
                'editorial' => $editorial,
                'newsCategories' => $editorial->categories(),
            ]);
        });
    }
}
