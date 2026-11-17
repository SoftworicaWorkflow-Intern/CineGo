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
        DB::table('seats')
            ->whereIn('seat_type', ['premium', 'recliner'])
            ->update(['seat_type' => 'normal']);

        Schema::table('showtimes', function (Blueprint $table): void {
            $table->dropColumn(['price_premium', 'price_recliner']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showtimes', function (Blueprint $table): void {
            $table->decimal('price_premium', 10, 2)->default(0)->after('price_normal');
            $table->decimal('price_recliner', 10, 2)->default(0)->after('price_premium');
        });

        DB::table('showtimes')->update([
            'price_premium' => DB::raw('price_normal'),
            'price_recliner' => DB::raw('price_normal'),
        ]);

        $seats = DB::table('seats')
            ->join('halls', 'seats.hall_id', '=', 'halls.id')
            ->select('seats.id', 'seats.row_label', 'halls.total_rows')
            ->get();

        foreach ($seats as $seat) {
            $rowIndex = ord($seat->row_label) - ord('A');
            $seatType = match ($rowIndex) {
                $seat->total_rows - 1 => 'recliner',
                $seat->total_rows - 2 => 'premium',
                default => 'normal',
            };

            DB::table('seats')->where('id', $seat->id)->update(['seat_type' => $seatType]);
        }
    }
};
