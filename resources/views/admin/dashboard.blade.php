@extends('layouts.site')

@section('title', 'Administration | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero compact admin-hero">
    <p class="eyebrow">Administration</p>
    <h1>Gestion du site</h1>
    <p>Un espace reserve pour gerer les membres, les fichiers mis a disposition, l'agenda des evenements et les utilisateurs de l'administration.</p>
</section>

<section class="admin-overview">
    <article><span>{{ $members->count() }}</span><strong>Membres</strong></article>
    <article><span>{{ $files->count() }}</span><strong>Fichiers</strong></article>
    <article><span>{{ $events->count() }}</span><strong>Evenements</strong></article>
    <article><span>{{ $users->where('is_admin', true)->count() }}</span><strong>Admins</strong></article>
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
                    <span>{{ $file->audience === 'pro' ? 'Professionnels' : 'Particuliers' }} · {{ $file->category ?: 'Sans categorie' }}</span>
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

    <form class="admin-form" method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        <input name="title" placeholder="Titre" required>
        <input name="event_date" type="date">
        <input name="location" placeholder="Lieu">
        <input name="registration_url" type="url" placeholder="Lien d'inscription">
        <textarea name="description" placeholder="Description"></textarea>
        <label class="admin-check"><input name="is_published" type="checkbox" value="1" checked> Publier</label>
        <button type="submit">Ajouter l'evenement</button>
    </form>

    <div class="admin-list">
        @forelse ($events as $event)
            <article>
                <form method="POST" action="{{ route('admin.events.update', $event) }}">
                    @csrf
                    @method('PUT')
                    <input name="title" value="{{ $event->title }}" required>
                    <input name="event_date" type="date" value="{{ optional($event->event_date)->format('Y-m-d') }}">
                    <input name="location" value="{{ $event->location }}" placeholder="Lieu">
                    <input name="registration_url" type="url" value="{{ $event->registration_url }}" placeholder="Lien d'inscription">
                    <textarea name="description">{{ $event->description }}</textarea>
                    <label class="admin-check"><input name="is_published" type="checkbox" value="1" @checked($event->is_published)> Publier</label>
                    <button type="submit">Mettre a jour</button>
                </form>
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
@endsection
