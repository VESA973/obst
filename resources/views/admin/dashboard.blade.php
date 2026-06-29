@extends('layouts.site')

@section('title', 'Administration CMS | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero compact admin-hero">
    <p class="eyebrow">Administration CMS</p>
    <h1>Piloter le site</h1>
    <p>Un espace reserve pour gerer les contenus, les membres, les fichiers, l'agenda, les utilisateurs et la configuration generale du site.</p>
</section>

<section class="cms-shell">
    <aside class="cms-sidebar" aria-label="Navigation administration">
        <strong>Administration</strong>
        <a href="#configuration">Configuration</a>
        <a href="#actualites">Actualites</a>
        <a href="#membres">Membres</a>
        <a href="#fichiers">Fichiers</a>
        <a href="#agenda">Agenda</a>
        <a href="#utilisateurs">Utilisateurs</a>
        <a href="{{ route('home') }}">Voir le site</a>
    </aside>

    <div class="cms-main">
        <section class="admin-overview">
            <article class="{{ $settings['maintenance_enabled'] === '1' ? 'maintenance-on' : 'maintenance-off' }}">
                <span>{{ $settings['maintenance_enabled'] === '1' ? 'ON' : 'OK' }}</span>
                <strong>{{ $settings['maintenance_enabled'] === '1' ? 'Maintenance' : 'Site en ligne' }}</strong>
            </article>
            <article><span>{{ $articles->count() }}</span><strong>Actualites</strong></article>
            <article><span>{{ $members->count() }}</span><strong>Membres</strong></article>
            <article><span>{{ $files->count() }}</span><strong>Fichiers</strong></article>
            <article><span>{{ $events->count() }}</span><strong>Evenements</strong></article>
            <article><span>{{ $users->where('is_admin', true)->count() }}</span><strong>Admins</strong></article>
        </section>

        <section class="admin-section cms-config" id="configuration">
            <div class="admin-section-heading">
                <p class="eyebrow">Configuration</p>
                <h2>Reglages generaux du site</h2>
                <p>Activez le mode maintenance lors des mises a jour. Les administrateurs gardent l'acces a l'administration et a la connexion.</p>
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
                <button type="submit">Enregistrer la configuration</button>
            </form>
        </section>

        <section class="admin-section" id="actualites">
            <div class="admin-section-heading">
                <p class="eyebrow">Actualites</p>
                <h2>Piloter les articles et liens externes</h2>
                <p>Ajoutez vos propres articles ou relayez une publication d'un autre site avec son lien source.</p>
            </div>

            <div class="category-manager">
                <form method="POST" action="{{ route('admin.article-categories.store') }}">
                    @csrf
                    <input name="name" placeholder="Nouvelle categorie" required>
                    <button type="submit">Ajouter la categorie</button>
                </form>
                <div>
                    @forelse ($articleCategories as $category)
                        <form method="POST" action="{{ route('admin.article-categories.destroy', $category) }}">
                            @csrf
                            @method('DELETE')
                            <span>{{ $category->name }} - {{ $category->articles_count }} article(s)</span>
                            <button class="danger-button" type="submit">Supprimer</button>
                        </form>
                    @empty
                        <span>Aucune categorie creee.</span>
                    @endforelse
                </div>
            </div>

            <form class="admin-form rich-admin-form" method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre" required>
                <select name="article_category_id">
                    <option value="">Sans categorie</option>
                    @foreach ($articleCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <input name="source_name" placeholder="Source externe">
                <input name="external_url" type="url" placeholder="Lien de l'article externe">
                <input name="published_at" type="datetime-local">
                <label>
                    Image principale
                    <input name="image" type="file" accept="image/*">
                </label>
                <label>
                    Photos supplementaires
                    <input name="photos[]" type="file" accept="image/*" multiple>
                </label>
                <textarea name="excerpt" placeholder="Resume court"></textarea>
                <textarea name="body" placeholder="Article redige sur le site"></textarea>
                <label class="admin-check"><input name="is_published" type="checkbox" value="1" checked> Publier</label>
                <button type="submit">Ajouter l'actualite</button>
            </form>

            <div class="admin-list">
                @forelse ($articles as $article)
                    <article>
                        @php
                            $photos = $article->assets->where('type', 'photo');
                        @endphp
                        <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $article->title }}" required>
                            <select name="article_category_id">
                                <option value="">Sans categorie</option>
                                @foreach ($articleCategories as $category)
                                    <option value="{{ $category->id }}" @selected($article->article_category_id === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <input name="source_name" value="{{ $article->source_name }}" placeholder="Source externe">
                            <input name="external_url" type="url" value="{{ $article->external_url }}" placeholder="Lien externe">
                            <input name="published_at" type="datetime-local" value="{{ optional($article->published_at)->format('Y-m-d\TH:i') }}">
                            <label>
                                Remplacer l'image principale
                                <input name="image" type="file" accept="image/*">
                            </label>
                            <label>
                                Ajouter des photos
                                <input name="photos[]" type="file" accept="image/*" multiple>
                            </label>
                            <textarea name="excerpt" placeholder="Resume">{{ $article->excerpt }}</textarea>
                            <textarea name="body" placeholder="Article">{{ $article->body }}</textarea>
                            <label class="admin-check"><input name="is_published" type="checkbox" value="1" @checked($article->is_published)> Publier</label>
                            <button type="submit">Mettre a jour</button>
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
                    </article>
                @empty
                    <p class="empty-admin">Aucune actualite ajoutee.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-section" id="membres">
            <div class="admin-section-heading">
                <p class="eyebrow">Membres</p>
                <h2>Gerer les membres</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.members.store') }}">
                @csrf
                <input name="name" placeholder="Nom" required>
                <input name="email" type="email" placeholder="Email">
                <input name="phone" placeholder="Telephone">
                <input name="role" placeholder="Role ou fonction">
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
                            <input name="phone" value="{{ $member->phone }}" placeholder="Telephone">
                            <input name="role" value="{{ $member->role }}" placeholder="Role">
                            <select name="status">
                                @foreach (['actif', 'en attente', 'archive'] as $status)
                                    <option value="{{ $status }}" @selected($member->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                            <textarea name="notes" placeholder="Notes">{{ $member->notes }}</textarea>
                            <button type="submit">Mettre a jour</button>
                        </form>
                        <form method="POST" action="{{ route('admin.members.destroy', $member) }}">
                            @csrf
                            @method('DELETE')
                            <button class="danger-button" type="submit">Supprimer</button>
                        </form>
                    </article>
                @empty
                    <p class="empty-admin">Aucun membre enregistre.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-section" id="fichiers">
            <div class="admin-section-heading">
                <p class="eyebrow">Fichiers</p>
                <h2>Mettre des ressources a disposition</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.files.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre du fichier" required>
                <select name="audience" required>
                    <option value="public">Particuliers</option>
                    <option value="pro">Professionnels</option>
                </select>
                <input name="category" placeholder="Categorie">
                <textarea name="description" placeholder="Description courte"></textarea>
                <input name="file" type="file" required>
                <button type="submit">Ajouter le fichier</button>
            </form>

            <div class="admin-table">
                @forelse ($files as $file)
                    <article>
                        <div>
                            <strong>{{ $file->title }}</strong>
                            <span>{{ $file->audience === 'pro' ? 'Professionnels' : 'Particuliers' }} - {{ $file->category ?: 'Sans categorie' }}</span>
                            <p>{{ $file->description }}</p>
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
                    <p class="empty-admin">Aucun fichier ajoute.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-section" id="agenda">
            <div class="admin-section-heading">
                <p class="eyebrow">Agenda</p>
                <h2>Gerer les evenements</h2>
            </div>

            <form class="admin-form rich-admin-form" method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf
                <input name="title" placeholder="Titre" required>
                <input name="event_date" type="date">
                <input name="location" placeholder="Lieu">
                <input name="registration_url" type="url" placeholder="Lien HelloAsso si payant">
                <input name="registration_capacity" type="number" min="1" placeholder="Places gratuites">
                <label>
                    Flyer ou miniature principale
                    <input name="image" type="file" accept="image/*">
                </label>
                <div class="schedule-fields">
                    <strong>Dates et horaires</strong>
                    @for ($slot = 0; $slot < 3; $slot++)
                        <div>
                            <input name="schedule_label[]" placeholder="Nom du creneau">
                            <input name="schedule_date[]" type="date">
                            <input name="schedule_start_time[]" type="time">
                            <input name="schedule_end_time[]" type="time">
                        </div>
                    @endfor
                </div>
                <label>
                    Photos supplementaires
                    <input name="photos[]" type="file" accept="image/*" multiple>
                </label>
                <div class="document-fields">
                    <strong>Documents associes</strong>
                    @for ($document = 0; $document < 3; $document++)
                        <div>
                            <input name="document_titles[]" placeholder="Titre du document">
                            <input name="documents[]" type="file">
                        </div>
                    @endfor
                </div>
                <textarea name="description" placeholder="Description"></textarea>
                <label class="admin-check"><input name="is_paid" type="checkbox" value="1"> Evenement payant</label>
                <label class="admin-check"><input name="is_published" type="checkbox" value="1" checked> Publier</label>
                <button type="submit">Ajouter l'evenement</button>
            </form>

            <div class="admin-list">
                @forelse ($events as $event)
                    <article>
                        @php
                            $scheduleRows = $event->schedule_items ?? [];
                            $scheduleRows = array_pad($scheduleRows, max(3, count($scheduleRows) + 1), []);
                            $photos = $event->assets->where('type', 'photo');
                            $documents = $event->assets->where('type', 'document');
                        @endphp
                        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input name="title" value="{{ $event->title }}" required>
                            <input name="event_date" type="date" value="{{ optional($event->event_date)->format('Y-m-d') }}">
                            <input name="location" value="{{ $event->location }}" placeholder="Lieu">
                            <input name="registration_url" type="url" value="{{ $event->registration_url }}" placeholder="Lien HelloAsso si payant">
                            <input name="registration_capacity" type="number" min="1" value="{{ $event->registration_capacity }}" placeholder="Places gratuites">
                            <label>
                                Remplacer le flyer ou la miniature
                                <input name="image" type="file" accept="image/*">
                            </label>
                            <div class="schedule-fields">
                                <strong>Dates et horaires</strong>
                                @foreach ($scheduleRows as $slot)
                                    <div>
                                        <input name="schedule_label[]" value="{{ $slot['label'] ?? '' }}" placeholder="Nom du creneau">
                                        <input name="schedule_date[]" type="date" value="{{ $slot['date'] ?? '' }}">
                                        <input name="schedule_start_time[]" type="time" value="{{ $slot['start_time'] ?? '' }}">
                                        <input name="schedule_end_time[]" type="time" value="{{ $slot['end_time'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                            <label>
                                Ajouter des photos
                                <input name="photos[]" type="file" accept="image/*" multiple>
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
                            <label class="admin-check"><input name="is_paid" type="checkbox" value="1" @checked($event->is_paid)> Evenement payant</label>
                            <label class="admin-check"><input name="is_published" type="checkbox" value="1" @checked($event->is_published)> Publier</label>
                            <button type="submit">Mettre a jour</button>
                        </form>
                        @if ($event->image_path || $photos->isNotEmpty() || $documents->isNotEmpty())
                            <div class="event-assets-admin">
                                @if ($event->image_path)
                                    <a href="{{ Storage::url($event->image_path) }}" target="_blank" rel="noreferrer">Flyer principal</a>
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
                    </article>
                @empty
                    <p class="empty-admin">Aucun evenement programme.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-section" id="utilisateurs">
            <div class="admin-section-heading">
                <p class="eyebrow">Utilisateurs</p>
                <h2>Gerer les acces administration</h2>
            </div>

            <form class="admin-form" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input name="name" placeholder="Nom" required>
                <input name="email" type="email" placeholder="Email" required>
                <input name="password" type="password" placeholder="Mot de passe" required>
                <label class="admin-check"><input name="is_admin" type="checkbox" value="1"> Administrateur</label>
                <label class="admin-check"><input name="is_member" type="checkbox" value="1"> Membre</label>
                <button type="submit">Creer l'utilisateur</button>
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
                            <button type="submit">Mettre a jour</button>
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
    </div>
</section>
@endsection
