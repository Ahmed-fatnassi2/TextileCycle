<?php

namespace App\Models;

use App\Support\Permissions;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_WORKSHOP_OWNER = 'workshop_owner';

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // Rôle demandé via $user->role = '...' / create(['role' => ...]), appliqué après l'enregistrement
    private ?string $pendingRole = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if ($user->pendingRole !== null) {
                $role = $user->pendingRole;
                $user->pendingRole = null;
                $user->syncRoles([$role]);
            } elseif ($user->wasRecentlyCreated && $user->roles()->doesntExist()) {
                // Tout nouveau compte est citoyen par défaut
                $user->assignRole(self::ROLE_USER);
            }
        });

        static::deleting(function (User $user) {
            if ($user->profile_photo_path) {
                Storage::disk('local')->delete($user->profile_photo_path);
            }
        });
    }

    /**
     * Rôle principal. Les rôles sont gérés par Spatie Permission (tables roles / model_has_roles) :
     * cet attribut garde la syntaxe simple $user->role pour le reste de l'application.
     *
     * Accesseurs "getXAttribute" volontairement : une méthode role() masquerait le filtre
     * Spatie User::role('admin').
     */
    public function getRoleAttribute(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function setRoleAttribute(?string $value): void
    {
        // Aucune colonne "role" en base : le rôle est appliqué par syncRoles() après l'enregistrement
        $this->pendingRole = $value;
    }

    public function roleLabel(): string
    {
        return Permissions::roleLabel($this->role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    // La photo est servie par une route protégée (pas besoin de "storage:link", fonctionne aussi en conteneur)
    public function profilePhotoUrl(): ?string
    {
        return $this->profile_photo_path
            ? route('profile.photo', ['user' => $this, 'v' => $this->updated_at?->timestamp])
            : null;
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 1));
    }

    public function association(): HasOne
    {
        return $this->hasOne(Association::class);
    }

    public function ownedWorkshops(): HasMany
    {
        return $this->hasMany(Workshop::class, 'owner_id');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public static function logActivity(self $user, string $action, string $description, ?self $actor = null, array $metadata = []): void
    {
        UserActivity::create([
            'user_id' => $user->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }
}
