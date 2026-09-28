<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('cost_price', 12, 2);   // kharida (per unit)
            $table->decimal('selling_price', 12, 2); // becha (per unit)
            $table->date('entry_date');
            $table->timestamps();
            $table->index(['farmer_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_entries');
    }
};
