<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow;

// Lecture brute d'un fichier : la 1re ligne sert d'en-têtes ("Poids (kg)" devient la clé poids_kg).
// La validation et l'enregistrement sont faits par App\Services\UpcyclingImporter.
class HeadingRowReader implements WithHeadingRow
{
}
