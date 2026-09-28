<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerEntry extends Model
{
    protected $fillable = [
        'farmer_id', 'product_name', 'quantity', 'cost_price', 'selling_price', 'entry_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'cost_price' => 'float',
            'selling_price' => 'float',
            'entry_date' => 'date',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function totalCost(): float
    {
        return round($this->cost_price * $this->quantity, 2);
    }

    public function totalSales(): float
    {
        return round($this->selling_price * $this->quantity, 2);
    }

    public function profit(): float
    {
        return round($this->totalSales() - $this->totalCost(), 2);
    }
}
