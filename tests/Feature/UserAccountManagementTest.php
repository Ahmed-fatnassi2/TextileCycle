<?php

namespace Tests\Feature;

use App\Models\MaterialBatch;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\RecoveryCode;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Gestion des utilisateurs, selon la grille de validation :
 * authentification de base, mot de passe oublié + vérification d'e-mail, rôles et permissions,
 * double authentification (2FA), profil utilisateur.
 */
class UserAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    // =================== 1. Authentification de base (Fortify) ===================

    public function test_registration_creates_a_citizen_and_sends_a_verification_email(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Leila Ben Ali',
            'email' => 'leila@example.test',
            'password' => 'Textile2026',
            'password_confirmation' => 'Textile2026',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'leila@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole(User::ROLE_USER));
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'Inscription']);
    }

    public function test_registration_rejects_weak_passwords_with_clear_messages(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Test',
            'email' => 'test@example.test',
            'password' => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ])->assertSessionHasErrors(['password' => 'Le champ mot de passe doit contenir au moins un chiffre.']);
    }

    public function test_login_with_wrong_password_shows_a_french_error_and_is_throttled(): void
    {
        $user = User::factory()->create(['password' => 'Textile2026']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'mauvais'])
            ->assertSessionHasErrors(['email' => 'Ces identifiants ne correspondent à aucun compte.']);
        $this->assertGuest();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'mauvais']);
        }
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Textile2026'])
            ->assertSessionHasErrors('email');
        $this->assertGuest(); // bloqué malgré le bon mot de passe : 5 tentatives par minute
    }

    public function test_protected_pages_redirect_guests_to_login_and_logout_works(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->get(route('deposits.create'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'Déconnexion']);
    }

    public function test_auth_pages_use_french_urls(): void
    {
        $this->assertSame(url('/connexion'), route('login'));
        $this->assertSame(url('/inscription'), route('register'));
        $this->assertSame(url('/mot-de-passe-oublie'), route('password.request'));

        foreach (['login', 'register', 'password.request'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    // =================== 2. Mot de passe oublié + vérification d'e-mail ===================

    public function test_password_reset_link_is_emailed_and_resets_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'Ancien2026']);

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token, $user) {
            $token = $notification->token;
            $mail = $notification->toMail($user);

            return $mail->subject === 'Réinitialisation de votre mot de passe'
                && str_contains(implode(' ', $mail->outroLines), 'expire dans 60 minutes');
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Nouveau2026',
            'password_confirmation' => 'Nouveau2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Nouveau2026', $user->fresh()->password));
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'Mot de passe réinitialisé']);

        // Jeton à usage unique
        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email, 'password' => 'Autre2026', 'password_confirmation' => 'Autre2026',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_reset_token_expires(): void
    {
        Notification::fake();
        $user = User::factory()->create(['password' => 'Ancien2026']);
        $this->post(route('password.email'), ['email' => $user->email]);

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->travel(61)->minutes();

        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email, 'password' => 'Nouveau2026', 'password_confirmation' => 'Nouveau2026',
        ])->assertSessionHasErrors(['email' => 'Ce lien de réinitialisation est invalide ou a expiré. Demandez-en un nouveau.']);

        $this->assertTrue(Hash::check('Ancien2026', $user->fresh()->password));
    }

    public function test_unverified_users_cannot_use_the_app_until_they_click_the_link(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $this->get(route('deposits.create'))->assertRedirect(route('verification.notice'));
        $this->get(route('verification.notice'))->assertOk()->assertSee($user->email);
        $this->get(route('profile.edit'))->assertOk(); // le profil reste accessible pour corriger l'adresse

        $link = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        $this->get($link)->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'E-mail vérifié']);
    }

    public function test_verification_link_expires_and_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $link = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->travel(61)->minutes();
        $this->actingAs($user)->get($link)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_changing_email_requires_a_new_verification(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('user-profile-information.update'), [
            'name' => $user->name,
            'email' => 'nouvelle@example.test',
        ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }

    // =================== 3. Rôles et permissions (Spatie) ===================

    public function test_each_role_only_reaches_its_own_space(): void
    {
        $citizen = User::factory()->create();
        $owner = User::factory()->workshopOwner()->create();
        $admin = User::factory()->admin()->create();

        // Citoyen
        $this->actingAs($citizen)->get(route('admin.dashboard'))->assertForbidden()->assertSee('Accès refusé');
        $this->get(route('workshop-owner.dashboard'))->assertForbidden();
        $this->get(route('repairs.create'))->assertOk();

        // Propriétaire d'atelier : son espace, mais pas de demande de réparation ni de back office
        $this->actingAs($owner)->get(route('workshop-owner.dashboard'))->assertOk();
        $this->get(route('repairs.create'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();

        // Administrateur
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk();
    }

    public function test_permissions_are_checked_on_the_server_per_module(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo(['admin.access', 'deposits.manage']); // gestionnaire de collecte uniquement

        $this->actingAs($manager)->get(route('admin.deposits.index'))->assertOk();
        $this->get(route('admin.material-batches.index'))->assertForbidden();
        $this->post(route('admin.material-batches.store'), ['material_type' => 'Jean', 'weight' => 5, 'quality_grade' => 'A'])
            ->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->assertDatabaseCount('material_batches', 0);

        // Le menu n'affiche que les modules autorisés
        $this->get(route('admin.dashboard'))
            ->assertSee(route('admin.deposits.index'))
            ->assertDontSee(route('admin.material-batches.index'));
    }

    public function test_admin_edits_the_role_permission_matrix_but_keeps_locked_rights(): void
    {
        $admin = User::factory()->admin()->create();
        $citizen = User::factory()->create();

        $this->actingAs($citizen)->get(route('deposits.create'))->assertOk();

        // Le citoyen perd le droit de déposer ; l'admin décoche tout pour lui-même
        $this->actingAs($admin)->put(route('admin.roles.update'), [
            'permissions' => [
                User::ROLE_USER => ['repairs.request'],
                User::ROLE_WORKSHOP_OWNER => ['workshop.repairs'],
                User::ROLE_ADMIN => [],
            ],
        ])->assertSessionHas('success');

        $this->actingAs($citizen->fresh())->get(route('deposits.create'))->assertForbidden();

        $adminRole = Role::findByName(User::ROLE_ADMIN);
        $this->assertTrue($adminRole->hasPermissionTo('admin.access'));
        $this->assertTrue($adminRole->hasPermissionTo('users.manage'));
        $this->assertTrue($adminRole->hasPermissionTo('roles.manage'));
        $this->assertFalse($adminRole->hasPermissionTo('upcycling.manage'));

        $this->actingAs($citizen)->put(route('admin.roles.update'), ['permissions' => []])->assertForbidden();
    }

    public function test_a_user_has_exactly_one_role_stored_by_spatie(): void
    {
        $user = User::factory()->create();
        $user->update(['role' => User::ROLE_WORKSHOP_OWNER]);

        $this->assertSame([User::ROLE_WORKSHOP_OWNER], $user->fresh()->getRoleNames()->all());
        $this->assertSame('Propriétaire d’atelier', $user->fresh()->roleLabel());
        $this->assertSame([$user->id], User::role(User::ROLE_WORKSHOP_OWNER)->pluck('id')->all());
    }

    // =================== 4. Double authentification (TOTP) ===================

    public function test_managing_two_factor_requires_password_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'Textile2026']);
        $this->actingAs($user);

        $this->get(route('profile.edit'))->assertSee('Activer la double authentification')->assertDontSee('<svg', false);
        $this->get(route('profile.security'))->assertRedirect(route('password.confirm'));
        $this->post(route('two-factor.enable'))->assertRedirect(route('password.confirm'));

        $this->post(route('password.confirm.store'), ['password' => 'Textile2026']);

        // Une fois confirmé : on arrive sur la section sécurité, sans redemander le mot de passe
        $this->get(route('profile.security'))->assertRedirect(route('profile.edit').'#securite');
        $this->get(route('profile.edit'))->assertSee(route('two-factor.enable'), false);
    }

    public function test_user_enables_two_factor_with_qr_code_and_recovery_codes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

        $this->post(route('two-factor.enable'))->assertSessionHas('status', 'two-factor-authentication-enabled');
        $user->refresh();

        // QR code + clé affichés, pas encore actif tant qu'un code n'est pas confirmé
        $this->get(route('profile.edit'))->assertSee('<svg', false)->assertSee(decrypt($user->two_factor_secret));
        $this->assertFalse($user->hasTwoFactorEnabled());

        $this->post(route('two-factor.confirm'), ['code' => '000000'])
            ->assertSessionHasErrors('code', null, 'confirmTwoFactorAuthentication');

        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
        $this->post(route('two-factor.confirm'), ['code' => $code])
            ->assertSessionHas('status', 'two-factor-authentication-confirmed');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertCount(8, $user->recoveryCodes());
        $this->get(route('profile.edit'))->assertSee($user->recoveryCodes()[0]);
    }

    public function test_login_with_two_factor_asks_for_a_code_and_accepts_recovery_codes_once(): void
    {
        $user = $this->userWithTwoFactor();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Textile2026'])
            ->assertRedirect(route('two-factor.login'));
        $this->assertGuest(); // le mot de passe seul ne suffit pas

        $this->post(route('two-factor.login.store'), ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertGuest();

        $recovery = $user->recoveryCodes()[0];
        $this->post(route('two-factor.login.store'), ['recovery_code' => $recovery])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($recovery, $user->fresh()->recoveryCodes()); // usage unique

        // Avec le code de l'application, et redirection selon le rôle
        $this->post(route('logout'));
        $user->update(['role' => User::ROLE_ADMIN]);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Textile2026']);
        $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->two_factor_secret));
        $this->post(route('two-factor.login.store'), ['code' => $code])->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_can_regenerate_recovery_codes_and_disable_two_factor(): void
    {
        $user = $this->userWithTwoFactor();
        $old = $user->recoveryCodes();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

        $this->post(route('two-factor.regenerate-recovery-codes'))->assertSessionHas('status', 'recovery-codes-generated');
        $this->assertNotEquals($old, $user->fresh()->recoveryCodes());

        $this->delete(route('two-factor.disable'))->assertSessionHas('status', 'two-factor-authentication-disabled');
        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    // =================== 5. Profil utilisateur ===================

    public function test_user_updates_profile_information_and_password(): void
    {
        $user = User::factory()->create(['password' => 'Textile2026']);
        $this->actingAs($user)->from(route('profile.edit'));

        $this->put(route('user-profile-information.update'), ['name' => 'Nom Modifié', 'email' => $user->email])
            ->assertRedirect(route('profile.edit'));
        $this->assertSame('Nom Modifié', $user->fresh()->name);

        $this->put(route('user-password.update'), [
            'current_password' => 'faux', 'password' => 'Nouveau2026', 'password_confirmation' => 'Nouveau2026',
        ])->assertSessionHasErrors(['current_password' => 'Le mot de passe actuel est incorrect.'], null, 'updatePassword');

        $this->put(route('user-password.update'), [
            'current_password' => 'Textile2026', 'password' => 'Nouveau2026', 'password_confirmation' => 'Nouveau2026',
        ])->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('Nouveau2026', $user->fresh()->password));
        $this->assertDatabaseHas('user_activities', ['user_id' => $user->id, 'action' => 'Mot de passe modifié']);
    }

    public function test_user_uploads_and_deletes_a_profile_photo(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('photo', null, 'updatePhoto');

        $this->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('moi.jpg', 300, 300)])
            ->assertRedirect(route('profile.edit'));

        $path = $user->fresh()->profile_photo_path;
        Storage::disk('local')->assertExists($path);
        $this->get(route('profile.photo', $user))->assertOk();
        $this->get(route('profile.edit'))->assertSee($user->fresh()->profilePhotoUrl(), false);

        $this->delete(route('profile.photo.destroy'));
        Storage::disk('local')->assertMissing($path);
        $this->assertNull($user->fresh()->profile_photo_path);
    }

    public function test_user_deletes_their_account_with_password_confirmation(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['password' => 'Textile2026']);
        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->image('moi.jpg')]);
        $photo = $user->fresh()->profile_photo_path;

        $this->delete(route('profile.destroy'), ['password' => 'faux'])
            ->assertSessionHasErrors('password', null, 'deleteUser');
        $this->assertModelExists($user);

        $this->delete(route('profile.destroy'), ['password' => 'Textile2026'])->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertModelMissing($user);
        Storage::disk('local')->assertMissing($photo);
    }

    public function test_the_last_administrator_cannot_delete_their_account(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Textile2026']);

        $this->actingAs($admin)->delete(route('profile.destroy'), ['password' => 'Textile2026'])
            ->assertSessionHasErrors('password', null, 'deleteUser');
        $this->assertModelExists($admin);

        User::factory()->admin()->create();
        $this->delete(route('profile.destroy'), ['password' => 'Textile2026']);
        $this->assertModelMissing($admin);
    }

    // =================== Outils ===================

    private function userWithTwoFactor(): User
    {
        $user = User::factory()->create(['password' => 'Textile2026']);

        $user->forceFill([
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(fn () => RecoveryCode::generate())->all())),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return $user->fresh();
    }
}
