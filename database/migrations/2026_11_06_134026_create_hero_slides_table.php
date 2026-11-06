<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->nullable()->constrained()->nullOnDelete();
            $table->string('desktop_image')->nullable();
            $table->string('mobile_image')->nullable();
            $table->string('movie_title_en');
            $table->string('movie_title_np')->nullable();
            $table->string('title_en');
            $table->string('title_np')->nullable();
            $table->text('description_en');
            $table->text('description_np')->nullable();
            $table->string('label_en')->nullable();
            $table->string('label_np')->nullable();
            $table->unsignedSmallInteger('duration')->nullable();
            $table->string('language', 40)->nullable();
            $table->date('release_date')->nullable();
            $table->string('primary_button_text_en', 60);
            $table->string('primary_button_text_np', 60)->nullable();
            $table->string('primary_button_url', 500);
            $table->string('secondary_button_text_en', 60)->nullable();
            $table->string('secondary_button_text_np', 60)->nullable();
            $table->string('secondary_button_url', 500)->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'display_order']);
            $table->index(['starts_at', 'ends_at']);
        });

        if (Schema::hasTable('movies')) {
            $featuredMovies = DB::table('movies')
                ->where('is_featured', true)
                ->where('is_active', true)
                ->where('is_now_showing', true)
                ->orderBy('sort_order')
                ->orderByDesc('release_date')
                ->limit(5)
                ->get();

            foreach ($featuredMovies as $index => $movie) {
                $imagePath = $movie->banner_url;

                if (! is_string($imagePath) || ! str_starts_with($imagePath, '/img/') || str_contains($imagePath, '..') || str_contains($imagePath, '\\')) {
                    $imagePath = $movie->poster_url;
                }

                if (! is_string($imagePath) || ! str_starts_with($imagePath, '/img/') || str_contains($imagePath, '..') || str_contains($imagePath, '\\')) {
                    continue;
                }

                DB::table('hero_slides')->insert([
                    'movie_id' => $movie->id,
                    'desktop_image' => ltrim($imagePath, '/'),
                    'movie_title_en' => $movie->title,
                    'movie_title_np' => $movie->title_np,
                    'title_en' => $movie->title,
                    'title_np' => $movie->title_np,
                    'description_en' => $movie->synopsis,
                    'description_np' => $movie->synopsis_np,
                    'label_en' => 'NOW SHOWING',
                    'label_np' => 'अहिले प्रदर्शनमा',
                    'duration' => $movie->duration_minutes,
                    'language' => $movie->language,
                    'release_date' => $movie->release_date,
                    'primary_button_text_en' => 'Book Tickets',
                    'primary_button_text_np' => 'टिकट बुक गर्नुहोस्',
                    'primary_button_url' => '/movies/'.$movie->slug,
                    'secondary_button_text_en' => 'Explore Movies',
                    'secondary_button_text_np' => 'चलचित्रहरू हेर्नुहोस्',
                    'secondary_button_url' => '/movies',
                    'display_order' => $index,
                    'status' => 'published',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
