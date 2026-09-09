<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    public function generate(?string $prefix = 'POS'): string
    {
        do {
            $number = sprintf(
                '%s-%s-%s',
                $prefix,
                now()->format('Ymd'),
                strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5))
            );

            $exists = DB::table('orders')->where('order_number', $number)->exists();
        } while ($exists);

        return $number;
    }
}
