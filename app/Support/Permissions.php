<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Rôles et permissions de TexTileCycle (Spatie Permission).
 *
 * Les routes sont protégées par des permissions (middleware "permission:..."), pas par des noms
 * de rôles : l'admin peut ainsi ajuster ce que chaque rôle a le droit de faire depuis le back office.
 */
class Permissions
{
    // Libellés affichés dans le back office
    public const ALL = [
        'admin.access' => 'Accéder au back office',
        'users.manage' => 'Gérer les utilisateurs',
        'roles.manage' => 'Gérer les rôles et permissions',
        'deposits.manage' => 'Gérer les points de collecte et les dépôts',
        'repairs.manage' => 'Gérer les ateliers et les réparations',
        'donations.manage' => 'Gérer les associations et les dons',
        'upcycling.manage' => 'Gérer les lots de matière et les produits',
        'workshop.repairs' => 'Traiter les réparations de son atelier',
        'deposits.create' => 'Déposer des vêtements',
        'repairs.request' => 'Demander une réparation',
    ];

    public const ROLE_LABELS = [
        User::ROLE_ADMIN => 'Administrateur',
        User::ROLE_WORKSHOP_OWNER => 'Propriétaire d’atelier',
        User::ROLE_USER => 'Citoyen',
    ];

    // Permissions par défaut de chaque rôle
    public const DEFAULTS = [
        User::ROLE_ADMIN => '*',
        User::ROLE_WORKSHOP_OWNER => ['workshop.repairs', 'deposits.create'],
        User::ROLE_USER => ['deposits.create', 'repairs.request'],
    ];

    // L'admin ne peut pas se retirer ces droits (sinon plus personne ne peut gérer les accès)
    public const LOCKED_FOR_ADMIN = ['admin.access', 'users.manage', 'roles.manage'];

    /**
     * Crée les rôles et permissions manquants. Avec $resetRolePermissions, remet aussi
     * les permissions par défaut de chaque rôle (installation, seeders, tests).
     */
    public static function sync(bool $resetRolePermissions = true): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::ALL) as $name) {
            Permission::findOrCreate($name, 'web');
        }

        foreach (self::DEFAULTS as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');

            if ($resetRolePermissions || $role->permissions()->doesntExist()) {
                $role->syncPermissions($permissions === '*' ? array_keys(self::ALL) : $permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function roleLabel(?string $role): string
    {
        return self::ROLE_LABELS[$role] ?? ($role ? ucfirst($role) : '—');
    }
}
