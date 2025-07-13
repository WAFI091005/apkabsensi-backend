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
        Schema::create('absen_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique(); // UUID untuk QR code
            $table->string('kelas');         // Contoh: x-ipa-2
            $table->timestamp('expired_at'); // Expire 5-10 menit
            $table->foreignId('created_by')->constrained('users'); // Guru yang membuat
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_sessions');
    }
};
