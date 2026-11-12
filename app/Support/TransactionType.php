<?php

namespace App\Support;

class TransactionType
{
    /**
     * Human readable (translated) label for a transaction type.
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
     * Badge colours (background/color) for a transaction type.
     *
     * @return array{background: string, color: string}
     */
    public static function badge(?string $type): array
    {
        return match ($type) {
            'stock_in' => ['background' => '#E8F5E9', 'color' => '#2E7D32'],
            'stock_out' => ['background' => '#FBECEC', 'color' => '#B45F5F'],
            'stock_audit' => ['background' => '#FFF4E5', 'color' => '#B26A00'],
            default => ['background' => '#F5EFE8', 'color' => '#76553D'],
        };
    }
}
