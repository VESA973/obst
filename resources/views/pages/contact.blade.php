@extends('layouts.site')

@section('title', 'Contact | La Quinzaine Obstetricale')

@section('content')
<x-page-hero page-key="contact" />

<section class="contact-layout">
    <form class="auth-card contact-form" action="mailto:contact@quinzaine-obstetricale.test" method="GET">
        <h2>Formulaire</h2>
        <label for="contact-name">Nom</label>
        <input id="contact-name" name="name" type="text" required>
        <label for="contact-email">Email</label>
        <input id="contact-email" name="email" type="email" required>
        <label for="contact-subject">Sujet</label>
        <input id="contact-subject" name="subject" type="text" required>
        <label for="contact-message">Message</label>
        <textarea id="contact-message" name="body" required></textarea>
        <button type="submit">Envoyer</button>
    </form>

    <div class="contact-side">
        <article>
            <span>Reseaux sociaux</span>
            <h2>Suivre l'association</h2>
            <p>Retrouvez les actualites, evenements et campagnes de sensibilisation sur nos reseaux.</p>
        </article>
        <article>
            <span>Newsletter</span>
            <h2>Recevoir les nouvelles</h2>
            <p>Inscrivez-vous pour suivre les manifestations, ressources et appels a participation.</p>
            <a class="btn secondary" href="mailto:contact@quinzaine-obstetricale.test?subject=Inscription%20newsletter">S'inscrire</a>
        </article>
    </div>
</section>
@endsection
