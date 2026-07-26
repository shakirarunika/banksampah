<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETED = 'COMPLETED';

    /**
     * Status yang dihitung sebagai "uang sudah keluar" dari saldo.
     */
    public static function deducted(): array
    {
        return [self::PENDING->value, self::COMPLETED->value];
    }
}
