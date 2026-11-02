<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Les rôles passent de la colonne users.role à Spatie Permission (tables roles / permissions).
 * Les rôles existants sont repris, puis la colonne est supprimée : Spatie devient la seule source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Permissions::sync();

        $roleIds = DB::table('roles')->pluck('id', 'name');
        DB::table('users')->orderBy('id')->each(function ($user) use ($roleIds) {
            // "member" était l'ancienne valeur par défaut du rôle citoyen
            $name = $roleIds->has($user->role) ? $user->role : 'user';

            DB::table('model_has_roles')->insertOrIgnore([
                'role_id' => $roleIds[$name],
                'model_type' => 'App\Models\User',
                'model_id' => $user->id,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
        });

        DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->orderBy('model_has_roles.model_id')
            ->get(['model_has_roles.model_id', 'roles.name'])
            ->each(fn ($row) => DB::table('users')->where('id', $row->model_id)->update(['role' => $row->name]));
    }
};
