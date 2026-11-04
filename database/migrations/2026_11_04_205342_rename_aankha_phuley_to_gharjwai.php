<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('movies')
            ->where('slug', 'aankha-phuley')
            ->update([
                'title' => 'Gharjwai',
                'title_np' => 'घरज्वाइँ',
                'slug' => 'gharjwai',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('movies')
            ->where('slug', 'gharjwai')
            ->update([
                'title' => 'Aankha Phuley',
                'title_np' => 'आँखा फुल्यो',
                'slug' => 'aankha-phuley',
            ]);
    }
};
