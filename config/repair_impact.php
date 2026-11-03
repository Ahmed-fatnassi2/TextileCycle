<?php

return [
    // Estimations indicatives par article neuf, à adapter aux données locales.
    'replacement_share' => (float) env('REPAIR_REPLACEMENT_SHARE', 0.5),
    'factors' => [
        'haut' => ['co2' => 7, 'waste' => 0.2], 'pantalon' => ['co2' => 30, 'waste' => 0.6],
        'robe' => ['co2' => 15, 'waste' => 0.4], 'veste' => ['co2' => 25, 'waste' => 0.9],
        'linge_maison' => ['co2' => 10, 'waste' => 0.7], 'autre' => ['co2' => 8, 'waste' => 0.4],
    ],
];
