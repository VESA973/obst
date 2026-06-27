@extends('layouts.site')

@section('title', 'Nous soutenir | La Quinzaine Obstetricale')

@section('content')
<section class="page-hero join-hero">
    <p class="eyebrow">Nous soutenir</p>
    <h1>Rejoindre et soutenir La Quinzaine Obstetricale</h1>
    <p>Adherer, devenir partenaire, faire un don ou proposer du benevolat permet de renforcer les actions pour la sante des femmes et la perinatalite en Guyane.</p>
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
