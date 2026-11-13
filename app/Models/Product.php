<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Product mewakili data barang/produk yang diperdagangkan atau dikelola stoknya.
 */
class Product extends Model
{
    /**
     * Kolom-kolom tabel yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'business_id',
        'category_id',
        'product_name',
        'product_code',
        'photo',
        'purchase_price',
        'selling_price',
        'initial_stock',
        'current_stock',
        'expiration_date',
        'notes',
    ];

    /**
     * Konversi tipe data otomatis (casting) untuk atribut angka dan desimal.
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'initial_stock' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    /**
     * Relasi ke entitas Bisnis: Setiap produk berada di bawah satu bisnis tertentu.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Relasi ke entitas Kategori: Produk dapat dikelompokkan ke dalam satu kategori (opsional).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
