<?php

namespace App\Livewire\Dashboard\Users;

use App\Enums\UserRole;
use App\Models\ApiToken;
use App\Models\AuditLog;
use App\Models\ServiceAccount;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class UserIndex extends Component
{
    private const DEFAULT_SERVICE_ABILITIES = [
        'articles:create',
        'articles:read',
        'articles:update',
        'articles:preview',
        'media:upload',
        'review:request',
    ];

    private const SERVICE_ABILITIES = [
        ...self::DEFAULT_SERVICE_ABILITIES,
        'articles:schedule',
        'articles:publish',
    ];

    public string $name = '';

    public string $email = '';

    public string $role = 'editor';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $editingUserId = null;

    public string $editingRole = 'editor';

    public string $serviceName = 'OpenClaw';

    public string $serviceDescription = 'Automated content publishing and media upload.';

    public mixed $serviceContentUserId = null;

    public array $serviceAbilities = self::DEFAULT_SERVICE_ABILITIES;

    public ?int $selectedServiceAccountId = null;

    public ?int $tokenServiceAccountId = null;

    public string $tokenName = 'OpenClaw API token';

    public string $tokenExpiresAt = '';

    public ?string $generatedToken = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdministrator(), 403);
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        $this->role = UserRole::Editor->value;
        $this->resetValidation();
        Flux::modal('user-form')->show();
    }

    public function createUser(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($data);
        AuditLog::record('user.created', $user, ['email' => $user->email, 'role' => $user->role->value]);

        Flux::modal('user-form')->close();
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        session()->flash('success', 'Human account created.');
    }

    public function openRoleEditor(User $user): void
    {
        $this->editingUserId = $user->id;
        $this->editingRole = $user->role->value;
        $this->resetValidation();
        Flux::modal('user-role-form')->show();
    }

    public function updateRole(): void
    {
        $this->validate(['editingRole' => ['required', Rule::enum(UserRole::class)]]);

        $user = User::findOrFail($this->editingUserId);
        $newRole = UserRole::from($this->editingRole);

        if ($user->is(auth()->user()) && $newRole !== UserRole::Administrator) {
            $this->addError('editingRole', 'You cannot remove your own administrator access.');

            return;
        }

        if ($user->isAdministrator() && $newRole !== UserRole::Administrator && User::query()->where('role', UserRole::Administrator)->count() === 1) {
            $this->addError('editingRole', 'Keep at least one administrator account.');

            return;
        }

        $user->update(['role' => $newRole]);
        AuditLog::record('user.role_updated', $user, ['role' => $newRole->value]);

        Flux::modal('user-role-form')->close();
        session()->flash('success', 'User role updated.');
    }

    public function resetPassword(User $user): void
    {
        abort_if($user->is(auth()->user()), 422, 'Use Settings to change your own password.');

        $user->update(['password' => 'ChangeMe123!']);
        AuditLog::record('user.password_reset', $user);
        session()->flash('success', "Password for {$user->email} was reset. Ask them to change it immediately.");
    }

    public function deleteUser(User $user): void
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        abort_if($user->isAdministrator(), 422, 'Administrator accounts cannot be deleted here.');

        AuditLog::record('user.deleted', $user, ['email' => $user->email]);
        $user->delete();
        session()->flash('success', 'Human account deleted.');
    }

    public function openCreateService(): void
    {
        $this->serviceName = 'OpenClaw';
        $this->serviceDescription = 'Automated content publishing and media upload.';
        $this->serviceContentUserId = auth()->id();
        $this->serviceAbilities = self::DEFAULT_SERVICE_ABILITIES;
        $this->resetValidation();
        Flux::modal('service-form')->show();
    }

    public function createService(): void
    {
        $data = $this->validate([
            'serviceName' => ['required', 'string', 'max:120', Rule::unique('service_accounts', 'name')],
            'serviceDescription' => ['required', 'string', 'max:300'],
            'serviceContentUserId' => ['required', 'exists:users,id'],
        ]);

        $service = ServiceAccount::create([
            'name' => $data['serviceName'],
            'slug' => Str::slug($data['serviceName']),
            'description' => $data['serviceDescription'],
            'content_user_id' => $data['serviceContentUserId'],
        ]);
        $this->selectedServiceAccountId = $service->id;
        AuditLog::record('service_account.created', $service, ['name' => $service->name]);

        Flux::modal('service-form')->close();
        session()->flash('success', 'Service account created. Generate a token to connect it.');
    }

    public function openServiceDetail(ServiceAccount $service): void
    {
        $this->selectedServiceAccountId = $service->id;
        $this->generatedToken = null;
        Flux::modal('service-detail')->show();
    }

    public function openTokenCreator(ServiceAccount $service): void
    {
        $this->tokenServiceAccountId = $service->id;
        $this->tokenName = $service->name.' API token';
        $this->tokenExpiresAt = '';
        $this->serviceAbilities = self::DEFAULT_SERVICE_ABILITIES;
        $this->generatedToken = null;
        $this->resetValidation();
        Flux::modal('api-token-form')->show();
    }

    public function createServiceToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:80'],
            'tokenExpiresAt' => ['nullable', 'date', 'after:today'],
            'serviceAbilities' => ['required', 'array', 'min:1'],
            'serviceAbilities.*' => [Rule::in(self::SERVICE_ABILITIES)],
        ]);

        $service = ServiceAccount::query()->with('contentUser')->findOrFail($this->tokenServiceAccountId);
        abort_unless($service->enabled, 422, 'Enable this service account before generating a token.');

        $this->generatedToken = $this->issueToken($service, $this->tokenName, $this->serviceAbilities, $this->tokenExpiresAt ?: null);
        AuditLog::record('api_token.created', $service, ['name' => $this->tokenName, 'service_account_id' => $service->id]);
    }

    public function regenerateToken(ApiToken $token): void
    {
        abort_unless($token->service_account_id, 422, 'Only service account tokens can be regenerated here.');
        $service = ServiceAccount::query()->with('contentUser')->findOrFail($token->service_account_id);
        abort_unless($service->enabled, 422, 'Enable this service account before regenerating a token.');

        $plainToken = $this->plainToken();
        $token->update([
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => $this->tokenPrefix($plainToken),
            'last_used_at' => null,
        ]);
        $this->generatedToken = $plainToken;
        AuditLog::record('api_token.regenerated', $service, ['name' => $token->name, 'service_account_id' => $service->id]);
        Flux::modal('api-token-form')->show();
    }

    public function revokeToken(ApiToken $token): void
    {
        abort_unless($token->service_account_id, 422, 'Only service account tokens can be revoked here.');
        $service = $token->serviceAccount;
        $token->delete();
        AuditLog::record('api_token.revoked', $service, ['name' => $token->name, 'service_account_id' => $service->id]);
        session()->flash('success', 'Service token revoked.');
    }

    public function toggleService(ServiceAccount $service): void
    {
        $service->update(['enabled' => ! $service->enabled]);
        AuditLog::record($service->enabled ? 'service_account.enabled' : 'service_account.disabled', $service, ['service_account_id' => $service->id]);
        session()->flash('success', $service->enabled ? 'Service account enabled.' : 'Service account disabled. Its tokens can no longer authenticate.');
    }

    public function render()
    {
        $serviceAccounts = ServiceAccount::query()
            ->with(['contentUser', 'apiTokens'])
            ->withCount('apiTokens')
            ->withMax('apiTokens as last_used_at', 'last_used_at')
            ->orderBy('name')
            ->get();
        $selectedService = $this->selectedServiceAccountId
            ? ServiceAccount::query()->with(['contentUser', 'apiTokens' => fn ($query) => $query->latest()])->find($this->selectedServiceAccountId)
            : null;

        return view('livewire.dashboard.users.index', [
            'users' => User::query()
                ->withCount('articles')
                ->addSelect(['last_activity_at' => AuditLog::query()->select('created_at')->whereColumn('user_id', 'users.id')->latest()->limit(1)])
                ->orderBy('name')
                ->get(),
            'roles' => UserRole::cases(),
            'serviceAccounts' => $serviceAccounts,
            'humanAccountCount' => User::count(),
            'activeTokenCount' => ApiToken::query()->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))->where(fn (Builder $query) => $query->whereNull('service_account_id')->orWhereHas('serviceAccount', fn (Builder $query) => $query->where('enabled', true)))->count(),
            'disabledServiceCount' => ServiceAccount::query()->where('enabled', false)->count(),
            'selectedService' => $selectedService,
            'serviceActivity' => $selectedService ? AuditLog::query()->where('properties->service_account_id', $selectedService->id)->latest()->limit(8)->get() : collect(),
            'serviceAbilities' => self::SERVICE_ABILITIES,
        ])->layout('layouts.dashboard');
    }

    private function issueToken(ServiceAccount $service, string $name, array $abilities, ?string $expiresAt): string
    {
        $plainToken = $this->plainToken();

        ApiToken::create([
            'user_id' => $service->content_user_id,
            'service_account_id' => $service->id,
            'name' => $name,
            'token_hash' => hash('sha256', $plainToken),
            'token_prefix' => $this->tokenPrefix($plainToken),
            'abilities' => array_values($abilities),
            'expires_at' => $expiresAt,
        ]);

        return $plainToken;
    }

    private function plainToken(): string
    {
        return 'nbl_'.Str::random(48);
    }

    private function tokenPrefix(string $token): string
    {
        return Str::substr($token, 0, 12).'…';
    }
}
