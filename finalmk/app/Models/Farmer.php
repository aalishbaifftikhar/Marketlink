<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'stall_name', 'contact_person', 'phone', 'description', 'address',
        'latitude', 'longitude', 'operating_days', 'pickup_windows',
        'order_cutoff_hours', 'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'operating_days' => 'array',
            'pickup_windows' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markets(): BelongsToMany
    {
        return $this->belongsToMany(Market::class, 'farmer_market')->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function pickupSlots(): HasMany
    {
        return $this->hasMany(PickupSlot::class);
    }

    public function stockTemplates(): HasMany
    {
        return $this->hasMany(StockTemplate::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function entries(): HasMany
{
    return $this->hasMany(FarmerEntry::class);
}

public function latestEntries(): HasMany
{
    return $this->entries()->orderByDesc('entry_date')->orderByDesc('id');
}

    public function visibleReviews(): HasMany
    {
        return $this->reviews()->where('is_hidden', false)->latest();
    }

    public function averageRating(): float
    {
        return round($this->reviews()->where('is_hidden', false)->avg('rating') ?? 0, 1);
    }

    public function totalReviews(): int
    {
        return $this->reviews()->where('is_hidden', false)->count();
    }

    public function isApproved(): bool
    {
        return $this->user?->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->user?->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->user?->status === 'suspended';
    }

    public function scopePending($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('status', 'pending'));
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('status', 'active'));
    }

    public function scopeApproved($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('status', 'active'));
    }

    public function scopeSuspended($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('status', 'suspended'));
    }

    public function availableProducts()
    {
        return $this->products()->where('is_active', true)->where('is_available', true)->where('stock_quantity', '>', 0);
    }

    public function operatingDaysLabel(): string
    {
        $days = $this->operating_days ?? [];
        if (! is_array($days) || empty($days)) {
            return 'Days vary';
        }

        $map = [
            'monday' => 'Mon', 'tuesday' => 'Tue', 'wednesday' => 'Wed',
            'thursday' => 'Thu', 'friday' => 'Fri', 'saturday' => 'Sat', 'sunday' => 'Sun',
        ];

        return implode(', ', array_map(fn ($d) => $map[strtolower($d)] ?? ucfirst($d), $days));
    }

    public function route(): string
    {
        return route('farmers.show', $this);
    }
}
