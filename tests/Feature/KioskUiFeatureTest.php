<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KioskUiFeatureTest extends TestCase
{
    use RefreshDatabase;

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
}
