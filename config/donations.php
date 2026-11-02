<?php

// Capacité logistique des livraisons de dons (modifiable dans .env)
return [
    // Nombre maximum de livraisons par jour
    'daily_deliveries' => (int) env('DONATION_DAILY_DELIVERIES', 3),

    // Nombre maximum de pièces livrées par jour
    'daily_items' => (int) env('DONATION_DAILY_ITEMS', 300),

    // Assistant de planning IA : nombre de jours à venir qu'il peut réorganiser
    'planning_horizon_days' => (int) env('DONATION_PLANNING_HORIZON_DAYS', 14),

    // Nombre maximum de déplacements proposés en une fois
    'planning_max_proposals' => 8,
];
