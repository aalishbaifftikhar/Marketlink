<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['farmer_id', 'name', 'items'];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }
}
