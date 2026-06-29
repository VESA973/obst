@extends('layouts.site')

@section('title', 'L association | La Quinzaine Obstetricale')

@section('content')
<x-page-hero page-key="about" class="compact association-hero" />

<section class="association-intro">
    <div class="association-years">
        <span>Depuis</span>
        <strong>15 ans</strong>
    </div>
    <div>
        <p class="eyebrow">Notre histoire</p>
        <h2>Agir durablement pour la sante sur le territoire guyanais</h2>
        <p>Depuis plus de 15 ans, nous developpons des actions de formation, d'information, de prevention et de recherche afin d'ameliorer la qualite des soins et de reduire les inegalites d'acces a la sante sur notre territoire.</p>
    </div>
</section>

<section class="mission-section">
    <div class="section-title">
        <p class="eyebrow">Nos missions</p>
        <h2>Former, informer, sensibiliser et faire cooperer</h2>
    </div>

    <div class="mission-grid">
        <article>
            <span>01</span>
            <h3>Former les professionnels</h3>
            <p>Congres, formations et ateliers pour renforcer les pratiques et soutenir la montee en competence des equipes.</p>
        </article>
        <article>
            <span>02</span>
            <h3>Informer les publics</h3>
            <p>Rendre accessibles les enjeux de la sante des femmes au grand public, aux familles et aux acteurs de sante.</p>
        </article>
        <article>
            <span>03</span>
            <h3>Sensibiliser aux enjeux majeurs</h3>
            <p>Perinatalite, endometriose, SOPK, fibromes, menopause, cancers feminins, sante mentale et prevention.</p>
        </article>
        <article>
            <span>04</span>
            <h3>Favoriser la recherche</h3>
            <p>Encourager l'innovation et les partenariats au service d'une medecine plus performante et plus humaine.</p>
        </article>
        <article>
            <span>05</span>
            <h3>Ameliorer l'organisation des soins</h3>
            <p>Contribuer a une sante plus equitable en Guyane en rapprochant les acteurs et les ressources du territoire.</p>
        </article>
    </div>
</section>

<section class="ambition-band">
    <p class="eyebrow">Notre ambition</p>
    <h2>Faire de la Guyane un territoire d'excellence en sante des femmes, en perinatalite et en prevention.</h2>
    <p>Nous placons la formation, la cooperation et l'innovation au coeur de nos actions pour construire une sante plus accessible, plus performante et plus humaine.</p>
</section>
@endsection
