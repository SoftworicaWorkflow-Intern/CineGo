<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->date('show_date');
            $table->time('start_time');
            $table->string('format')->default('2D');
            $table->string('language')->nullable();
            $table->decimal('price_normal', 10, 2);
            $table->decimal('price_premium', 10, 2);
            $table->decimal('price_recliner', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['cinema_id', 'show_date']);
            $table->index(['movie_id', 'show_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
