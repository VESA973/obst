{{--
    Calendrier mensuel léger (construit par agenda.js à partir des événements de la liste).
    Grille navigable au clavier (flèches, Début/Fin, Page préc./suiv.), jours avec événements marqués par des pastilles.
--}}
<section {{ $attributes->class('agenda-calendar') }} data-agenda-calendar hidden aria-labelledby="agenda-calendar-title">
    <div class="agenda-calendar-head">
        <button type="button" class="home-carousel-btn" data-calendar-prev><span class="sr-only">Mois précédent</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
        <h2 id="agenda-calendar-title" data-calendar-title aria-live="polite"></h2>
        <button type="button" class="home-carousel-btn" data-calendar-next><span class="sr-only">Mois suivant</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
    </div>
    <table class="agenda-calendar-grid" role="grid" aria-labelledby="agenda-calendar-title">
        <thead>
            <tr>
                @foreach (['lun.' => 'lundi', 'mar.' => 'mardi', 'mer.' => 'mercredi', 'jeu.' => 'jeudi', 'ven.' => 'vendredi', 'sam.' => 'samedi', 'dim.' => 'dimanche'] as $short => $long)
                    <th scope="col"><abbr title="{{ $long }}">{{ $short }}</abbr></th>
                @endforeach
            </tr>
        </thead>
        <tbody data-calendar-body></tbody>
    </table>
    <div class="agenda-calendar-day" data-calendar-day aria-live="polite"></div>
</section>
