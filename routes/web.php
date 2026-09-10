<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LocationQrController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Cashier\OrderActionController;
use App\Http\Controllers\Cashier\ReceiptController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Customer\OrderTrackingController;
use App\Http\Controllers\Customer\PaymentRedirectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\Kitchen\KitchenOrderActionController;
use App\Http\Controllers\Payment\MockWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest')->name('login.store');
Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::prefix('menu')->name('menu.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/category/{category:slug}', [MenuController::class, 'category'])->name('category');
});

Route::get('/', [MenuController::class, 'home'])->name('home');

Route::prefix('cart')->name('cart.')->middleware('throttle:kiosk-cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update/{productId}', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::post('/discount', [CartController::class, 'applyDiscount'])->name('discount');
});

Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:kiosk-checkout');

Route::get('/track/{order:order_number}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:120,1')
    ->missing(fn () => redirect()->route('home')->with('error', 'Order tidak ditemukan.'))
    ->name('tracking.show');

Route::get('/payment/mock/{payment}', [PaymentRedirectController::class, 'show'])->name('payment.mock.pay');
Route::post('/payment/mock/{payment}/process', [PaymentRedirectController::class, 'process'])->name('payment.mock.process');

Route::post('/webhook/payment/mock', [MockWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('webhook.payment.mock');

Route::middleware(['auth', 'active', 'role:cashier|manager|admin|kitchen'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/freshness', [CashierController::class, 'freshness'])->name('dashboard.freshness');
    Route::get('/orders', [CashierController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [CashierController::class, 'show'])->name('orders.show');

    Route::post('/orders/{order}/accept', [OrderActionController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/complete', [OrderActionController::class, 'complete'])->name('orders.complete');
    Route::post('/orders/{order}/cancel', [OrderActionController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/pay-cash', [OrderActionController::class, 'payCash'])->name('orders.pay-cash');
    Route::post('/orders/{order}/pay-online', [OrderActionController::class, 'payOnline'])->name('orders.pay-online');

    Route::get('/receipt/{order}', [ReceiptController::class, 'show'])->name('receipt.show');
    Route::get('/receipt/{order}/print', [ReceiptController::class, 'print'])->name('receipt.print');

    Route::get('/history', [CashierController::class, 'history'])->name('history');
    Route::get('/shift', [ShiftController::class, 'show'])->name('shift');
});

Route::middleware(['auth', 'active', 'role:kitchen|manager|admin'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/', [KitchenController::class, 'dashboard'])->name('dashboard');
    Route::get('/board/freshness', [KitchenController::class, 'freshness'])->name('board.freshness');
    Route::post('/orders/{order}/status', [KitchenOrderActionController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/cancel', [KitchenOrderActionController::class, 'cancel'])->name('orders.cancel');
});

Route::middleware(['auth', 'active', 'role:admin|manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('areas', AreaController::class);

    Route::get('/rooms', [RoomController::class, 'indexAll'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

    Route::get('/dining-tables', [DiningTableController::class, 'indexAll'])->name('dining-tables.index');
    Route::get('/dining-tables/create', [DiningTableController::class, 'create'])->name('dining-tables.create');
    Route::post('/dining-tables', [DiningTableController::class, 'store'])->name('dining-tables.store');
    Route::get('/dining-tables/{table}/edit', [DiningTableController::class, 'edit'])->name('dining-tables.edit');
    Route::put('/dining-tables/{table}', [DiningTableController::class, 'update'])->name('dining-tables.update');
    Route::delete('/dining-tables/{table}', [DiningTableController::class, 'destroy'])->name('dining-tables.destroy');
    Route::get('/dining-tables/{table}/qr-code', [LocationQrController::class, 'table'])->name('tables.qr');

    Route::get('/rooms/{room}/qr-code', [LocationQrController::class, 'room'])->name('rooms.qr');

    Route::resource('payment-methods', PaymentMethodController::class)->except('show');
    Route::resource('discounts', DiscountController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/stock-in', [InventoryController::class, 'stockInForm'])->name('inventory.stock-in.form');
    Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in.store');
    Route::get('/inventory/stock-out', [InventoryController::class, 'stockOutForm'])->name('inventory.stock-out.form');
    Route::post('/inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out.store');
    Route::get('/inventory/transfer', [InventoryController::class, 'transferForm'])->name('inventory.transfer.form');
    Route::post('/inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer.store');

    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    Route::get('/refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/{payment}/create', [RefundController::class, 'create'])->name('refunds.create');
    Route::post('/refunds/{payment}', [RefundController::class, 'store'])->name('refunds.store');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'active'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/excel', [ReportController::class, 'excel'])->name('reports.excel');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
});
