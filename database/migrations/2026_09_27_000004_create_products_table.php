<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('unit', 30)->default('kg');
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true); // sold-out toggle
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['farmer_id', 'slug']);
            $table->index(['category_id', 'is_available']);
        });

        Schema::create('pickup_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 (Sun) - 6 (Sat)
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('items'); // [{name, category_id, price, unit, stock_quantity, description}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_templates');
        Schema::dropIfExists('pickup_slots');
        Schema::dropIfExists('products');
    }
};
