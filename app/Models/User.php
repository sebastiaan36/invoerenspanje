<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * @return HasMany<DossierMessage, $this>
     */
    public function dossierMessages(): HasMany
    {
        return $this->hasMany(DossierMessage::class, 'author_id');
    }

    /**
     * @return HasMany<Document, $this>
     */
    public function reviewedDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'reviewed_by');
    }

    /**
     * Relations that keep a user from being deleted: removing them would break
     * a dossier, its history or a blog article.
     *
     * @return list<string>
     */
    public static function protectingRelations(): array
    {
        return ['dossiers', 'posts', 'dossierMessages', 'reviewedDocuments'];
    }

    /**
     * Why `$actor` may not delete this user, or null when deleting is allowed.
     * Uses `<relation>_exists` attributes when the query loaded them via withExists().
     */
    public function deletionBlockedReason(?User $actor = null): ?string
    {
        if ($actor !== null && $actor->is($this)) {
            return 'Je kunt je eigen account hier niet verwijderen.';
        }

        if ($this->isAdmin() && self::query()->where('role', 'admin')->count() <= 1) {
            return 'Dit is de laatste beheerder; die kan niet verwijderd worden.';
        }

        foreach (self::protectingRelations() as $relation) {
            $loaded = $this->getAttributes()[Str::snake($relation).'_exists'] ?? null;
            $exists = $loaded !== null ? (bool) $loaded : $this->{$relation}()->exists();

            if ($exists) {
                return 'Deze gebruiker heeft dossiers, blogartikelen, berichten of beoordeelde documenten en kan daarom niet verwijderd worden.';
            }
        }

        return null;
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
