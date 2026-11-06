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
        $hasUnlinkedSlides = DB::table('hero_slides')
            ->leftJoin('movies', 'hero_slides.movie_id', '=', 'movies.id')
            ->whereNull('movies.id')
            ->exists();

        if ($hasUnlinkedSlides) {
            throw new RuntimeException('Every hero slide must be linked to an existing movie before this migration can run.');
        }

        $hasDuplicateMovies = DB::table('hero_slides')
            ->select('movie_id')
            ->groupBy('movie_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateMovies) {
            throw new RuntimeException('Each movie can only have one hero banner before this migration can run.');
        }

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('internal_title')->nullable();
        });

        DB::table('hero_slides')->whereNull('internal_title')->update([
            'internal_title' => DB::raw('movie_title_en'),
        ]);

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn([
                'movie_title_en',
                'movie_title_np',
                'title_en',
                'title_np',
                'description_en',
                'description_np',
                'label_en',
                'label_np',
                'duration',
                'language',
                'release_date',
                'primary_button_text_en',
                'primary_button_text_np',
                'primary_button_url',
                'secondary_button_text_en',
                'secondary_button_text_np',
                'secondary_button_url',
            ]);
        });

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->unique('movie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropUnique(['movie_id']);
        });

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('movie_title_en')->nullable();
            $table->string('movie_title_np')->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_np')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_np')->nullable();
            $table->string('label_en')->nullable();
            $table->string('label_np')->nullable();
            $table->unsignedSmallInteger('duration')->nullable();
            $table->string('language', 40)->nullable();
            $table->date('release_date')->nullable();
            $table->string('primary_button_text_en', 60)->nullable();
            $table->string('primary_button_text_np', 60)->nullable();
            $table->string('primary_button_url', 500)->nullable();
            $table->string('secondary_button_text_en', 60)->nullable();
            $table->string('secondary_button_text_np', 60)->nullable();
            $table->string('secondary_button_url', 500)->nullable();
        });

        foreach (DB::table('hero_slides')->join('movies', 'hero_slides.movie_id', '=', 'movies.id')
            ->select('hero_slides.id', 'movies.title', 'movies.title_np', 'movies.synopsis', 'movies.synopsis_np',
                'movies.duration_minutes', 'movies.language', 'movies.release_date', 'movies.slug')
            ->get() as $slide) {
            DB::table('hero_slides')->where('id', $slide->id)->update([
                'movie_title_en' => $slide->title,
                'movie_title_np' => $slide->title_np,
                'title_en' => $slide->title,
                'title_np' => $slide->title_np,
                'description_en' => $slide->synopsis,
                'description_np' => $slide->synopsis_np,
                'duration' => $slide->duration_minutes,
                'language' => $slide->language,
                'release_date' => $slide->release_date,
                'primary_button_text_en' => 'Book Tickets',
                'primary_button_url' => '/movies/'.$slide->slug,
            ]);
        }

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn('internal_title');
        });
    }
};
