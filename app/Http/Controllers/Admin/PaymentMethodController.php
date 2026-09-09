<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('setting.manage');
    }

    public function index(): View
    {
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.payment-methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('admin.payment-methods.create');
    }

    public function store(PaymentMethodRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['config'] = $data['config'] ?? null;
        $data['config'] = is_string($data['config']) ? json_decode($data['config'], true) : $data['config'];

        $method = PaymentMethod::create($data);

        $this->audit->log('create', 'settings', 'payment_method', $method->id, [], $method->toArray());

        return redirect()->route('admin.payment-methods.index')->with('success', 'Metode bayar berhasil dibuat.');
    }

    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment-methods.edit', ['method' => $paymentMethod]);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $old = $paymentMethod->toArray();
        $data = $request->validated();

        if (is_string($data['config'] ?? null)) {
            $data['config'] = json_decode($data['config'], true);
        }

        $paymentMethod->update($data);

        $this->audit->log('update', 'settings', 'payment_method', $paymentMethod->id, $old, $paymentMethod->fresh()->toArray());

        return redirect()->route('admin.payment-methods.index')->with('success', 'Metode bayar berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->payments()->exists()) {
            return back()->with('error', 'Metode bayar sudah pernah dipakai transaksi. Nonaktifkan saja.');
        }

        $paymentMethod->delete();

        $this->audit->log('delete', 'settings', 'payment_method', $paymentMethod->id, [], []);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Metode bayar berhasil dihapus.');
    }
}
