<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk membuat tabel entitas bisnis/toko pengguna.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel businesses.
     */
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            // Kolom ID primer auto increment
            $table->id();
            // Foreign key relasi ke tabel users dengan cascade delete
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Nama toko / bisnis
            $table->string('business_name', 40);
            // Nama berkas foto logo bisnis
            $table->string('photo')->nullable();
            // Kode mata uang acuan (default: IDR)
            $table->string('currency', 10)->default('IDR');
            // Catatan atau keterangan deskripsi bisnis
            $table->text('notes')->nullable();
            // Timestamp created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi (menghapus tabel businesses).
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
