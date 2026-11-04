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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hall_id')->constrained()->cascadeOnDelete();
            $table->foreignId('showtime_id')->constrained()->cascadeOnDelete();
            $table->date('show_date');
            $table->time('start_time');
            $table->string('movie_title');
            $table->string('cinema_name');
            $table->string('hall_name');
            $table->string('format')->default('2D');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->integer('seat_count')->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('offer_code')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('confirmed');
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->index(['showtime_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
