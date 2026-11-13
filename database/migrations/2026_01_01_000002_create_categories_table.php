<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk membuat tabel kategori produk bisnis.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel categories.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            // Kolom ID primer
            $table->id();
            // Foreign key relasi ke tabel businesses
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // Nama kategori produk
            $table->string('category_name', 40);
            // Timestamp created_at dan updated_at
            $table->timestamps();

            // Mencegah duplikasi nama kategori dalam satu bisnis yang sama
            $table->unique(['business_id', 'category_name']);
        });
    }

    /**
     * Membatalkan migrasi (menghapus tabel categories).
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
