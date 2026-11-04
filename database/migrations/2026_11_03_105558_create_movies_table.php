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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_np')->nullable();
            $table->string('slug')->unique();
            $table->text('synopsis');
            $table->text('synopsis_np')->nullable();
            $table->json('genres');
            $table->integer('duration_minutes');
            $table->string('language')->default('Nepali');
            $table->date('release_date');
            $table->string('certificate')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('poster_url');
            $table->string('banner_url')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('director')->nullable();
            $table->string('cast')->nullable();
            $table->boolean('is_now_showing')->default(false);
            $table->boolean('is_coming_soon')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
