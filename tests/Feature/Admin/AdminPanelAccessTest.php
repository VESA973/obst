<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Member;
use App\Models\ResourceFile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_panel_login(): void
    {
        $this->get('/admin/articles')->assertRedirect(route('filament.admin.auth.login'));
        $this->get(route('filament.admin.auth.login'))->assertOk();
    }

    public function test_old_admin_login_url_redirects_to_panel_login(): void
    {
        $this->get('/admin/connexion')->assertRedirect('/admin/login');
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/articles')->assertForbidden();
    }

    public function test_first_logged_user_can_become_admin_when_none_exists(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.bootstrap'))->assertRedirect('/admin');

        $this->assertTrue($user->refresh()->is_admin);
    }

    public function test_bootstrap_is_closed_once_an_admin_exists(): void
    {
        User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('admin.bootstrap'))->assertForbidden();
    }

    public function test_panel_login_stays_accessible_during_maintenance(): void
    {
        SiteSetting::setValue('maintenance_enabled', '1');

        $this->get(route('filament.admin.auth.login'))->assertOk();
    }

    /**
     * Chaque écran du panneau s'affiche sans erreur, avec des données.
     */
    #[DataProvider('panelUrls')]
    public function test_admin_can_open_every_panel_page(string $url): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_member' => true]);
        $category = ArticleCategory::factory()->create(['name' => 'Tribunes', 'slug' => 'tribunes']);
        $article = Article::factory()->for($category, 'category')->create();
        $event = Event::create(['title' => 'Colloque', 'event_date' => now()->addMonth(), 'is_published' => true]);
        EventRegistration::create(['event_id' => $event->id, 'name' => 'Jeanne', 'email' => 'jeanne@example.com']);
        Member::create(['name' => 'Membre test', 'status' => 'actif']);
        ResourceFile::create(['title' => 'Guide', 'audience' => 'pro', 'path' => 'resources/guide.pdf']);

        $url = strtr($url, ['{article}' => $article->id, '{category}' => $category->id, '{event}' => $event->id]);

        $this->actingAs($admin)->get($url)->assertOk();
    }

    public static function panelUrls(): array
    {
        return collect([
            '/admin',
            '/admin/categories',
            '/admin/categories/creer',
            '/admin/categories/{category}/modifier',
            '/admin/articles',
            '/admin/articles/categorie/tribunes',
            '/admin/articles/creer?categorie=tribunes',
            '/admin/articles/{article}/modifier',
            '/admin/evenements',
            '/admin/evenements/creer',
            '/admin/evenements/{event}/modifier',
            '/admin/inscriptions',
            '/admin/membres',
            '/admin/ressources',
            '/admin/professionnels',
            '/admin/utilisateurs',
            '/admin/newsletter',
            '/admin/page-association',
            '/admin/messages',
            '/admin/pages',
            '/admin/configuration',
        ])->mapWithKeys(fn (string $url) => [$url => [$url]])->all();
    }
}
