<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'avatar_path', 'role', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'role' => UserRole::class];
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function isAdministrator(): bool
    {
        return $this->role === UserRole::Administrator;
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path && Storage::disk('public')->exists($this->avatar_path)
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    public function displayAvatarUrl(): string
    {
        return $this->avatarUrl() ?? asset('images/default-avatar.svg');
    }

    public function ownsAvatarPath(?string $path = null): bool
    {
        return is_string($path) && str_starts_with($path, 'avatars/'.$this->id.'/');
    }
}
