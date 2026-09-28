<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['placed', 'accepted', 'ready_for_pickup', 'completed', 'cancelled', 'declined'];

    protected $fillable = [
        'order_number', 'customer_id', 'farmer_id', 'market_id', 'status',
        'pickup_date', 'pickup_slot', 'subtotal', 'total_amount',
        'customer_notes', 'farmer_notes', 'cutoff_at', 'placed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'subtotal' => 'float',
            'total_amount' => 'float',
            'cutoff_at' => 'datetime',
            'placed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ML-' . now()->format('Y') . '-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['placed', 'accepted', 'ready_for_pickup']);
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'cancelled', 'declined']);
    }

    public function canBeModified(): bool
    {
        return in_array($this->status, ['placed', 'accepted'])
            && (! $this->cutoff_at || now()->lt($this->cutoff_at));
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['placed', 'accepted'])
            && (! $this->cutoff_at || now()->lt($this->cutoff_at));
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'completed'
            && ! $this->reviews()->where('user_id', $this->customer_id)->exists();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'placed' => 'Placed',
            'accepted' => 'Accepted',
            'ready_for_pickup' => 'Ready for Pickup',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'declined' => 'Declined',
            default => ucfirst($this->status),
        };
    }

    public function isPendingAction(): bool
    {
        return $this->status === 'placed';
    }
}
