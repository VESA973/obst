<?php

/*
 * Contenus de présentation de l'association (côté front uniquement).
 */

return [
    /*
     * Chiffres clés, affichés sur l'accueil et sur la page « L'association ».
     * `value` doit être un nombre entier (compteur animé) ; `suffix` est facultatif.
     */
    'stats' => [
        ['value' => 15, 'suffix' => ' ans', 'label' => "d'engagement en Guyane", 'on_home' => false],
        ['value' => 8, 'suffix' => '', 'label' => 'actions phares', 'on_home' => true],
        ['value' => 11, 'suffix' => '', 'label' => 'thèmes santé', 'on_home' => true],
        ['value' => 6, 'suffix' => '', 'label' => 'ressources pro', 'on_home' => true],
        ['value' => 1, 'suffix' => '', 'label' => 'territoire guyanais', 'on_home' => true],
    ],

    /*
     * Frise « Notre histoire » : vide tant que les dates n'ont pas été fournies (la frise est alors masquée).
     * Exemple : ['year' => '2010', 'title' => 'Première édition', 'text' => '…'],
     */
    'timeline' => [],
];
