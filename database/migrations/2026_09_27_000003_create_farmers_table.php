<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('stall_name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 30)->nullable();
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('operating_days')->nullable();
            $table->json('pickup_windows')->nullable(); // [{day,start,end}]
            $table->unsignedSmallInteger('order_cutoff_hours')->default(24); // hours before pickup that modifications close
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });

        Schema::create('farmer_market', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['farmer_id', 'market_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_market');
        Schema::dropIfExists('farmers');
    }
};
