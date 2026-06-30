<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleAsset;
use App\Models\ArticleCategory;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventRegistration;
use App\Models\Member;
use App\Models\PageSetting;
use App\Models\ResourceFile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        return $this->dashboardView('dashboard');
    }

    public function configuration(): View
    {
        return $this->dashboardView('configuration');
    }

    public function pages(): View
    {
        return $this->dashboardView('pages');
    }

    public function articles(Request $request): View
    {
        return $this->dashboardView('actualites', $request);
    }

    public function members(): View
    {
        return $this->dashboardView('membres');
    }

    public function files(): View
    {
        return $this->dashboardView('fichiers');
    }

    public function events(Request $request): View
    {
        return $this->dashboardView('agenda', $request);
    }

    public function users(): View
    {
        return $this->dashboardView('utilisateurs');
    }

    public function registrations(): View
    {
        return $this->dashboardView('inscriptions');
    }

    public function professionals(): View
    {
        return $this->dashboardView('professionnels');
    }

    private function dashboardView(string $activeSection, ?Request $request = null): View
    {
        $request ??= request();
        $articlePerPage = (int) $request->integer('articles_per_page', 10);
        $articlePerPage = in_array($articlePerPage, [10, 20, 50], true) ? $articlePerPage : 10;
        $articleQuery = Article::with(['assets', 'categoryModel'])->latest('published_at')->latest();
        $eventPerPage = (int) $request->integer('events_per_page', 10);
        $eventPerPage = in_array($eventPerPage, [10, 20, 50], true) ? $eventPerPage : 10;
        $eventQuery = Event::with(['registrations', 'assets'])->orderByRaw('event_date IS NULL')->orderBy('event_date');

        return view('admin.dashboard', [
            'activeSection' => $activeSection,
            'articleCategories' => ArticleCategory::withCount('articles')->orderBy('section')->orderBy('name')->get(),
            'publicCategories' => ArticleCategory::where('section', 'public')->withCount('articles')->orderBy('name')->get(),
            'pageSettings' => PageSetting::allConfigured(),
            'articlePerPage' => $articlePerPage,
            'articleTotal' => Article::count(),
            'articles' => $activeSection === 'actualites'
                ? $articleQuery->paginate($articlePerPage)->withQueryString()
                : $articleQuery->get(),
            'members' => Member::latest()->get(),
            'files' => ResourceFile::latest()->get(),
            'eventPerPage' => $eventPerPage,
            'eventTotal' => Event::count(),
            'events' => $activeSection === 'agenda'
                ? $eventQuery->paginate($eventPerPage)->withQueryString()
                : $eventQuery->get(),
            'eventRegistrations' => EventRegistration::with('event')->latest()->get(),
            'professionalUsers' => User::where('is_member', true)->orWhere('is_health_professional', true)->latest()->get(),
            'users' => User::orderByDesc('is_admin')->orderBy('name')->get(),
            'settings' => [
                'maintenance_enabled' => SiteSetting::getValue('maintenance_enabled', '0'),
                'maintenance_message' => SiteSetting::getValue('maintenance_message', 'Le site est temporairement en maintenance. Merci de revenir dans quelques instants.'),
                'admin_note' => SiteSetting::getValue('admin_note', ''),
                'smtp_mailer' => SiteSetting::getValue('smtp_mailer', 'smtp'),
                'smtp_host' => SiteSetting::getValue('smtp_host', ''),
                'smtp_port' => SiteSetting::getValue('smtp_port', '465'),
                'smtp_encryption' => SiteSetting::getValue('smtp_encryption', 'ssl'),
                'smtp_username' => SiteSetting::getValue('smtp_username', ''),
                'smtp_from_address' => SiteSetting::getValue('smtp_from_address', ''),
                'smtp_from_name' => SiteSetting::getValue('smtp_from_name', 'La Quinzaine Obstétricale'),
                'google_login_enabled' => SiteSetting::getValue('google_login_enabled', '0'),
                'google_client_id' => SiteSetting::getValue('google_client_id', ''),
                'google_redirect_uri' => route('professional.google.callback'),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'maintenance_enabled' => ['nullable', 'boolean'],
            'maintenance_message' => ['required', 'string', 'max:500'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'smtp_mailer' => ['required', Rule::in(['smtp', 'log'])],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', Rule::in(['ssl', 'tls', ''])],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
            'google_login_enabled' => ['nullable', 'boolean'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        SiteSetting::setValue('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
        SiteSetting::setValue('maintenance_message', $attributes['maintenance_message']);
        SiteSetting::setValue('admin_note', $attributes['admin_note'] ?? '');
        SiteSetting::setValue('smtp_mailer', $attributes['smtp_mailer']);
        SiteSetting::setValue('smtp_host', $attributes['smtp_host'] ?? '');
        SiteSetting::setValue('smtp_port', (string) ($attributes['smtp_port'] ?? 465));
        SiteSetting::setValue('smtp_encryption', $attributes['smtp_encryption'] ?? '');
        SiteSetting::setValue('smtp_username', $attributes['smtp_username'] ?? '');
        SiteSetting::setValue('smtp_from_address', $attributes['smtp_from_address'] ?? '');
        SiteSetting::setValue('smtp_from_name', $attributes['smtp_from_name'] ?? '');

        if (filled($attributes['smtp_password'] ?? null)) {
            SiteSetting::setValue('smtp_password', $attributes['smtp_password']);
        }
        SiteSetting::setValue('google_login_enabled', $request->boolean('google_login_enabled') ? '1' : '0');
        SiteSetting::setValue('google_client_id', $attributes['google_client_id'] ?? '');

        if (filled($attributes['google_client_secret'] ?? null)) {
            SiteSetting::setValue('google_client_secret', $attributes['google_client_secret']);
        }

        return back()->with('status', 'Configuration du site mise à jour.');
    }

    public function updatePages(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'pages' => ['required', 'array'],
            'pages.*.menu_label' => ['required', 'string', 'max:80'],
            'pages.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'pages.*.title' => ['required', 'string', 'max:180'],
            'pages.*.description' => ['nullable', 'string', 'max:600'],
            'pages.*.title_size' => ['required', Rule::in(['small', 'normal', 'large'])],
            'pages.*.show_in_menu' => ['nullable', 'boolean'],
            'hero_images' => ['nullable', 'array'],
            'hero_images.*' => ['nullable', 'file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
        ]);

        foreach ($attributes['pages'] as $pageKey => $pageAttributes) {
            abort_unless(array_key_exists($pageKey, PageSetting::defaults()), 404);

            $pageSetting = PageSetting::firstOrNew(['page_key' => $pageKey]);

            if ($request->hasFile("hero_images.{$pageKey}")) {
                if ($pageSetting->hero_image_path) {
                    Storage::disk('public')->delete($pageSetting->hero_image_path);
                }

                $pageAttributes['hero_image_path'] = $request->file("hero_images.{$pageKey}")->store('pages/headers', 'public');
            }

            $pageAttributes['show_in_menu'] = $request->boolean("pages.{$pageKey}.show_in_menu");
            $pageSetting->fill($pageAttributes);
            $pageSetting->save();
        }

        return back()->with('status', 'Configuration des pages mise a jour.');
    }

    public function storeMember(Request $request): RedirectResponse
    {
        Member::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Membre ajoute.');
    }

    public function updateMember(Request $request, Member $member): RedirectResponse
    {
        $member->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:120'],
            'status' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]));

        return back()->with('status', 'Membre mis a jour.');
    }

    public function destroyMember(Member $member): RedirectResponse
    {
        $member->delete();

        return back()->with('status', 'Membre supprime.');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'audience' => ['required', Rule::in(['public', 'pro'])],
            'category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $uploadedFile = $request->file('file');
        $attributes['path'] = $uploadedFile->store('resources', 'public');
        $attributes['original_name'] = $uploadedFile->getClientOriginalName();
        unset($attributes['file']);

        ResourceFile::create($attributes);

        return back()->with('status', 'Ressource ajoutée.');
    }

    public function destroyFile(ResourceFile $file): RedirectResponse
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('status', 'Ressource supprimée.');
    }

    public function storeArticleCategory(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:article_categories,name'],
            'section' => ['nullable', Rule::in(['news', 'public'])],
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
        ]);

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')->store('article-categories', 'public');
        }

        ArticleCategory::create([
            'name' => $attributes['name'],
            'slug' => $this->uniqueCategorySlug($attributes['name']),
            'section' => $attributes['section'] ?? 'news',
            'title' => $attributes['title'] ?? $attributes['name'],
            'description' => $attributes['description'] ?? null,
            'image_path' => $attributes['image_path'] ?? null,
        ]);

        return back()->with('status', 'Catégorie ajoutée.');
    }

    public function updateArticleCategory(Request $request, ArticleCategory $category): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('article_categories', 'name')->ignore($category)],
            'section' => ['required', Rule::in(['news', 'public'])],
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
        ]);

        if ($category->name !== $attributes['name']) {
            $attributes['slug'] = $this->uniqueCategorySlug($attributes['name']);
        }

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $attributes['image_path'] = $request->file('image')->store('article-categories', 'public');
        }

        unset($attributes['image']);

        $category->update($attributes);

        return back()->with('status', 'Catégorie mise à jour.');
    }

    public function destroyArticleCategory(ArticleCategory $category): RedirectResponse
    {
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return back()->with('status', 'Catégorie supprimée.');
    }

    public function exportEventRegistrations(): StreamedResponse
    {
        $registrations = EventRegistration::with('event')->latest()->get();

        return response()->streamDownload(function () use ($registrations): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Événement', 'Date', 'Nom', 'Email', 'Téléphone', 'Inscrit le']);

            foreach ($registrations as $registration) {
                fputcsv($output, [
                    $registration->event?->title,
                    optional($registration->event?->event_date)->format('Y-m-d'),
                    $registration->name,
                    $registration->email,
                    $registration->phone,
                    $registration->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($output);
        }, 'inscriptions-evenements.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $article = Article::create($this->articleAttributes($request, publishByDefault: true));
        $this->storeArticleAssets($request, $article);

        return back()->with('status', 'Actualite ajoutee.');
    }

    public function updateArticle(Request $request, Article $article): RedirectResponse
    {
        $attributes = $this->articleAttributes($request, $article);

        if (isset($attributes['image_path']) && $article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        $article->update($attributes);
        $this->storeArticleAssets($request, $article);

        return back()->with('status', 'Actualite mise a jour.');
    }

    public function destroyArticle(Article $article): RedirectResponse
    {
        if ($article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        foreach ($article->assets as $asset) {
            Storage::disk('public')->delete($asset->path);
        }

        $article->delete();

        return back()->with('status', 'Actualite supprimee.');
    }

    public function destroyArticleAsset(ArticleAsset $asset): RedirectResponse
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return back()->with('status', 'Photo actualité supprimée.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $event = Event::create($this->eventAttributes($request));
        $this->storeEventAssets($request, $event);

        return back()->with('status', 'Événement ajouté.');
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        $attributes = $this->eventAttributes($request);

        if (isset($attributes['image_path']) && $event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        $event->update($attributes);
        $this->storeEventAssets($request, $event);

        return back()->with('status', 'Événement mis à jour.');
    }

    public function destroyEvent(Event $event): RedirectResponse
    {
        if ($event->image_path) {
            Storage::disk('public')->delete($event->image_path);
        }

        foreach ($event->assets as $asset) {
            Storage::disk('public')->delete($asset->path);
        }

        $event->delete();

        return back()->with('status', 'Événement supprimé.');
    }

    public function destroyEventAsset(EventAsset $asset): RedirectResponse
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return back()->with('status', 'Fichier événement supprimé.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        User::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'is_admin' => ['nullable', 'boolean'],
            'is_member' => ['nullable', 'boolean'],
        ]));

        return back()->with('status', 'Utilisateur cree.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', Password::min(8)],
            'is_admin' => ['nullable', 'boolean'],
            'is_member' => ['nullable', 'boolean'],
        ]);

        $attributes['is_admin'] = $request->boolean('is_admin');
        $attributes['is_member'] = $request->boolean('is_member');

        if (blank($attributes['password'])) {
            unset($attributes['password']);
        }

        $user->update($attributes);

        return back()->with('status', 'Utilisateur mis a jour.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->delete();

        return back()->with('status', 'Utilisateur supprime.');
    }

    public function updateProfessional(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'is_member' => ['nullable', 'boolean'],
            'is_health_professional' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);

        $user->forceFill([
            'is_member' => $request->boolean('is_member'),
            'is_health_professional' => $request->boolean('is_health_professional'),
            'email_verified_at' => $request->boolean('email_verified') ? ($user->email_verified_at ?? now()) : null,
        ])->save();

        return back()->with('status', 'Compte professionnel mis à jour.');
    }

    public function destroyProfessional(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Vous ne pouvez pas supprimer votre propre compte.');
        abort_if($user->is_admin, 422, 'Un administrateur ne peut pas être supprimé depuis les comptes professionnels.');

        $user->delete();

        return back()->with('status', 'Compte professionnel supprimé.');
    }

    private function eventAttributes(Request $request): array
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'schedule_label' => ['nullable', 'array'],
            'schedule_label.*' => ['nullable', 'string', 'max:120'],
            'schedule_date' => ['nullable', 'array'],
            'schedule_date.*' => ['nullable', 'date'],
            'schedule_start_time' => ['nullable', 'array'],
            'schedule_start_time.*' => ['nullable', 'date_format:H:i'],
            'schedule_end_time' => ['nullable', 'array'],
            'schedule_end_time.*' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'max:20480'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240'],
            'document_titles' => ['nullable', 'array'],
            'document_titles.*' => ['nullable', 'string', 'max:255'],
            'registration_url' => ['nullable', 'url', 'max:255', 'required_if:is_paid,1'],
            'is_paid' => ['nullable', 'boolean'],
            'registration_capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $attributes['schedule_items'] = $this->scheduleItems($request);

        if (! $attributes['event_date'] && $attributes['schedule_items']) {
            $attributes['event_date'] = $attributes['schedule_items'][0]['date'];
        }

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')->store('events/flyers', 'public');
        }

        unset(
            $attributes['image'],
            $attributes['photos'],
            $attributes['documents'],
            $attributes['document_titles'],
            $attributes['schedule_label'],
            $attributes['schedule_date'],
            $attributes['schedule_start_time'],
            $attributes['schedule_end_time'],
        );
        $attributes['is_paid'] = $request->boolean('is_paid');
        $attributes['is_published'] = $request->boolean('is_published');

        return $attributes;
    }

    private function scheduleItems(Request $request): array
    {
        $items = [];

        foreach ($request->input('schedule_date', []) as $index => $date) {
            if (blank($date)) {
                continue;
            }

            $items[] = [
                'label' => $request->input("schedule_label.$index"),
                'date' => $date,
                'start_time' => $request->input("schedule_start_time.$index"),
                'end_time' => $request->input("schedule_end_time.$index"),
            ];
        }

        return $items;
    }

    private function storeEventAssets(Request $request, Event $event): void
    {
        foreach ($request->file('photos', []) as $photo) {
            $event->assets()->create([
                'type' => 'photo',
                'path' => $photo->store('events/photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
            ]);
        }

        foreach ($request->file('documents', []) as $index => $document) {
            $event->assets()->create([
                'type' => 'document',
                'path' => $document->store('events/documents', 'public'),
                'original_name' => $document->getClientOriginalName(),
                'title' => $request->input("document_titles.$index"),
            ]);
        }
    }

    private function articleAttributes(Request $request, ?Article $article = null, bool $publishByDefault = false): array
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'article_category_id' => ['nullable', 'exists:article_categories,id'],
            'category' => ['nullable', 'string', 'max:120'],
            'source_name' => ['nullable', 'string', 'max:120'],
            'external_url' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['file', 'extensions:jpg,jpeg,png,webp,gif,svg', 'max:20480'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:10000'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $attributes['slug'] = $this->uniqueSlug($attributes['title'], $article);
        $attributes['is_published'] = $publishByDefault ? $request->boolean('is_published', true) : $request->boolean('is_published');
        $attributes['published_at'] = $attributes['published_at'] ?? now();

        if ($request->hasFile('image')) {
            $attributes['image_path'] = $request->file('image')->store('articles', 'public');
        }

        unset($attributes['image'], $attributes['photos']);

        return $attributes;
    }

    private function storeArticleAssets(Request $request, Article $article): void
    {
        foreach ($request->file('photos', []) as $photo) {
            $article->assets()->create([
                'type' => 'photo',
                'path' => $photo->store('articles/photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
            ]);
        }
    }

    private function uniqueSlug(string $title, ?Article $article = null): string
    {
        $base = Str::slug($title) ?: 'actualite';
        $slug = $base;
        $counter = 2;

        while (Article::where('slug', $slug)->when($article, fn ($query) => $query->whereKeyNot($article->id))->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'categorie';
        $slug = $base;
        $counter = 2;

        while (ArticleCategory::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
