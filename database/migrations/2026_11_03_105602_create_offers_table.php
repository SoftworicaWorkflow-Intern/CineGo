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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_np')->nullable();
            $table->text('description');
            $table->text('description_np')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('badge')->nullable();
            $table->string('discount_type')->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
