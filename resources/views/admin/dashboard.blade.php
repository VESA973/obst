@extends('layouts.admin')

@section('title', 'Administration CMS | La Quinzaine Obstétricale')

@section('content')
@php
    $activeSection = $activeSection ?? 'dashboard';
@endphp

<section class="admin-topbar">
    <div>
        <span>Administration</span>
        <strong>La Quinzaine Obstétricale</strong>
    </div>
    <nav aria-label="Actions administration">
        <a href="{{ route('home') }}">Voir le site</a>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Déconnexion</button>
        </form>
    </nav>
</section>

<section class="cms-shell">
    <aside class="cms-sidebar" aria-label="Navigation administration">
        <strong>Admin</strong>
        <a href="{{ route('admin.home') }}" @class(['active' => $activeSection === 'dashboard'])><span>00</span> Tableau de bord</a>
        <a href="{{ route('admin.configuration') }}" @class(['active' => $activeSection === 'configuration'])><span>01</span> Configuration</a>
        <a href="{{ route('admin.pages.index') }}" @class(['active' => $activeSection === 'pages'])><span>02</span> Pages</a>
        <a href="{{ route('admin.articles.index') }}" @class(['active' => $activeSection === 'actualites'])><span>03</span> Gestion des articles</a>
        <a href="{{ route('admin.members.index') }}" @class(['active' => $activeSection === 'membres'])><span>04</span> Membres</a>
        <a href="{{ route('admin.files.index') }}" @class(['active' => $activeSection === 'fichiers'])><span>05</span> Fichiers</a>
        <a href="{{ route('admin.events.index') }}" @class(['active' => $activeSection === 'agenda'])><span>06</span> Agenda</a>
        <a href="{{ route('admin.registrations.index') }}" @class(['active' => $activeSection === 'inscriptions'])><span>07</span> Inscriptions</a>
        <a href="{{ route('admin.users.index') }}" @class(['active' => $activeSection === 'utilisateurs'])><span>08</span> Utilisateurs</a>
        <a href="{{ route('home') }}"><span>↗</span> Voir le site</a>
        <form class="admin-sidebar-logout" method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">Déconnexion</button>
        </form>
    </aside>

    <div class="cms-main">
        <div class="admin-console-head">
            <div>
                <p class="eyebrow">Console</p>
                <h2>Modules du site</h2>
            </div>
            <a class="admin-site-link" href="{{ route('home') }}">Ouvrir le site</a>
        </div>

        @if ($activeSection === 'dashboard')
        <section class="admin-overview">
            <article class="{{ $settings['maintenance_enabled'] === '1' ? 'maintenance-on' : 'maintenance-off' }}">
                <span>{{ $settings['maintenance_enabled'] === '1' ? 'ON' : 'OK' }}</span>
                <strong>{{ $settings['maintenance_enabled'] === '1' ? 'Maintenance' : 'Site en ligne' }}</strong>
            </article>
            <article><span>{{ $articleTotal }}</span><strong>Articles</strong></article>
            <article><span>{{ $members->count() }}</span><strong>Membres</strong></article>
            <article><span>{{ $files->count() }}</span><strong>Fichiers</strong></article>
            <article><span>{{ $eventTotal }}</span><strong>Événements</strong></article>
            <article><span>{{ $users->where('is_admin', true)->count() }}</span><strong>Admins</strong></article>
        </section>

        <section class="module-grid" aria-label="Modules administration">
            <a href="{{ route('admin.configuration') }}"><strong>Configuration</strong><span>Maintenance, note interne</span></a>
            <a href="{{ route('admin.pages.index') }}"><strong>Pages</strong><span>Menus, entêtes, images</span></a>
            <a href="{{ route('admin.articles.index') }}"><strong>Gestion des articles</strong><span>Articles, catégories, photos</span></a>
            <a href="{{ route('admin.members.index') }}"><strong>Membres</strong><span>Contacts et statuts</span></a>
            <a href="{{ route('admin.files.index') }}"><strong>Fichiers</strong><span>Ressources publiques et pro</span></a>
            <a href="{{ route('admin.events.index') }}"><strong>Agenda</strong><span>Événements, QR codes, inscriptions</span></a>
            <a href="{{ route('admin.registrations.index') }}"><strong>Inscriptions</strong><span>Participants et export CSV</span></a>
            <a href="{{ route('admin.users.index') }}"><strong>Utilisateurs</strong><span>Admins et comptes membres</span></a>
        </section>
        @endif

        @if ($activeSection === 'configuration')
        <section class="admin-section admin-module cms-config" id="configuration">
            <div class="admin-section-heading">
                <p class="eyebrow">Configuration</p>
                <h2>Réglages généraux du site</h2>
                <p>Activez le mode maintenance lors des mises à jour. Les administrateurs gardent l'accès à l'administration et à la connexion.</p>
            </div>

            <form class="admin-form settings-form" method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')
                <label class="maintenance-toggle">
                    <input name="maintenance_enabled" type="checkbox" value="1" @checked($settings['maintenance_enabled'] === '1')>
                    <span>
                        <strong>Mode maintenance</strong>
                        <small>{{ $settings['maintenance_enabled'] === '1' ? 'Le site public affiche la page de maintenance.' : 'Le site public est accessible.' }}</small>
                    </span>
                </label>
                <label>
                    Message de maintenance
                    <textarea name="maintenance_message" required>{{ $settings['maintenance_message'] }}</textarea>
                </label>
                <label>
                    Note interne
                    <textarea name="admin_note" placeholder="Information visible uniquement dans l'administration">{{ $settings['admin_note'] }}</textarea>
                </label>
                <label>
                    Envoi des emails
                    <select name="smtp_mailer" required>
                        <option value="smtp" @selected($settings['smtp_mailer'] === 'smtp')>SMTP</option>
                        <option value="log" @selected($settings['smtp_mailer'] === 'log')>Test local sans envoi</option>
                    </select>
                </label>
                <input name="smtp_host" value="{{ $settings['smtp_host'] }}" placeholder="Serveur SMTP">
                <input name="smtp_port" type="number" min="1" max="65535" value="{{ $settings['smtp_port'] }}" placeholder="Port SMTP">
                <label>
                    Sécurité SMTP
                    <select name="smtp_encryption">
                        <option value="ssl" @selected($settings['smtp_encryption'] === 'ssl')>SSL</option>
                        <option value="tls" @selected($settings['smtp_encryption'] === 'tls')>TLS</option>
                        <option value="" @selected($settings['smtp_encryption'] === '')>Aucune</option>
                    </select>
                </label>
                <input name="smtp_username" value="{{ $settings['smtp_username'] }}" placeholder="Identifiant SMTP">
                <input name="smtp_password" type="password" placeholder="Mot de passe SMTP (laisser vide pour conserver)">
                <input name="smtp_from_address" type="email" value="{{ $settings['smtp_from_address'] }}" placeholder="Adresse email d'envoi">
                <input name="smtp_from_name" value="{{ $settings['smtp_from_name'] }}" placeholder="Nom d'expéditeur">
                <button type="submit">Enregistrer la configuration</button>
            </form>
        </section>
        @endif

        @if ($activeSection === 'pages')
        <section class="admin-section admin-module" id="pages">
            <div class="admin-section-heading">
                <p class="eyebrow">Pages</p>
                <h2>Configurer les entêtes et le menu</h2>
                <p>Modifiez le libellé du menu, le titre affiché en haut de page, l'image d'entête et la taille du texte.</p>
            </div>

            <form class="admin-page-settings" method="POST" action="{{ route('admin.pages.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @foreach ($pageSettings as $pageKey => $page)
                    <details class="admin-event-item admin-page-item">
                        <summary class="admin-event-summary admin-page-summary">
                            <span class="admin-event-date">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span>
                                <strong>{{ $page['menu_label'] }}</strong>
                                <small>{{ route($page['route']) }}</small>
                            </span>
                            <span class="admin-event-badges">
                                <small>{{ $page['show_in_menu'] ? 'Menu visible' : 'Hors menu' }}</small>
                                <small>{{ ucfirst($page['title_size']) }}</small>
                                @if (! empty($page['hero_image_path']))
                                    <small>Image</small>
                                @endif
                            </span>
                            <span class="admin-event-edit">Configurer</span>
                        </summary>
                        <div class="admin-page-form">
                            <input name="pages[{{ $pageKey }}][menu_label]" value="{{ $page['menu_label'] }}" placeholder="Nom dans le menu" required>
                            <input name="pages[{{ $pageKey }}][eyebrow]" value="{{ $page['eyebrow'] }}" placeholder="Petit titre au-dessus">
                            <input name="pages[{{ $pageKey }}][title]" value="{{ $page['title'] }}" placeholder="Titre de l'entête" required>
                            <select name="pages[{{ $pageKey }}][title_size]">
                                <option value="small" @selected($page['title_size'] === 'small')>Titre discret</option>
                                <option value="normal" @selected($page['title_size'] === 'normal')>Titre normal</option>
                                <option value="large" @selected($page['title_size'] === 'large')>Titre grand</option>
                            </select>
                            <label>
                                Image d'entête
                                <input name="hero_images[{{ $pageKey }}]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                            </label>
                            <textarea name="pages[{{ $pageKey }}][description]" placeholder="Description de l'entête">{{ $page['description'] }}</textarea>
                            <label class="admin-check">
                                <input name="pages[{{ $pageKey }}][show_in_menu]" type="checkbox" value="1" @checked($page['show_in_menu'])>
                                Afficher dans le menu principal
                            </label>
                            @if (! empty($page['hero_image_path']))
                                <a class="admin-current-image" href="{{ Storage::url($page['hero_image_path']) }}" target="_blank" rel="noreferrer">Voir l'image actuelle</a>
                            @endif
                        </div>
                    </details>
                @endforeach
                <button type="submit">Enregistrer les pages</button>
            </form>
        </section>
        @endif

        @if ($activeSection === 'actualites')
        <section class="admin-section admin-module" id="actualites">
            <div class="admin-section-heading">
                <p class="eyebrow">Gestion des articles</p>
                <h2>Piloter les articles, catégories et contenus Santé</h2>
                <p>Ajoutez vos articles, créez des catégories et choisissez si elles alimentent les actualités ou la page Santé de la femme.</p>
            </div>

            <div class="category-manager">
                <form class="category-form" method="POST" action="{{ route('admin.article-categories.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input name="name" placeholder="Nom de la catégorie" required>
                    <input name="title" placeholder="Titre affiché">
                    <select name="section" required>
                        <option value="news">Actualités</option>
                        <option value="public">Santé de la femme</option>
                    </select>
                    <label>
                        Image d'illustration
                        <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                    </label>
                    <textarea name="description" placeholder="Description de la catégorie"></textarea>
                    <button type="submit">Ajouter la catégorie</button>
                </form>
                <div>
                    @forelse ($articleCategories as $category)
                        <details class="category-edit-item">
                            <summary>
                                <span>{{ $category->name }} - {{ $category->section === 'public' ? 'Santé de la femme' : 'Actualités' }} - {{ $category->articles_count }} article(s)</span>
                            </summary>
                            <form class="category-form" method="POST" action="{{ route('admin.article-categories.update', $category) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input name="name" value="{{ $category->name }}" required>
                                <input name="title" value="{{ $category->title }}" placeholder="Titre affiché">
                                <select name="section" required>
                                    <option value="news" @selected($category->section === 'news')>Actualités</option>
                                    <option value="public" @selected($category->section === 'public')>Santé de la femme</option>
                                </select>
                                <label>
                                    Image d'illustration
                                    <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                                </label>
                                <textarea name="description" placeholder="Description">{{ $category->description }}</textarea>
                                @if ($category->image_path)
                                    <a class="admin-current-image" href="{{ Storage::url($category->image_path) }}" target="_blank" rel="noreferrer">Voir l'image actuelle</a>
                                @endif
                                <button type="submit">Mettre à jour</button>
                            </form>
                            <form method="POST" action="{{ route('admin.article-categories.destroy', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button class="danger-button" type="submit">Supprimer</button>
                            </form>
                        </details>
                    @empty
                        <span>Aucune catégorie créée.</span>
                    @endforelse
                </div>
            </div>

            <form class="admin-form rich-admin-form" method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre" required>
                <select name="article_category_id">
                    <option value="">Sans catégorie</option>
                    @foreach ($articleCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->section === 'public' ? 'Santé de la femme' : 'Actualités' }} - {{ $category->name }}</option>
                    @endforeach
                </select>
                <input name="source_name" placeholder="Source externe">
                <input name="external_url" type="url" placeholder="Lien de l'article externe">
                <input name="published_at" type="datetime-local">
                <label>
                    Image principale
                    <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                </label>
                <label>
                    Photos supplémentaires
                    <input name="photos[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*" multiple>
                </label>
                <textarea name="excerpt" placeholder="Résumé court"></textarea>
                <textarea name="body" placeholder="Article rédigé sur le site"></textarea>
                <label class="admin-check"><input name="is_published" type="checkbox" value="1" checked> Publier</label>
                <button type="submit">Ajouter l'actualité</button>
            </form>

            <div class="admin-list-toolbar">
                <span>{{ $articleTotal }} actualité(s)</span>
                <form method="GET" action="{{ route('admin.articles.index') }}">
                    <label for="articles-per-page">Afficher</label>
                    <select id="articles-per-page" name="articles_per_page" onchange="this.form.submit()">
                        @foreach ([10, 20, 50] as $option)
                            <option value="{{ $option }}" @selected($articlePerPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="admin-list">
                @forelse ($articles as $article)
                    <details class="admin-event-item admin-article-item">
                        @php
                            $photos = $article->assets->where('type', 'photo');
                        @endphp
                        <summary class="admin-event-summary admin-article-summary">
                            <span class="admin-event-date">{{ $article->published_at ? $article->published_at->translatedFormat('d M') : 'Date' }}</span>
                            <span>
                                <strong>{{ $article->title }}</strong>
                                <small>{{ $article->display_category }}{{ $article->source_name ? ' - '.$article->source_name : '' }}</small>
                            </span>
                            <span class="admin-event-badges">
                                <small>{{ $article->is_published ? 'Publié' : 'Brouillon' }}</small>
                                @if ($article->external_url)
                                    <small>Lien externe</small>
                                @else
                                    <small>Article site</small>
                                @endif
                                @if ($article->image_path || $photos->isNotEmpty())
                                    <small>Photo</small>
                                @endif
                            </span>
                            <span class="admin-event-edit">Modifier</span>
                        </summary>
                        <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $article->title }}" required>
                            <select name="article_category_id">
                                <option value="">Sans catégorie</option>
                                @foreach ($articleCategories as $category)
                                    <option value="{{ $category->id }}" @selected($article->article_category_id === $category->id)>{{ $category->section === 'public' ? 'Santé de la femme' : 'Actualités' }} - {{ $category->name }}</option>
                                @endforeach
                            </select>
                            <input name="source_name" value="{{ $article->source_name }}" placeholder="Source externe">
                            <input name="external_url" type="url" value="{{ $article->external_url }}" placeholder="Lien externe">
                            <input name="published_at" type="datetime-local" value="{{ optional($article->published_at)->format('Y-m-d\TH:i') }}">
                            <label>
                                Remplacer l'image principale
                                <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*">
                            </label>
                            <label>
                                Ajouter des photos
                                <input name="photos[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*" multiple>
                            </label>
                            <textarea name="excerpt" placeholder="Résumé">{{ $article->excerpt }}</textarea>
                            <textarea name="body" placeholder="Article">{{ $article->body }}</textarea>
                            <label class="admin-check"><input name="is_published" type="checkbox" value="1" @checked($article->is_published)> Publier</label>
                            <button type="submit">Mettre à jour</button>
                        </form>
                        @if ($article->image_path || $photos->isNotEmpty())
                            <div class="event-assets-admin">
                                @if ($article->image_path)
                                    <a href="{{ Storage::url($article->image_path) }}" target="_blank" rel="noreferrer">Image principale</a>
                                @endif
                                @foreach ($photos as $photo)
                                    <form method="POST" action="{{ route('admin.articles.assets.destroy', $photo) }}">
                                        @csrf
                                        @method('DELETE')
                                        <a href="{{ Storage::url($photo->path) }}" target="_blank" rel="noreferrer">{{ $photo->original_name ?: 'Photo' }}</a>
                                        <button class="danger-button" type="submit">Supprimer</button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}">
                            @csrf
                            @method('DELETE')
                            <button class="danger-button" type="submit">Supprimer</button>
                        </form>
                    </details>
                @empty
                    <p class="empty-admin">Aucune actualité ajoutée.</p>
                @endforelse
            </div>

            @if ($articles->hasPages())
                <div class="admin-pagination">
                    {{ $articles->links() }}
                </div>
            @endif
        </section>
        @endif

        @if ($activeSection === 'membres')
        <section class="admin-section admin-module" id="membres">
            <div class="admin-section-heading">
                <p class="eyebrow">Membres</p>
                <h2>Gérer les membres</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.members.store') }}">
                @csrf
                <input name="name" placeholder="Nom" required>
                <input name="email" type="email" placeholder="Email">
                <input name="phone" placeholder="Téléphone">
                <input name="role" placeholder="Rôle ou fonction">
                <select name="status">
                    <option value="actif">Actif</option>
                    <option value="en attente">En attente</option>
                    <option value="archive">Archive</option>
                </select>
                <textarea name="notes" placeholder="Notes internes"></textarea>
                <button type="submit">Ajouter le membre</button>
            </form>

            <div class="admin-list">
                @forelse ($members as $member)
                    <article>
                        <form method="POST" action="{{ route('admin.members.update', $member) }}">
                            @csrf
                            @method('PUT')
                            <input name="name" value="{{ $member->name }}" required>
                            <input name="email" type="email" value="{{ $member->email }}" placeholder="Email">
                            <input name="phone" value="{{ $member->phone }}" placeholder="Téléphone">
                            <input name="role" value="{{ $member->role }}" placeholder="Rôle">
                            <select name="status">
                                @foreach (['actif', 'en attente', 'archive'] as $status)
                                    <option value="{{ $status }}" @selected($member->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <textarea name="notes" placeholder="Notes">{{ $member->notes }}</textarea>
                            <button type="submit">Mettre à jour</button>
                        </form>
                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}">
                            @csrf
                            @method('DELETE')
                            <button class="danger-button" type="submit">Supprimer</button>
                        </form>
                    </article>
                @empty
                    <p class="empty-admin">Aucun membre enregistré.</p>
                @endforelse
            </div>
        </section>
        @endif

        @if ($activeSection === 'fichiers')
        <section class="admin-section admin-module" id="fichiers">
            <div class="admin-section-heading">
                <p class="eyebrow">Fichiers</p>
                <h2>Mettre des ressources à disposition</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre du fichier" required>
                <select name="audience" required>
                    <option value="public">Particuliers</option>
                    <option value="pro">Professionnels</option>
                </select>
                <input name="category" placeholder="Catégorie">
                <textarea name="description" placeholder="Description courte"></textarea>
                <input name="file" type="file" required>
                <button type="submit">Ajouter le fichier</button>
            </form>

            <div class="admin-table">
                @forelse ($files as $file)
                    <article class="admin-file-card">
                        <div class="admin-file-icon" aria-hidden="true">DOC</div>
                        <div class="admin-file-content">
                            <div class="admin-file-title">
                                <strong>{{ $file->title }}</strong>
                                <span>{{ $file->audience === 'pro' ? 'Professionnels' : 'Particuliers' }}</span>
                            </div>
                            <div class="admin-file-meta">
                                <span>{{ $file->category ?: 'Sans catégorie' }}</span>
                                @if ($file->original_name)
                                    <span>{{ $file->original_name }}</span>
                                @endif
                            </div>
                            <p>{{ $file->description ?: 'Aucune description renseignée.' }}</p>
                        </div>
                        <div class="admin-row-actions">
                            <a href="{{ Storage::url($file->path) }}" target="_blank" rel="noreferrer">Ouvrir</a>
                            <form method="POST" action="{{ route('admin.files.destroy', $file) }}">
                                @csrf
                                @method('DELETE')
                                <button class="danger-button" type="submit">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="empty-admin">Aucun fichier ajouté.</p>
                @endforelse
            </div>
        </section>
        @endif

        @if ($activeSection === 'agenda')
        <section class="admin-section admin-module" id="agenda">
            <div class="admin-section-heading">
                <p class="eyebrow">Agenda</p>
                <h2>Gérer les événements</h2>
            </div>

            <form class="admin-form rich-admin-form" method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre" required>
                <input name="event_date" type="date">
                <input name="location" placeholder="Lieu">
                <input name="registration_url" type="url" placeholder="Lien HelloAsso si payant">
                <input name="registration_capacity" type="number" min="1" placeholder="Places gratuites">
                <label>
                    Flyer principal
                    <input name="image" type="file" accept=".png,.jpg,.jpeg,.webp,.gif,.svg,.pdf,.doc,.docx,.ppt,.pptx,image/*,application/pdf">
                </label>
                <div class="schedule-fields">
                    <strong>Dates et horaires</strong>
                    @for ($slot = 0; $slot < 3; $slot++)
                        <div>
                            <input name="schedule_label[]" placeholder="Nom du créneau">
                            <input name="schedule_date[]" type="date">
                            <input name="schedule_start_time[]" type="time">
                            <input name="schedule_end_time[]" type="time">
                        </div>
                    @endfor
                </div>
                <label>
                    Photos supplémentaires
                    <input name="photos[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*" multiple>
                </label>
                <div class="document-fields">
                    <strong>Documents associés</strong>
                    @for ($document = 0; $document < 3; $document++)
                        <div>
                            <input name="document_titles[]" placeholder="Titre du document">
                            <input name="documents[]" type="file">
                        </div>
                    @endfor
                </div>
                <textarea name="description" placeholder="Description"></textarea>
                <label class="admin-check"><input name="is_paid" type="checkbox" value="1"> Événement payant</label>
                <label class="admin-check"><input name="is_published" type="checkbox" value="1" checked> Publier</label>
                <button type="submit">Ajouter l'événement</button>
            </form>

            <div class="admin-list-toolbar">
                <span>{{ $eventTotal }} événement(s)</span>
                <form method="GET" action="{{ route('admin.events.index') }}">
                    <label for="events-per-page">Afficher</label>
                    <select id="events-per-page" name="events_per_page" onchange="this.form.submit()">
                        @foreach ([10, 20, 50] as $option)
                            <option value="{{ $option }}" @selected($eventPerPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="admin-list">
                @forelse ($events as $event)
                    <details class="admin-event-item">
                        @php
                            $scheduleRows = $event->schedule_items ?? [];
                            $scheduleRows = array_pad($scheduleRows, max(3, count($scheduleRows) + 1), []);
                            $photos = $event->assets->where('type', 'photo');
                            $documents = $event->assets->where('type', 'document');
                        @endphp
                        <summary class="admin-event-summary">
                            <span class="admin-event-date">{{ $event->event_date ? $event->event_date->translatedFormat('d M') : 'À venir' }}</span>
                            <span>
                                <strong>{{ $event->title }}</strong>
                                <small>{{ $event->location ?: 'Lieu non renseigné' }}</small>
                                @if ($event->description)
                                    <small class="admin-event-description">{{ Str::limit($event->description, 150) }}</small>
                                @endif
                            </span>
                            <span class="admin-event-badges">
                                <small>{{ $event->is_published ? 'Publié' : 'Brouillon' }}</small>
                                <small>{{ $event->is_paid ? 'Payant' : 'Gratuit' }}</small>
                                @if ($event->registration_capacity)
                                    <small>{{ $event->registrations->count() }} / {{ $event->registration_capacity }} inscrit(s)</small>
                                @endif
                            </span>
                            <span class="admin-event-edit">Modifier</span>
                        </summary>
                        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $event->title }}" required>
                            <input name="event_date" type="date" value="{{ optional($event->event_date)->format('Y-m-d') }}">
                            <input name="location" value="{{ $event->location }}" placeholder="Lieu">
                            <input name="registration_url" type="url" value="{{ $event->registration_url }}" placeholder="Lien HelloAsso si payant">
                            <input name="registration_capacity" type="number" min="1" value="{{ $event->registration_capacity }}" placeholder="Places gratuites">
                            <label>
                                Remplacer le flyer principal
                                <input name="image" type="file" accept=".png,.jpg,.jpeg,.webp,.gif,.svg,.pdf,.doc,.docx,.ppt,.pptx,image/*,application/pdf">
                            </label>
                            <div class="schedule-fields">
                                <strong>Dates et horaires</strong>
                                @foreach ($scheduleRows as $slot)
                                    <div>
                                        <input name="schedule_label[]" value="{{ $slot['label'] ?? '' }}" placeholder="Nom du créneau">
                                        <input name="schedule_date[]" type="date" value="{{ $slot['date'] ?? '' }}">
                                        <input name="schedule_start_time[]" type="time" value="{{ $slot['start_time'] ?? '' }}">
                                        <input name="schedule_end_time[]" type="time" value="{{ $slot['end_time'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                            <label>
                                Ajouter des photos
                                <input name="photos[]" type="file" accept=".jpg,.jpeg,.png,.webp,.gif,.svg,image/*" multiple>
                            </label>
                            <div class="document-fields">
                                <strong>Ajouter des documents</strong>
                                @for ($document = 0; $document < 3; $document++)
                                    <div>
                                        <input name="document_titles[]" placeholder="Titre du document">
                                        <input name="documents[]" type="file">
                                    </div>
                                @endfor
                            </div>
                            <textarea name="description">{{ $event->description }}</textarea>
                            <label class="admin-check"><input name="is_paid" type="checkbox" value="1" @checked($event->is_paid)> Événement payant</label>
                            <label class="admin-check"><input name="is_published" type="checkbox" value="1" @checked($event->is_published)> Publier</label>
                            <button type="submit">Mettre à jour</button>
                        </form>
                        @if ($event->image_path || $photos->isNotEmpty() || $documents->isNotEmpty())
                            <div class="event-assets-admin">
                                @if ($event->image_path)
                                    <a href="{{ Storage::url($event->image_path) }}" target="_blank" rel="noreferrer">Flyer principal {{ $event->flyer_extension }}</a>
                                @endif
                                @foreach ($photos as $photo)
                                    <form method="POST" action="{{ route('admin.events.assets.destroy', $photo) }}">
                                        @csrf
                                        @method('DELETE')
                                        <a href="{{ Storage::url($photo->path) }}" target="_blank" rel="noreferrer">{{ $photo->original_name ?: 'Photo' }}</a>
                                        <button class="danger-button" type="submit">Supprimer</button>
                                    </form>
                                @endforeach
                                @foreach ($documents as $document)
                                    <form method="POST" action="{{ route('admin.events.assets.destroy', $document) }}">
                                        @csrf
                                        @method('DELETE')
                                        <a href="{{ Storage::url($document->path) }}" target="_blank" rel="noreferrer">{{ $document->title ?: $document->original_name ?: 'Document' }}</a>
                                        <button class="danger-button" type="submit">Supprimer</button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                        @if ($event->registrations->isNotEmpty())
                            <div class="registration-admin-list">
                                <strong>{{ $event->registrations->count() }} inscription(s)</strong>
                                @foreach ($event->registrations as $registration)
                                    <span>{{ $registration->name }} - {{ $registration->email }}{{ $registration->phone ? ' - '.$registration->phone : '' }}</span>
                                @endforeach
                            </div>
                        @endif
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
                            @csrf
                            @method('DELETE')
                            <button class="danger-button" type="submit">Supprimer</button>
                        </form>
                    </details>
                @empty
                    <p class="empty-admin">Aucun événement programmé.</p>
                @endforelse
            </div>

            @if ($events->hasPages())
                <div class="admin-pagination">
                    {{ $events->links() }}
                </div>
            @endif
        </section>
        @endif

        @if ($activeSection === 'inscriptions')
        <section class="admin-section admin-module" id="inscriptions">
            <div class="admin-section-heading">
                <p class="eyebrow">Inscriptions</p>
                <h2>Tableau des inscriptions aux événements</h2>
                <p>Consultez tous les participants inscrits aux événements gratuits et exportez la liste en CSV.</p>
            </div>

            <div class="admin-list-toolbar">
                <span>{{ $eventRegistrations->count() }} inscription(s)</span>
                <a class="admin-site-link" href="{{ route('admin.events.registrations.export') }}">Exporter en CSV</a>
            </div>

            <div class="admin-table registration-table">
                @forelse ($eventRegistrations as $registration)
                    <article>
                        <div>
                            <strong>{{ $registration->event?->title ?: 'Événement supprimé' }}</strong>
                            <span>{{ $registration->event?->event_date ? $registration->event->event_date->translatedFormat('d F Y') : 'Date non renseignée' }}</span>
                        </div>
                        <div>
                            <strong>{{ $registration->name }}</strong>
                            <span>{{ $registration->email }}{{ $registration->phone ? ' - '.$registration->phone : '' }}</span>
                        </div>
                        <small>Inscrit le {{ $registration->created_at->translatedFormat('d F Y H:i') }}</small>
                    </article>
                @empty
                    <p class="empty-admin">Aucune inscription enregistrée.</p>
                @endforelse
            </div>
        </section>
        @endif

        @if ($activeSection === 'utilisateurs')
        <section class="admin-section admin-module" id="utilisateurs">
            <div class="admin-section-heading">
                <p class="eyebrow">Utilisateurs</p>
                <h2>Gérer les accès administration</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input name="name" placeholder="Nom" required>
                <input name="email" type="email" placeholder="Email" required>
                <input name="password" type="password" placeholder="Mot de passe" required>
                <label class="admin-check"><input name="is_admin" type="checkbox" value="1"> Administrateur</label>
                <label class="admin-check"><input name="is_member" type="checkbox" value="1"> Membre</label>
                <button type="submit">Créer l'utilisateur</button>
            </form>

            <div class="admin-list">
                @foreach ($users as $user)
                    <article>
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <input name="name" value="{{ $user->name }}" required>
                            <input name="email" type="email" value="{{ $user->email }}" required>
                            <input name="password" type="password" placeholder="Nouveau mot de passe optionnel">
                            <label class="admin-check"><input name="is_admin" type="checkbox" value="1" @checked($user->is_admin)> Administrateur</label>
                            <label class="admin-check"><input name="is_member" type="checkbox" value="1" @checked($user->is_member)> Membre</label>
                            <button type="submit">Mettre à jour</button>
                        </form>
                        @unless (auth()->user()->is($user))
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf
                                @method('DELETE')
                                <button class="danger-button" type="submit">Supprimer</button>
                            </form>
                        @endunless
                    </article>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</section>
@endsection
