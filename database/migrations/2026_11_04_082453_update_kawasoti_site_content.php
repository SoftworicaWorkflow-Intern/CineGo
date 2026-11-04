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
            ->where('title', 'Kathmandu Nights')
            ->update([
                'title' => 'Kawasoti Nights',
                'title_np' => 'कावासोती नाइट्स',
                'slug' => 'kawasoti-nights',
                'synopsis' => 'A relentless crime reporter and a street-smart detective race through Kawasoti\'s neon alleys to expose a smuggling ring before the city wakes up.',
                'synopsis_np' => 'संवाददाता र पुलिस अधिकारी कावासोतीका नियोन गल्लीहरूमा शहर जाग्नुअघि तस्करी गिरोह पर्दाफास गर्न दौडिन्छन्।',
            ]);

        DB::table('announcements')
            ->where('message', '🎬 Kathmandu Nights & Shikhar now playing at a screen near you')
            ->update([
                'message' => '🎬 Kawasoti Nights & Shikhar now playing at a screen near you',
                'message_np' => '🎬 कावासोती नाइट्स र शिखर तपाईंको नजिकैको स्क्रिनमा',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('movies')
            ->where('title', 'Kawasoti Nights')
            ->update([
                'title' => 'Kathmandu Nights',
                'title_np' => 'काठमाडौँ नाइट्स',
                'slug' => 'kathmandu-nights',
                'synopsis' => 'A relentless crime reporter and a street-smart detective race through Kathmandu\'s neon alleys to expose a smuggling ring before the city wakes up.',
                'synopsis_np' => 'संवाददाता र पुलिस अधिकारी काठमाडौँको नियोन गल्लीहरूमा शहर जाग्नुअघि तस्करी गिरोह पर्दाफास गर्न दौडिन्छन्।',
            ]);

        DB::table('announcements')
            ->where('message', '🎬 Kawasoti Nights & Shikhar now playing at a screen near you')
            ->update([
                'message' => '🎬 Kathmandu Nights & Shikhar now playing at a screen near you',
                'message_np' => '🎬 काठमाडौँ नाइट्स र शिखर तपाईंको नजिकैको स्क्रिनमा',
            ]);
    }
};
