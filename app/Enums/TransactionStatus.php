<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case POSTED = 'POSTED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu',
            self::POSTED => 'Berhasil',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
