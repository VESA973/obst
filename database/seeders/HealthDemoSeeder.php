<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Database\Seeders\Concerns\DownloadsDemoImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Rubriques et articles de démonstration pour la section « Santé de la femme ».
 * À ne jamais lancer en production :
 *   php artisan db:seed --class=HealthDemoSeeder
 */
class HealthDemoSeeder extends Seeder
{
    use DownloadsDemoImages;

    /** Rubrique => [description, titres d'articles]. Les slugs correspondent à config/sante.php. */
    private const RUBRIQUES = [
        'Grossesse' => ['Suivi, examens, alimentation et signes d\'alerte, trimestre après trimestre.', [
            'Les examens du premier trimestre expliqués simplement',
            'Nausées de grossesse : ce qui aide vraiment',
            'Préparer l\'accouchement sereinement',
            'Signes d\'alerte : quand consulter sans attendre',
        ]],
        'Cycle menstruel' => ['Comprendre son cycle, ses variations et les douleurs qui doivent alerter.', [
            'Les quatre phases du cycle en un coup d\'œil',
            'Règles douloureuses : normal ou pas ?',
            'Cycle irrégulier : les causes les plus fréquentes',
            'Syndrome prémenstruel : des solutions au quotidien',
        ]],
        'Ménopause' => ['Bouffées de chaleur, sommeil, santé osseuse : des repères pour bien vivre cette étape.', [
            'Périménopause : reconnaître les premiers signes',
            'Bouffées de chaleur : les pistes validées',
            'Ménopause et santé des os',
            'Mieux dormir pendant la ménopause',
        ]],
        'Prévention santé' => ['Dépistages, vaccination et bons réflexes pour prendre soin de soi.', [
            'Dépistage du cancer du col : le calendrier',
            'Autopalpation des seins : le geste en 5 étapes',
            'Vaccination HPV : les questions fréquentes',
            'Bilan de santé : à quel âge, pour quoi faire ?',
        ]],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('HealthDemoSeeder ne doit pas être lancé en production.');
        }

        $author = User::query()->where('is_admin', true)->first();
        $index = 0;

        foreach (self::RUBRIQUES as $name => [$description, $titles]) {
            // Recherche limitée à la section : une catégorie des Actualités n'est jamais réutilisée.
            $rubrique = ArticleCategory::query()->where('section', 'public')->where('slug', Str::slug($name))->first();

            if (! $rubrique && ArticleCategory::query()->where('name', $name)->orWhere('slug', Str::slug($name))->exists()) {
                // Nom ou slug déjà pris par une autre rubrique du site : on n'y touche pas.
                $this->command?->warn("Rubrique « {$name} » ignorée : ce nom existe déjà ailleurs sur le site.");

                continue;
            }

            $rubrique ??= ArticleCategory::query()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'section' => 'public',
                'title' => $name,
                'description' => $description,
                'is_active' => true,
            ]);

            foreach ($titles as $title) {
                $slug = Str::slug($title);
                $index++;

                if (Article::query()->where('slug', $slug)->exists()) {
                    continue;
                }

                $this->command?->info("Article {$index} : {$title}");

                Article::query()->create([
                    'title' => $title,
                    'slug' => $slug,
                    'article_category_id' => $rubrique->id,
                    'user_id' => $author?->id,
                    'excerpt' => fake('fr_FR')->realText(160),
                    'body' => $this->body(),
                    // Un article sur cinq sans photo, pour voir le visuel de remplacement.
                    'image_path' => $index % 5 === 0 ? null : $this->downloadDemoImage('sante-'.$slug, 'sante-demo'),
                    'status' => ArticleStatus::Published,
                    'published_at' => now()->subHours($index * 31 + 3),
                ]);
            }
        }
    }

    /**
     * Contenu structuré (H2 / H3) pour tester le sommaire automatique.
     */
    private function body(): string
    {
        $faker = fake('fr_FR');
        $html = '';

        foreach (['Ce qu\'il faut savoir', 'Les symptômes à surveiller', 'Nos conseils au quotidien', 'Quand consulter ?'] as $position => $heading) {
            $html .= "<h2>{$heading}</h2><p>".$faker->realText(450).'</p>';

            if ($position === 1) {
                $html .= '<h3>Les signes les plus fréquents</h3><ul><li>'.$faker->sentence().'</li><li>'.$faker->sentence().'</li><li>'.$faker->sentence().'</li></ul>';
                $html .= '<h3>Les signes plus rares</h3><p>'.$faker->realText(300).'</p>';
            }

            if ($position === 2) {
                $html .= '<blockquote><p>'.$faker->realText(140).'</p></blockquote>';
            }
        }

        return $html;
    }
}
