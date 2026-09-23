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
 * Articles de démonstration pour tester le rendu de la rubrique Actualités.
 * Images téléchargées depuis picsum.photos. À ne jamais lancer en production :
 *   php artisan db:seed --class=NewsDemoSeeder
 */
class NewsDemoSeeder extends Seeder
{
    use DownloadsDemoImages;

    private const CATEGORIES = [
        'Tribunes' => 'Prises de position et regards d\'experts sur la santé des femmes en Guyane.',
        'Interviews' => 'Rencontres avec les professionnelles et professionnels de terrain.',
        'Prévention' => 'Repères concrets pour prendre soin de sa santé à chaque étape de la vie.',
        'Publications' => 'Études, rapports et ressources scientifiques de l\'association.',
    ];

    private const TITLES = [
        'Suivi de grossesse : les rendez-vous à ne pas manquer',
        'Dépistage du cancer du col : où en est la Guyane ?',
        'Allaitement : cinq idées reçues passées au crible',
        'Sages-femmes en zone isolée : le défi du dernier kilomètre',
        'Endométriose : mieux la reconnaître pour mieux la soigner',
        'Diabète gestationnel : ce que change le nouveau protocole',
        'Contraception : un guide pratique pour les adolescentes',
        'Périnatalité : les chiffres clés de l\'année',
        'Entretien avec une gynécologue de Saint-Laurent-du-Maroni',
        'Violences faites aux femmes : repérer et orienter',
        'Ménopause : briser le silence au travail',
        'Nutrition pendant la grossesse : les produits locaux à privilégier',
        'Santé mentale périnatale : un enjeu encore sous-estimé',
        'Vaccination et grossesse : le point sur les recommandations',
        'Quinzaine obstétricale : retour sur la dernière édition',
        'Télé-expertise : quand la maternité se connecte aux communes de l\'intérieur',
        'Prématurité : l\'accompagnement des familles en néonatalogie',
        'Infections sexuellement transmissibles : le dépistage gratuit',
        'Rééducation périnéale : pourquoi elle compte',
        'Parcours de soins : ce qui change pour les femmes enceintes',
        'Médiatrices en santé : un lien indispensable',
        'Drépanocytose et grossesse : une surveillance renforcée',
        'Publication : état des lieux de la mortalité maternelle',
        'Formation continue : les ateliers de simulation obstétricale',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('NewsDemoSeeder ne doit pas être lancé en production.');
        }

        $author = User::query()->where('is_admin', true)->first();

        $categories = collect(self::CATEGORIES)->map(fn (string $description, string $name) => ArticleCategory::query()->firstOrCreate(
            ['name' => $name],
            ['section' => 'news', 'title' => $name, 'description' => $description, 'is_active' => true],
        ))->values();

        foreach (self::TITLES as $index => $title) {
            $slug = Str::slug($title);

            if (Article::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $this->command?->info('Article '.($index + 1).'/'.count(self::TITLES).' : '.$title);

            Article::query()->create([
                'title' => $title,
                'slug' => $slug,
                'article_category_id' => $categories[$index % $categories->count()]->id,
                'user_id' => $author?->id,
                'excerpt' => fake('fr_FR')->realText(170),
                'body' => $this->body(),
                // Un article sur sept sans photo, pour voir le visuel de remplacement.
                'image_path' => $index % 7 === 6 ? null : $this->downloadDemoImage($slug),
                'status' => ArticleStatus::Published,
                'published_at' => now()->subHours($index * 29 + 2),
            ]);
        }

        // Un brouillon et un article programmé : ils ne doivent pas apparaître sur le site.
        Article::query()->firstOrCreate(['slug' => 'brouillon-de-demonstration'], [
            'title' => 'Brouillon de démonstration',
            'article_category_id' => $categories[0]->id,
            'status' => ArticleStatus::Draft,
            'body' => $this->body(),
        ]);
        Article::query()->firstOrCreate(['slug' => 'article-programme-de-demonstration'], [
            'title' => 'Article programmé de démonstration',
            'article_category_id' => $categories[1]->id,
            'status' => ArticleStatus::Scheduled,
            'published_at' => now()->addWeek(),
            'body' => $this->body(),
        ]);
    }

    private function body(): string
    {
        $faker = fake('fr_FR');
        $html = '';

        foreach (range(1, 3) as $section) {
            $html .= '<h2>'.rtrim($faker->sentence(5), '.').'</h2>';
            $html .= '<p>'.$faker->realText(600).'</p>';
            $html .= '<p>'.$faker->realText(500).'</p>';

            if ($section === 2) {
                $html .= '<blockquote><p>'.$faker->realText(160).'</p></blockquote>';
                $html .= '<ul><li>'.$faker->sentence().'</li><li>'.$faker->sentence().'</li><li>'.$faker->sentence().'</li></ul>';
            }
        }

        return $html;
    }
}
