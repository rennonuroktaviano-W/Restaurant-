<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\WeeklyPromoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('discount.manage');
    }

    public function index(WeeklyPromoService $weekly): View
    {
        $weekly->ensureCurrent();

        $discounts = Discount::withCount('items')->orderByDesc('is_active')->orderBy('name')->paginate(20);

        return view('admin.discounts.index', compact('discounts'));
    }

    public function weekly(WeeklyPromoService $weekly): RedirectResponse
    {
        $promo = $weekly->regenerate();

        return redirect()->route('admin.discounts.index')
            ->with('success', "Promo mingguan baru dibuat: kode {$promo->code} — diskon {$promo->value}%.");
    }

    public function create(): View
    {
        return view('admin.discounts.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $items = $data['discount_items'] ?? [];
        unset($data['discount_items']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('discounts', 'public');
        }

        $discount = DB::transaction(function () use ($data, $items) {
            $discount = Discount::create($data);

            $this->syncItems($discount, $items);

            return $discount;
        });

        $this->audit->log('create', 'pricing', 'discount', $discount->id, [], $discount->toArray());

        return redirect()->route('admin.discounts.index')->with('success', 'Promo berhasil dibuat.');
    }

    public function edit(Discount $discount): View
    {
        return view('admin.discounts.edit', ['discount' => $discount] + $this->formData());
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $old = $discount->toArray();
        $data = $this->validated($request);
        $items = $data['discount_items'] ?? [];
        unset($data['discount_items']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('discounts', 'public');
        }

        DB::transaction(function () use ($discount, $data, $items) {
            $discount->update($data);
            $this->syncItems($discount, $items);
        });

        $this->audit->log('update', 'pricing', 'discount', $discount->id, $old, $discount->fresh()->toArray());

        return redirect()->route('admin.discounts.index')->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->delete();

        $this->audit->log('delete', 'pricing', 'discount', $discount->id, [], []);

        return redirect()->route('admin.discounts.index')->with('success', 'Promo berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'code' => ['nullable', 'string', 'max:50', 'unique:discounts,code,'.$request->route('discount')?->id],
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gt:min_amount'],
            'is_automatic' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'discount_items' => ['nullable', 'array'],
            'discount_items.*.target_type' => ['nullable', Rule::in(['product', 'category'])],
            'discount_items.*.target_id' => ['nullable', 'integer'],
        ]);
    }

    private function syncItems(Discount $discount, array $items): void
    {
        $discount->items()->delete();

        foreach ($items as $item) {
            if (empty($item['target_type']) || empty($item['target_id'])) {
                continue;
            }

            $discount->items()->create([
                'target_type' => $item['target_type'],
                'target_id' => $item['target_id'],
                'quantity_required' => $item['quantity_required'] ?? null,
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'products' => Product::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ];
    }
}
