<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use RuntimeException;

class RefundController extends Controller
{
    public function __construct(protected RefundService $refunds) {}

    public function index(Request $request): View
    {
        Gate::authorize('payment.refund');

        $refunds = Refund::query()
            ->with(['order', 'payment', 'creator'])
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('reason', 'like', "%{$s}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$s}%"));
            }))
            ->when($request->filled('date_from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->filled('date_to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }

    public function create(Payment $payment): View
    {
        Gate::authorize('payment.refund');

        $payment->load(['order']);
        $refundable = $this->refundable($payment);

        if ($refundable <= 0) {
            abort(422, 'Payment ini sudah di-refund penuh.');
        }

        return view('admin.refunds.create', compact('payment', 'refundable'));
    }

    public function store(Request $request, Payment $payment): RedirectResponse
    {
        Gate::authorize('payment.refund');

        $payment->load(['order']);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason_code' => ['nullable', 'in:customer,damaged,other'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validated['amount'] > $this->refundable($payment)) {
            return back()->withErrors(['amount' => 'Jumlah refund melebihi sisa yang dapat di-refund.'])
                ->withInput();
        }

        try {
            $this->refunds->refund(
                $payment->order,
                $payment,
                (float) $validated['amount'],
                $validated['reason'],
                actorId: auth()->id(),
                reasonCode: $validated['reason_code'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.refunds.index')
            ->with('success', 'Refund berhasil dicatat.');
    }

    private function refundable(Payment $payment): float
    {
        if ($payment->status !== Payment::STATUS_PAID) {
            return 0.0;
        }

        $refunded = (float) $payment->refunds()
            ->where('status', Refund::STATUS_SUCCEEDED)
            ->sum('amount');

        return round((float) $payment->amount - $refunded, 2);
    }
}
