<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk membuat tabel produk barang dagangan.
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel products.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Kolom ID primer produk
            $table->id();
            // Foreign key relasi ke tabel businesses
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // Foreign key relasi opsional ke tabel categories
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            // Nama barang / produk
            $table->string('product_name', 50);
            // Kode unik SKU / barcode produk
            $table->string('product_code', 20);
            // Nama berkas foto produk
            $table->string('photo')->nullable();
            // Harga beli (modal) per unit
            $table->decimal('purchase_price', 15, 2)->default(0);
            // Harga jual per unit
            $table->decimal('selling_price', 15, 2)->default(0);
            // Jumlah stok awal saat produk pertama kali dibuat
            $table->integer('initial_stock')->default(0);
            // Jumlah stok fisik terkini yang tersedia
            $table->integer('current_stock')->default(0);
            // Tanggal batas kedaluwarsa barang (opsional)
            $table->date('expiration_date')->nullable();
            // Catatan atau keterangan produk
            $table->text('notes')->nullable();
            // Timestamp created_at dan updated_at
            $table->timestamps();

            // Kode produk bersifat unik dalam satu bisnis tertentu
            $table->unique(['business_id', 'product_code']);
        });
    }

    /**
     * Membatalkan migrasi (menghapus tabel products).
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
