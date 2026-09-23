<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Professionals\ProfessionalResource;
use App\Models\Article;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Chiffres clés du tableau de bord.
 */
class SiteOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pendingProfessionals = User::query()
            ->where('is_health_professional', true)
            ->whereNull('email_verified_at')
            ->count();

        return [
            Stat::make('Articles publiés', Article::query()->visible()->count())
                ->description(Article::query()->draft()->count().' brouillon(s), '.Article::query()->scheduled()->count().' programmé(s)')
                ->url(ArticleResource::getUrl('index')),
            Stat::make('Événements à venir', Event::query()->where('is_published', true)->whereDate('event_date', '>=', today())->count())
                ->url(EventResource::getUrl('index')),
            Stat::make('Inscriptions (30 jours)', EventRegistration::query()->where('created_at', '>=', now()->subDays(30))->count())
                ->url(EventRegistrationResource::getUrl('index')),
            Stat::make('Professionnels non vérifiés', $pendingProfessionals)
                ->color($pendingProfessionals > 0 ? 'warning' : 'success')
                ->url(ProfessionalResource::getUrl('index')),
        ];
    }
}
