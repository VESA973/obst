<?php

/*
 * Agenda (côté front uniquement) : le modèle Event n'a ni type, ni commune, ni coordonnées.
 * Ces informations sont déduites du titre et du lieu à partir des listes ci-dessous.
 */

return [
    /*
     * Types d'événement, reconnus par mots-clés dans le titre (sans accents ni majuscules).
     * Le premier type dont un mot-clé apparaît l'emporte. Couleurs : variables existantes du site.
     */
    'types' => [
        'conference' => ['label' => 'Conférence', 'keywords' => ['conference', 'debat', 'colloque', 'assises', 'congres'], 'color' => 'var(--deep-teal)'],
        'atelier' => ['label' => 'Atelier', 'keywords' => ['atelier', 'formation', 'escape game'], 'color' => 'var(--teal)'],
        'permanence' => ['label' => 'Permanence', 'keywords' => ['permanence'], 'color' => 'var(--coral-dark)'],
        'mission' => ['label' => 'Mission de terrain', 'keywords' => ['mission', 'itinerant', 'fleuve'], 'color' => 'color-mix(in srgb, var(--deep-teal) 70%, var(--coral-dark))'],
        'sensibilisation' => ['label' => 'Sensibilisation', 'keywords' => ['octobre rose', 'journee', 'stand', 'marche', 'campagne', 'prevention', 'depistage'], 'color' => 'var(--coral)'],
        'rencontre' => ['label' => 'Rencontre', 'keywords' => ['rencontre', 'jeudis', 'festival', 'cafe'], 'color' => 'color-mix(in srgb, var(--teal) 60%, var(--coral))'],
    ],

    'default_type' => ['label' => 'Événement', 'color' => 'var(--muted)'],

    /*
     * Communes de Guyane reconnues dans le champ « lieu », avec une position approximative
     * (centre de la commune) pour la carte. Aucune adresse précise n'est déduite.
     */
    'communes' => [
        'Apatou' => [5.155, -54.342],
        'Awala-Yalimapo' => [5.743, -53.918],
        'Camopi' => [3.166, -52.338],
        'Cayenne' => [4.937, -52.326],
        'Grand-Santi' => [4.255, -54.383],
        'Iracoubo' => [5.481, -53.207],
        'Kourou' => [5.160, -52.650],
        'Macouria' => [5.016, -52.481],
        'Mana' => [5.660, -53.778],
        'Maripasoula' => [3.641, -54.030],
        'Matoury' => [4.847, -52.331],
        'Montsinéry-Tonnegrande' => [4.896, -52.496],
        'Ouanary' => [4.218, -51.670],
        'Papaïchton' => [3.805, -54.150],
        'Régina' => [4.313, -52.133],
        'Rémire-Montjoly' => [4.917, -52.267],
        'Roura' => [4.728, -52.327],
        'Saint-Élie' => [4.823, -53.281],
        'Saint-Georges-de-l\'Oyapock' => [3.891, -51.805],
        'Saint-Laurent-du-Maroni' => [5.498, -54.033],
        'Saül' => [3.623, -53.208],
        'Sinnamary' => [5.377, -52.957],
    ],

    // Heure retenue quand un événement n'a pas d'horaire (compte à rebours, ajout à l'agenda).
    'default_start_time' => '09:00',
];
