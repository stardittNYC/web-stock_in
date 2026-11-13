<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Business mewakili entitas toko/usaha yang dikelola pengguna.
 */
class Business extends Model
{
    /**
     * Kolom-kolom tabel yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'user_id',
        'business_name',
        'photo',
        'currency',
        'notes',
    ];

    /**
     * Relasi ke entitas Pengguna: Bisnis dimiliki oleh satu pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke entitas Kategori: Bisnis memiliki banyak kategori produk.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Relasi ke entitas Produk: Bisnis memiliki banyak produk barang.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relasi ke Riwayat Transaksi: Bisnis mencatat banyak riwayat pergerakan stok.
     */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
