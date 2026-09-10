<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskUiFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function seedPaymentMethods(): void
    {
        PaymentMethod::create(['name' => 'Tunai', 'code' => 'cash', 'type' => 'cash', 'is_active' => true, 'sort_order' => 1]);
        PaymentMethod::create(['name' => 'QRIS', 'code' => 'qris', 'type' => 'online', 'is_active' => true, 'sort_order' => 2, 'config' => ['provider' => 'mock']]);
        PaymentMethod::create(['name' => 'Kartu Debit', 'code' => 'debit_card', 'type' => 'online', 'is_active' => true, 'sort_order' => 3, 'config' => ['provider' => 'mock']]);
    }

    public function test_menu_renders_product_photo(): void
    {
        Product::factory()->create([
            'name' => 'Nasi Goreng Spesial',
            'image' => 'products/nasgor.jpg',
        ]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('storage/products/nasgor.jpg')
            ->assertSee('Nasi Goreng Spesial');
    }

    public function test_cart_drawer_and_cart_page_show_product_thumbnail(): void
    {
        $product = Product::factory()->create([
            'name' => 'Sate Ayam',
            'image' => 'products/sate.jpg',
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

        $menu = $this->get(route('menu.index'));
        $menu->assertOk()->assertSee('storage/products/sate.jpg');

        $cart = $this->get(route('cart.index'));
        $cart->assertOk()->assertSee('storage/products/sate.jpg')->assertSee('Sate Ayam');
    }

    public function test_menu_card_shows_quantity_stepper_when_item_in_cart(): void
    {
        $product = Product::factory()->create([
            'name' => 'Kopi Susu',
            'image' => 'products/kopisusu.jpg',
            'description' => 'Kopi dengan susu segar',
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('name="quantity" value="1"', false)
            ->assertSee('name="quantity" value="3"', false);
    }

    public function test_sold_out_product_shows_overlay_instead_of_add_button(): void
    {
        Product::factory()->create([
            'name' => 'Es Teh Manis',
            'stock_type' => 'limited',
            'stock' => 0,
            'image' => 'products/esteh.jpg',
        ]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('Habis')
            ->assertDontSee('+ Tambah');
    }

    public function test_cart_page_has_payment_radios_without_cashier_option(): void
    {
        $this->seedPaymentMethods();

        Product::factory()->create(['name' => 'Es Kopi', 'stock_type' => 'unlimited', 'stock' => 99]);

        $this->post(route('cart.add'), ['product_id' => Product::first()->id, 'quantity' => 1]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Tipe Order')
            ->assertSee('Metode Pembayaran')
            ->assertSee('Kartu Debit')
            ->assertDontSee('Bayar di Kasir')
            ->assertDontSee('Lanjut Isi Data');
    }

    public function test_customer_layout_has_no_cashier_or_account_links(): void
    {
        $home = $this->get('/');
        $home->assertOk();
        $home->assertDontSee('>Kasir<');
        $home->assertDontSee('>Akun<');
        $home->assertSee('Pengalaman');

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertDontSee('>Kasir<');
    }

    public function test_location_section_shows_all_active_areas_with_maps_button(): void
    {
        Area::factory()->create([
            'name' => 'Restoran Utama',
            'is_active' => true,
            'address' => 'https://www.google.com/maps/search/?api=1&query=-6.9043,107.6181',
            'latitude' => -6.9043,
            'longitude' => 107.6181,
            'open_time' => '11:00',
            'close_time' => '22:00',
        ]);
        Area::factory()->create([
            'name' => 'Villa',
            'is_active' => true,
            'address' => 'https://www.google.com/maps/search/?api=1&query=-6.8082,107.6219',
            'open_time' => '07:00',
            'close_time' => '23:00',
        ]);
        Area::factory()->create([
            'name' => 'Gudang',
            'is_active' => false,
            'address' => 'https://www.google.com/maps/search/?api=1&query=-7.0,110.0',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Restoran Utama')
            ->assertSee('Villa')
            ->assertDontSee('Gudang')
            ->assertSee('-6.9043,107.6181')
            ->assertSee('maps.google.com')
            ->assertSee('Menuju Restaurant')
            ->assertSee('Buka di Google Maps')
            ->assertDontSee('Lihat Menu Lengkap');
    }

    public function test_cart_actions_accept_ajax_json_requests(): void
    {
        $product = Product::factory()->create([
            'name' => 'Kopi Tubruk',
            'stock_type' => 'unlimited',
            'stock' => 99,
            'sale_price' => 15000,
        ]);

        $this->withHeader('Accept', 'application/json')
            ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertOk()
            ->assertJsonStructure(['count' => [], 'subtotal' => [], 'lines' => [], 'pricing' => []])
            ->assertJsonPath('count', 1);

        $this->withHeader('Accept', 'application/json')
            ->post(route('cart.update', $product->id), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('count', 3)
            ->assertJsonPath('subtotal', 45000);

        $this->withHeader('Accept', 'application/json')
            ->post(route('cart.remove', $product->id))
            ->assertOk()
            ->assertJsonPath('count', 0);
    }
}
