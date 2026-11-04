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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('city_id')->nullable()->after('phone')->constrained()->nullOnDelete();
            $table->string('preferred_language')->default('en')->after('city_id');
            $table->boolean('is_admin')->default(false)->after('preferred_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropColumn(['phone', 'preferred_language', 'is_admin']);
        });
    }
};
