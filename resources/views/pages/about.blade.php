@extends('layouts.site')

@section('title', "L'association | La Quinzaine Obstétricale")

@section('content')
<x-page-hero page-key="about" class="compact association-hero" />

<section class="association-intro">
    <div class="association-years">
        <span>Depuis</span>
        <strong>15 ans</strong>
    </div>
    <div>
        <p class="eyebrow">Notre histoire</p>
        <h2>Agir durablement pour la santé sur le territoire guyanais</h2>
        <p>Depuis plus de 15 ans, nous développons des actions de formation, d'information, de prévention et de recherche afin d'améliorer la qualité des soins et de réduire les inégalités d'accès à la santé sur notre territoire.</p>
    </div>
</section>

<section class="mission-section">
    <div class="section-title">
        <p class="eyebrow">Nos missions</p>
        <h2>Former, informer, sensibiliser et faire coopérer</h2>
    </div>

    <div class="mission-grid">
        <article>
            <span>01</span>
            <h3>Former les professionnels</h3>
            <p>Congrès, formations et ateliers pour renforcer les pratiques et soutenir la montée en compétence des équipes.</p>
        </article>
        <article>
            <span>02</span>
            <h3>Informer les publics</h3>
            <p>Rendre accessibles les enjeux de la santé de la femme au grand public, aux familles et aux acteurs de santé.</p>
        </article>
        <article>
            <span>03</span>
            <h3>Sensibiliser aux enjeux majeurs</h3>
            <p>Périnatalité, endométriose, SOPK, fibromes, ménopause, cancers féminins, santé mentale et prévention.</p>
        </article>
        <article>
            <span>04</span>
            <h3>Favoriser la recherche</h3>
            <p>Encourager l'innovation et les partenariats au service d'une médecine plus performante et plus humaine.</p>
        </article>
        <article>
            <span>05</span>
            <h3>Améliorer l'organisation des soins</h3>
            <p>Contribuer à une santé plus équitable en Guyane en rapprochant les acteurs et les ressources du territoire.</p>
        </article>
    </div>
</section>

<section class="ambition-band">
    <p class="eyebrow">Notre ambition</p>
    <h2>Faire de la Guyane un territoire d'excellence en santé de la femme, en périnatalité et en prévention.</h2>
    <p>Nous plaçons la formation, la coopération et l'innovation au cœur de nos actions pour construire une santé plus accessible, plus performante et plus humaine.</p>
</section>
@endsection
