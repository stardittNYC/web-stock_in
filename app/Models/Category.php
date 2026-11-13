<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Category mewakili kelompok klasifikasi produk dalam suatu bisnis.
 */
class Category extends Model
{
    /**
     * Kolom-kolom tabel yang dapat diisi secara massal (mass assignable).
     */
    protected $fillable = [
        'business_id',
        'category_name',
    ];

    /**
     * Relasi ke entitas Bisnis: Kategori dimiliki oleh satu bisnis.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
