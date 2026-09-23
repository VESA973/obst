<?php

/*
 * Page d'accueil (côté front uniquement).
 */

return [
    /*
     * Formule d'accueil multilingue affichée dans le hero.
     * Ajoutez ici UNIQUEMENT des traductions validées par des locuteurs, par exemple :
     *   ['lang' => 'gcr', 'label' => 'Créole guyanais', 'text' => '…'],
     * (codes de langue ISO 639 : gcr = créole guyanais, pt = portugais, srn = sranan tongo…)
     */
    'greetings' => [
        ['lang' => 'fr', 'label' => 'Français', 'text' => 'Bienvenue'],
    ],

    // Numéros d'urgence affichés sur l'accueil.
    'emergency' => [
        ['number' => '15', 'label' => 'SAMU', 'description' => 'Urgence médicale'],
        ['number' => '112', 'label' => 'Urgences', 'description' => 'Numéro européen, depuis tout téléphone'],
    ],
];
