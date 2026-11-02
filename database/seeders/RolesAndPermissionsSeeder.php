<?php

namespace Database\Seeders;

use App\Support\Permissions;
use Illuminate\Database\Seeder;

// Crée les rôles et permissions manquants (sans écraser les réglages faits dans le back office).
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        Permissions::sync(resetRolePermissions: false);
    }
}
