<?php

namespace App\Models;

use App\Notifications\RestockNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id', 'category_id', 'name', 'slug', 'description', 'price',
        'unit', 'stock_quantity', 'image', 'images', 'is_available', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'images' => 'array',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updated(function (Product $product) {
            if (! $product->wasChanged(['is_available', 'stock_quantity', 'is_active'])) {
                return;
            }

            $wasOrderable = $product->getOriginal('is_available') && $product->getOriginal('is_active') && $product->getOriginal('stock_quantity') > 0;
            $isOrderable = $product->is_available && $product->is_active && $product->stock_quantity > 0;

            if (! $wasOrderable && $isOrderable) {
                RestockNotification::notifyFavoriters($product);
            }
        });
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function visibleReviews(): HasMany
    {
        return $this->reviews()->where('is_hidden', false)->latest();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('is_available', true)
            ->where('stock_quantity', '>', 0)
            ->whereHas('farmer.user', fn ($q) => $q->where('status', 'active'));
    }

    public function imageUrl(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return $this->galleryImages()[0] ?? asset('images/product-placeholder.svg');
    }

    /**
     * All gallery paths: the new multi-image array plus the legacy single
     * image column, deduplicated.
     */
    public function galleryPaths(): array
    {
        return array_values(array_unique(array_merge(
            (array) ($this->images ?? []),
            $this->image ? [$this->image] : []
        )));
    }

    public function galleryImages(): array
    {
        return array_map(fn ($path) => asset('storage/' . $path), $this->galleryPaths());
    }

    public function galleryEntries(): array
    {
        return array_map(fn ($path) => ['path' => $path, 'url' => asset('storage/' . $path)], $this->galleryPaths());
    }

    public function averageRating(): float
    {
        return round($this->reviews()->where('is_hidden', false)->avg('rating') ?? 0, 1);
    }

    public function totalReviews(): int
    {
        return $this->reviews()->where('is_hidden', false)->count();
    }

    public function inStock(): bool
    {
        return $this->is_available && $this->stock_quantity > 0;
    }
}
