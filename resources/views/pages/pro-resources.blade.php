@extends('layouts.site')

@section('title', 'Ressources | Espace professionnels')

@section('content')
<x-page-hero page-key="pro" class="pro-hero" />

<section class="pro-resource-page">
    <div class="pro-resource-heading">
        <p class="eyebrow">Espace professionnel</p>
        <h2>Ressources pédagogiques</h2>
        <p>Documents et supports mis à disposition depuis l’administration.</p>
        <a class="back-link" href="{{ route('pro') }}">Retour à l’espace pro</a>
    </div>

    <div class="resource-list">
        @forelse ($resources as $resource)
            <article class="resource-card">
                <div class="resource-card-icon" aria-hidden="true">DOC</div>
                <div class="resource-card-body">
                    <div class="resource-card-head">
                        <span>{{ $resource->category ?: 'Ressource' }}</span>
                        <small>{{ $resource->audience === 'pro' ? 'Professionnels' : 'Particuliers' }}</small>
                    </div>
                    <h3>{{ $resource->title }}</h3>
                    <p>{{ $resource->description ?: 'Document disponible en consultation.' }}</p>
                    @if ($resource->original_name)
                        <small class="resource-file-name">{{ $resource->original_name }}</small>
                    @endif
                </div>
                <a class="resource-download" href="{{ Storage::url($resource->path) }}" target="_blank" rel="noreferrer">Ouvrir</a>
            </article>
        @empty
            <div class="empty-state">
                <p>Aucune ressource n’est disponible pour le moment.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
