<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model InventoryTransaction mencatat riwayat buku besar setiap mutasi/pergerakan stok barang.
 */
class InventoryTransaction extends Model
{
    /**
     * Kolom-kolom tabel yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'business_id',
        'product_id',
        'product_name',
        'product_code',
        'transaction_type',
        'quantity',
        'stock_before',
        'stock_after',
        'purchase_price',
        'selling_price',
        'total_amount',
        'notes',
        'transaction_date',
    ];

    /**
     * Konversi tipe data otomatis (casting) untuk angka, nilai mata uang, dan tanggal transaksi.
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'transaction_date' => 'datetime',
        ];
    }

    /**
     * Relasi ke entitas Bisnis: Transaksi mutasi stok tercatat pada bisnis tertentu.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Relasi ke entitas Produk: Transaksi mutasi stok mengacu pada produk tertentu.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
