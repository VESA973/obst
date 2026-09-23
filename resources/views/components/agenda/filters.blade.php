{{--
    Filtres de l'agenda (front, agenda.js) : recherche, type, commune, mois, et bascule liste / calendrier.
    Sans JavaScript, le formulaire est masqué et la liste complète reste affichée.
--}}
@props(['types', 'communes', 'months'])

<div {{ $attributes->class('agenda-filters') }} data-agenda-filters hidden>
    <div class="agenda-filters-row">
        <label class="agenda-search">
            <span class="sr-only">Rechercher un événement</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" data-filter-search placeholder="Rechercher : atelier, Kourou, dépistage…" autocomplete="off">
        </label>

        {{-- Mobile : les pastilles se replient derrière ce bouton pour que la barre collante reste compacte. --}}
        <button type="button" class="agenda-filters-toggle" data-filters-toggle aria-expanded="false" aria-controls="agenda-chips">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
            Filtres <span data-filters-active hidden></span>
        </button>

        <div class="agenda-view-toggle" role="group" aria-label="Affichage">
            <button type="button" data-view="list" aria-pressed="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg><span class="agenda-view-label">Liste</span>
            </button>
            <button type="button" data-view="calendar" aria-pressed="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span class="agenda-view-label">Calendrier</span>
            </button>
        </div>
    </div>

    <div class="agenda-chips-groups" id="agenda-chips">
        @foreach (['type' => ['Type', $types], 'commune' => ['Commune', $communes], 'month' => ['Mois', $months]] as $filter => [$label, $options])
            @if (count($options) > 1)
                <fieldset class="agenda-chips" data-filter-group="{{ $filter }}">
                    <legend>{{ $label }}</legend>
                    <div class="agenda-chips-scroll">
                        <button type="button" class="agenda-chip" data-value="" aria-pressed="true">Tous</button>
                        @foreach ($options as $value => $option)
                            <button type="button" class="agenda-chip" data-value="{{ $value }}" aria-pressed="false"
                                    @isset($option['color']) style="--type-color: {{ $option['color'] }}" @endisset>
                                {{ $option['label'] }}
                            </button>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        @endforeach
    </div>

    <p class="agenda-results-count" data-results-count aria-live="polite"></p>
</div>
