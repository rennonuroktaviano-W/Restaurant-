<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('orders:expire-payments')]
#[Description('Expire overdue online payment attempts (FR-PAY-006, Table 22)')]
class ExpirePayments extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PaymentService $payments): int
    {
        $count = 0;

        Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '<=', now())
            ->each(function (Payment $payment) use ($payments, &$count): void {
                $payments->expire($payment);
                $count++;
            });

        $this->info("Expired {$count} overdue online payment attempt(s).");

        return self::SUCCESS;
    }
}
