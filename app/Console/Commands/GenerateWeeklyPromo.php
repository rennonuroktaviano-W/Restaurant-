<?php

namespace App\Console\Commands;

use App\Services\WeeklyPromoService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('promo:weekly')]
#[Description('Ensure exactly one active weekly promo (10-30%) with a unique code')]
class GenerateWeeklyPromo extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WeeklyPromoService $weeklyPromo): int
    {
        $promo = $weeklyPromo->ensureCurrent();

        $this->info("Weekly promo active: {$promo->code} — {$promo->value}%.");

        return self::SUCCESS;
    }
}
