<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk membuat tabel buku besar transaksi inventaris (mutasi stok).
 */
return new class extends Migration
{
    /**
     * Menjalankan migrasi pembuatan tabel inventory_transactions.
     */
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            // Kolom ID primer transaksi
            $table->id();
            // Foreign key relasi ke tabel businesses
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // Foreign key relasi ke tabel products
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot nama produk pada saat transaksi terjadi
            $table->string('product_name', 50);
            // Snapshot kode produk pada saat transaksi terjadi
            $table->string('product_code', 20)->nullable();
            // Tipe mutasi transaksi: barang masuk, barang keluar, atau penyesuaian audit
            $table->enum('transaction_type', ['stock_in', 'stock_out', 'stock_audit']);
            // Jumlah kuantitas barang yang termutasi
            $table->integer('quantity');
            // Snapshot jumlah stok sebelum mutasi
            $table->integer('stock_before')->nullable();
            // Snapshot jumlah stok sesudah mutasi
            $table->integer('stock_after')->nullable();
            // Snapshot harga beli saat transaksi berlangsung
            $table->decimal('purchase_price', 15, 2)->default(0);
            // Snapshot harga jual saat transaksi berlangsung
            $table->decimal('selling_price', 15, 2)->default(0);
            // Total nominal nilai uang transaksi
            $table->decimal('total_amount', 15, 2)->default(0);
            // Catatan atau keterangan terkait alasan transaksi
            $table->text('notes')->nullable();
            // Waktu pelaksanaan transaksi
            $table->dateTime('transaction_date')->useCurrent();
            // Timestamp created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Membatalkan migrasi (menghapus tabel inventory_transactions).
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
