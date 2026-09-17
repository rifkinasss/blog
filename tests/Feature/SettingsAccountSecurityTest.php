<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Dashboard\Settings\SettingsIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsAccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_update_their_own_name_and_email(): void
    {
        $user = User::create(['name' => 'Before', 'email' => 'before@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('name', 'After')
            ->set('email', 'after@example.test')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'After', 'email' => 'after@example.test']);
    }

    public function test_a_user_cannot_use_an_email_owned_by_someone_else(): void
    {
        $user = User::create(['name' => 'First', 'email' => 'first@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        User::create(['name' => 'Second', 'email' => 'second@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('email', 'second@example.test')
            ->call('saveProfile')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_a_password_change_requires_the_current_password(): void
    {
        $user = User::create(['name' => 'User', 'email' => 'password-check@example.test', 'role' => UserRole::Editor, 'password' => 'current-password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('current_password', 'incorrect-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }

    public function test_a_user_can_change_their_password_without_changing_their_role(): void
    {
        $user = User::create(['name' => 'Editor', 'email' => 'role-check@example.test', 'role' => UserRole::Editor, 'password' => 'current-password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertSame(UserRole::Editor, $user->role);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::create(['name' => 'User', 'email' => 'confirmation@example.test', 'role' => UserRole::Editor, 'password' => 'current-password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['password' => 'confirmed']);
    }

    public function test_guests_cannot_access_settings(): void
    {
        $this->get('/dashboard/settings')->assertRedirect('/login');
    }

    public function test_a_user_can_upload_an_avatar(): void
    {
        Storage::fake('public');
        $user = User::create(['name' => 'Avatar User', 'email' => 'avatar-upload@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('profile_avatar', UploadedFile::fake()->image('avatar.jpg'))
            ->call('uploadAvatar')
            ->assertHasNoErrors();

        $path = $user->fresh()->avatar_path;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('avatars/'.$user->id.'/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_avatar_rejects_invalid_file_types_and_oversized_images(): void
    {
        Storage::fake('public');
        $user = User::create(['name' => 'Avatar User', 'email' => 'avatar-validation@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('profile_avatar', UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'))
            ->call('uploadAvatar')
            ->assertHasErrors(['profile_avatar']);

        Livewire::test(SettingsIndex::class)
            ->set('profile_avatar', UploadedFile::fake()->image('avatar.jpg')->size(2049))
            ->call('uploadAvatar')
            ->assertHasErrors(['profile_avatar']);
    }

    public function test_replacing_an_avatar_removes_only_the_previous_managed_avatar(): void
    {
        Storage::fake('public');
        $user = User::create(['name' => 'Avatar User', 'email' => 'avatar-replace@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $oldPath = 'avatars/'.$user->id.'/old-avatar.jpg';
        Storage::disk('public')->put($oldPath, 'old avatar');
        $user->update(['avatar_path' => $oldPath]);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->set('profile_avatar', UploadedFile::fake()->image('replacement.png'))
            ->call('uploadAvatar')
            ->assertHasNoErrors();

        $newPath = $user->fresh()->avatar_path;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_a_user_can_remove_their_managed_avatar(): void
    {
        Storage::fake('public');
        $user = User::create(['name' => 'Avatar User', 'email' => 'avatar-remove@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $path = 'avatars/'.$user->id.'/avatar.jpg';
        Storage::disk('public')->put($path, 'avatar');
        $user->update(['avatar_path' => $path]);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->call('removeAvatar');

        $this->assertNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_the_account_screen_uses_the_default_avatar_when_no_avatar_exists(): void
    {
        $user = User::create(['name' => 'Avatar User', 'email' => 'avatar-initials@example.test', 'role' => UserRole::Editor, 'password' => 'password']);
        $this->actingAs($user);

        Livewire::test(SettingsIndex::class)
            ->assertSee('default-avatar.svg');

        $this->assertNull($user->avatarUrl());
        $this->assertStringEndsWith('/images/default-avatar.svg', $user->displayAvatarUrl());
    }
}
