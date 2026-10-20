<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\HasMany;
=======
>>>>>>> origin/Donation/Association-"aziz"
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
<<<<<<< HEAD
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_WORKSHOP_OWNER = 'workshop_owner';

=======
>>>>>>> origin/Donation/Association-"aziz"
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
<<<<<<< HEAD
        'last_login_at',
=======
>>>>>>> origin/Donation/Association-"aziz"
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'password' => 'hashed',
        ];
    }

    public function association(): HasOne
    {
        return $this->hasOne(Association::class);
    }

<<<<<<< HEAD
    public function ownedWorkshops(): HasMany
    {
        return $this->hasMany(Workshop::class, 'owner_id');
    }

=======
>>>>>>> origin/Donation/Association-"aziz"
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
