<?php

namespace Database\Seeders;

use App\Models\Event;
use Database\Seeders\Concerns\DownloadsDemoImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Événements d'exemple pour l'agenda, marqués « [Exemple] » dans le titre.
 *   php artisan db:seed --class=AgendaExampleSeeder
 * Idempotent : un événement dont le titre existe déjà n'est pas recréé ; rien n'est supprimé.
 * Pour les retirer : Filament > Agenda > Événements, rechercher « [Exemple] », tout sélectionner, supprimer.
 */
class AgendaExampleSeeder extends Seeder
{
    use DownloadsDemoImages;

    public const PREFIX = '[Exemple] ';

    public function run(): void
    {
        $today = Carbon::today('America/Cayenne');
        $created = 0;

        foreach ($this->events($today) as $index => $data) {
            $title = self::PREFIX.$data['title'];

            if (Event::query()->where('title', $title)->exists()) {
                continue;
            }

            $this->command?->info('Événement '.($index + 1).' : '.$data['title']);

            Event::query()->create([
                'title' => $title,
                'location' => $data['location'],
                'description' => implode("\n\n", $data['paragraphs']),
                'schedule_items' => $data['slots'],
                'event_date' => $data['slots'][0]['date'],
                'registration_capacity' => $data['capacity'] ?? null,
                'is_paid' => false,
                'is_published' => true,
                // Un événement sur quatre sans image, pour voir le visuel de remplacement.
                'image_path' => $index % 4 === 3 ? null : $this->downloadDemoImage('agenda-'.Str::slug($data['title']), 'events/flyers'),
            ]);

            $created++;
        }

        $this->command?->info("{$created} événement(s) d'exemple créé(s).");
    }

    /**
     * Créneau horaire au format du champ schedule_items.
     */
    private function slot(Carbon $date, string $start, string $end, ?string $label = null): array
    {
        return ['label' => $label, 'date' => $date->format('Y-m-d'), 'start_time' => $start, 'end_time' => $end];
    }

    /**
     * Prochain jour de la semaine donné après une date (1 = lundi … 6 = samedi).
     */
    private function next(Carbon $from, int $dayOfWeek): Carbon
    {
        return $from->copy()->next($dayOfWeek);
    }

