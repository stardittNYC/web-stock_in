<?php

namespace App\Support;

/**
 * Kelas pembantu untuk menangani label terjemahan dan gaya warna badge jenis transaksi inventaris.
 */
class TransactionType
{
    /**
     * Mengembalikan teks label terjemahan yang mudah dibaca untuk setiap tipe transaksi inventaris.
     */
    public static function label(?string $type): string
    {
        return match ($type) {
            'stock_in' => t('transaction_stock_in'),
            'stock_out' => t('transaction_stock_out'),
            'stock_audit' => t('transaction_stock_audit'),
            default => (string) $type,
        };
    }

    /**
     * Mengembalikan konfigurasi warna badge (warna latar belakang dan warna teks) untuk tiap jenis transaksi.
     *
     * @return array{background: string, color: string}
     */
    public static function badge(?string $type): array
    {
        return match ($type) {
            // Hijau untuk barang masuk (Stock In)
            'stock_in' => ['background' => '#E8F5E9', 'color' => '#2E7D32'],
            // Merah untuk barang keluar / penjualan (Stock Out)
            'stock_out' => ['background' => '#FBECEC', 'color' => '#B45F5F'],
            // Kuning oranye untuk penyesuaian audit stok (Stock Audit)
            'stock_audit' => ['background' => '#FFF4E5', 'color' => '#B26A00'],
            // Default netral
            default => ['background' => '#F5EFE8', 'color' => '#76553D'],
        };
    }
}
