@extends('layouts.site')

@section('title', 'Nous soutenir | La Quinzaine Obstetricale')

@section('content')
@php($joinHero = \App\Models\PageSetting::forKey('join'))
@php($joinHeroImage = ! empty($joinHero['hero_image_path']) ? (str_starts_with($joinHero['hero_image_path'], 'images/') ? asset($joinHero['hero_image_path']) : Storage::url($joinHero['hero_image_path'])) : null)
<section class="page-hero join-hero title-{{ $joinHero['title_size'] }}{{ $joinHeroImage ? ' has-hero-image' : '' }}" @if ($joinHeroImage) style="--page-hero-image: url('{{ $joinHeroImage }}')" @endif>
    <p class="eyebrow">{{ $joinHero['eyebrow'] }}</p>
    <h1>{{ $joinHero['title'] }}</h1>
    <p>{{ $joinHero['description'] }}</p>
    <div class="hero-actions">
        <a class="btn primary" href="mailto:contact@quinzaine-obstetricale.test?subject=Demande%20d'adhesion">Adherer</a>
        <a class="btn secondary" href="#don">Faire un don</a>
    </div>
</section>

<section class="resource-grid join-grid">
    <article><span>Adhesion</span><h2>Adherer</h2><p>Participer a la vie associative et soutenir les actions d'information, de formation et de prevention.</p></article>
    <article><span>Partenariat</span><h2>Devenir partenaire</h2><p>Construire des projets avec l'association, partager des ressources et amplifier l'impact territorial.</p></article>
    <article id="don"><span>Don</span><h2>Faire un don</h2><p>Contribuer au developpement des campagnes, evenements, supports pedagogiques et actions locales.</p></article>
    <article><span>Terrain</span><h2>Benevolat</h2><p>Donner du temps pour l'accueil, la logistique, la sensibilisation ou l'appui aux manifestations.</p></article>
</section>
@endsection