    private function events(Carbon $today): array
    {
        // Prochain mois d'octobre (Octobre Rose), puis décembre et mars suivants.
        $year = $today->month > 10 ? $today->year + 1 : $today->year;
        $october = Carbon::create($year, 10, 1, 0, 0, 0, 'America/Cayenne');
        $december = Carbon::create($year, 12, 1, 0, 0, 0, 'America/Cayenne');
        $march = Carbon::create($year + 1, 3, 1, 0, 0, 0, 'America/Cayenne');

        return [
            [
                'title' => 'Octobre Rose : journée de sensibilisation au dépistage du cancer du sein',
                'location' => 'Place des Palmistes, Cayenne',
                'slots' => [$this->slot($this->next($october, Carbon::SATURDAY), '09:00', '16:00')],
                'paragraphs' => [
                    'À l\'occasion d\'Octobre Rose, l\'association vous accueille toute la journée pour parler du dépistage du cancer du sein, en toute simplicité et sans jugement.',
                    'Des professionnelles de santé et des bénévoles seront présentes pour répondre à vos questions, vous orienter vers les structures de dépistage et vous remettre de la documentation.',
                    'Entrée libre, sans inscription. Venez seule, entre amies ou en famille.',
                ],
            ],
            [
                'title' => 'Octobre Rose : marche solidaire',
                'location' => 'Front de mer, Kourou',
                'slots' => [$this->slot($this->next($october->copy()->addWeeks(2), Carbon::SUNDAY), '07:00', '10:00')],
                'capacity' => 150,
                'paragraphs' => [
                    'Marchons ensemble, en rose, pour rappeler l\'importance du dépistage et soutenir les femmes touchées par la maladie.',
                    'Le parcours, accessible à toutes et à tous, se fait à allure libre. Pensez à votre eau, à une casquette et à une tenue confortable.',
                    'L\'inscription est gratuite mais conseillée pour faciliter l\'organisation.',
                ],
            ],
            [
                'title' => 'Permanence santé de la femme',
                'location' => 'Maison de quartier, Saint-Laurent-du-Maroni',
                'slots' => [$this->slot($this->next($october->copy()->addWeeks(3), Carbon::WEDNESDAY), '08:30', '12:30')],
                'paragraphs' => [
                    'Une matinée d\'accueil et d\'échange autour de la santé des femmes : suivi gynécologique, contraception, grossesse ou ménopause.',
                    'Nos équipes vous écoutent, vous informent et vous orientent vers les professionnels et les structures adaptés à votre situation.',
                    'Accueil sans rendez-vous, dans le respect de la confidentialité.',
                ],
            ],
            [
                'title' => 'Atelier « Bien vivre son post-partum »',
                'location' => 'Salle municipale, Matoury',
                'slots' => [$this->slot($this->next($october->copy()->addMonth(), Carbon::SATURDAY), '09:30', '11:30')],
                'capacity' => 20,
                'paragraphs' => [
                    'Les semaines qui suivent une naissance sont riches en émotions et en questions. Cet atelier propose un temps d\'échange bienveillant entre jeunes parents.',
                    'Fatigue, allaitement, retour à la maison, vie de couple : une sage-femme anime les discussions et partage des repères concrets.',
                    'Les bébés sont les bienvenus. Nombre de places limité, inscription recommandée.',
                ],
            ],
            [
                'title' => 'Journée mondiale de lutte contre le sida : stand de prévention et de dépistage',
                'location' => 'Marché central, Cayenne',
                'slots' => [$this->slot($december, '08:00', '13:00')],
                'paragraphs' => [
                    'À l\'occasion du 1er décembre, retrouvez notre stand de prévention pour vous informer sur le VIH et les infections sexuellement transmissibles.',
                    'Documentation, préservatifs et orientation vers les lieux de dépistage : l\'équipe répond à toutes vos questions, de façon anonyme et sans jugement.',
                ],
            ],
            [
                'title' => 'Mission de prévention itinérante sur le fleuve',
                'location' => 'Maripasoula et Papaïchton',
                'slots' => [
                    $this->slot($this->next($december->copy()->addWeek(), Carbon::TUESDAY), '09:00', '16:00', 'Maripasoula'),
                    $this->slot($this->next($december->copy()->addWeek(), Carbon::TUESDAY)->addDay(), '09:00', '15:00', 'Papaïchton'),
                ],
                'paragraphs' => [
                    'Notre équipe remonte le Maroni pour aller à la rencontre des femmes et des familles des communes de l\'intérieur.',
                    'Au programme : temps d\'information sur la santé de la femme et la périnatalité, échanges avec les professionnels de santé locaux et remise de documentation.',
                    'Les horaires peuvent évoluer selon les conditions de navigation.',
                ],
            ],
            [
                'title' => 'Permanence santé et prévention',
                'location' => 'Salle polyvalente, Saint-Georges-de-l\'Oyapock',
                'slots' => [$this->slot($this->next($december->copy()->addMonth()->startOfMonth(), Carbon::THURSDAY)->addWeek(), '09:00', '13:00')],
                'paragraphs' => [
                    'Une permanence ouverte à toutes et à tous pour s\'informer sur la santé de la femme, la contraception et la prévention.',
                    'Accueil en français, avec des bénévoles pouvant échanger en portugais.',
                ],
            ],
            [
                'title' => 'Atelier contraception et santé sexuelle pour les jeunes',
                'location' => 'Maison des jeunes, Rémire-Montjoly',
                'slots' => [$this->slot($this->next($december->copy()->addMonths(2)->startOfMonth(), Carbon::WEDNESDAY), '14:00', '16:30')],
                'capacity' => 25,
                'paragraphs' => [
                    'Un atelier interactif pour les 15-25 ans : connaître les différentes méthodes de contraception, parler de consentement et savoir où trouver de l\'aide.',
                    'L\'animation se fait en petit groupe, dans un cadre respectueux et confidentiel, avec des jeux et des supports adaptés.',
                ],
            ],
            [
                'title' => 'Conférence-débat sur l\'endométriose',
                'location' => 'Amphithéâtre, Cayenne',
                'slots' => [$this->slot($this->next($march->copy()->addWeeks(2), Carbon::THURSDAY), '18:00', '20:30')],
                'capacity' => 120,
                'paragraphs' => [
                    'En mars, mois de sensibilisation à l\'endométriose, l\'association organise une soirée d\'échange autour de cette maladie encore trop méconnue.',
                    'Des professionnels de santé et des patientes partageront leurs regards, suivis d\'un temps de questions avec le public.',
                    'Entrée gratuite, inscription conseillée.',
                ],
            ],
            [
                'title' => 'Journée prévention dengue et gestes contre les moustiques',
                'location' => 'Place du bourg, Macouria',
                'slots' => [$this->slot($this->next($march->copy()->subMonth(), Carbon::SATURDAY), '08:30', '12:30')],
                'paragraphs' => [
                    'Une matinée pour apprendre, en famille, les gestes simples qui limitent la présence des moustiques autour de la maison.',
                    'Démonstrations, jeux pour les enfants et documentation sur la conduite à tenir en cas de fièvre, en lien avec les recommandations des autorités sanitaires.',
                ],
            ],
            // Événements passés, pour la section « Événements passés ».
            [
                'title' => 'Rencontre « Parlons ménopause »',
                'location' => 'Médiathèque, Cayenne',
                'slots' => [$this->slot($today->copy()->subMonths(2)->startOfMonth()->next(Carbon::THURSDAY), '18:00', '20:00')],
                'paragraphs' => [
                    'Une soirée d\'échange libre autour de la ménopause, de ses effets au quotidien et des ressources disponibles.',
                    'Merci aux participantes pour la richesse des échanges !',
                ],
            ],
            [
                'title' => 'Atelier préparation à la naissance',
                'location' => 'Salle municipale, Kourou',
                'slots' => [$this->slot($today->copy()->subMonth()->startOfMonth()->next(Carbon::SATURDAY), '09:00', '11:30')],
                'paragraphs' => [
                    'Un atelier collectif pour les futurs parents : repères pour les dernières semaines de grossesse et les premiers jours avec bébé.',
                ],
            ],
            [
                'title' => 'Permanence santé de la femme à Mana',
                'location' => 'Centre social, Mana',
                'slots' => [$this->slot($today->copy()->subWeeks(2)->startOfWeek()->addDays(2), '08:30', '12:00')],
                'paragraphs' => [
                    'Une matinée d\'accueil et d\'orientation autour de la santé des femmes, sans rendez-vous.',
                ],
            ],
        ];
    }
}
