<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\DossierMessage;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKlant(): bool
    {
        return $this->role === 'klant';
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Aantal admin-berichten over alle dossiers van deze klant die nog niet gelezen zijn.
     * Wordt gebruikt voor de badge in de klantportaal-sidebar.
     */
    public function unreadAdminMessagesCount(): int
    {
        return DossierMessage::query()
            ->whereIn('dossier_id', $this->dossiers()->select('id'))
            ->where('author_role', 'admin')
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Filament policy: alleen admins krijgen toegang tot het /admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
