<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** The one and only email allowed to hold the admin role. */
    public const ADMIN_EMAIL = 'admin@gmail.com';

    protected static function booted(): void
    {
        // Nobody except ADMIN_EMAIL can ever be saved with the admin role.
        static::saving(function (User $user) {
            if ($user->role === 'admin' && strtolower((string) $user->email) !== self::ADMIN_EMAIL) {
                throw new \RuntimeException('Only ' . self::ADMIN_EMAIL . ' can be an admin.');
            }
            if ($user->email && strtolower($user->email) === self::ADMIN_EMAIL && $user->role !== 'admin') {
                throw new \RuntimeException(self::ADMIN_EMAIL . ' is reserved for the admin account.');
            }
        });
    }

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address', 'avatar',
        'status', 'google_id', 'google_token', 'google_refresh_token',
    ];

    protected $hidden = ['password', 'remember_token', 'google_token', 'google_refresh_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function farmer(): HasOne
    {
        return $this->hasOne(Farmer::class);
    }

    public function farmers(): HasOne
    {
        return $this->hasOne(Farmer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteFarmers(): HasMany
    {
        return $this->hasMany(Favorite::class)->where('favoritable_type', 'farmer');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' && strtolower((string) $this->email) === self::ADMIN_EMAIL;
    }

    public function isFarmer(): bool
    {
        return $this->role === 'farmer';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function dashboardRoute(): string
    {
        if ($this->role === 'farmer' && $this->status === 'pending') {
            return route('farmer.pending');
        }

        return match ($this->role) {
            'admin' => route('admin.dashboard'),
            'farmer' => route('farmer.dashboard'),
            default => route('customer.dashboard'),
        };
    }

    public function hasFavorited(string $type, int $id): bool
    {
        return $this->favorites()
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->exists();
    }

    public function toggleFavorite(string $type, int $id): bool
    {
        $favorite = $this->favorites()
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return false;
        }

        $this->favorites()->create(['favoritable_type' => $type, 'favoritable_id' => $id]);
        return true;
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=059669&color=fff&bold=true';
    }
}
