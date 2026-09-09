---
paths:
  - 'app/Http/Controllers/Customer/**'
  - app/Http/Controllers/Customer/CartController.php
---

# Customer

## Landing page lives in MenuController::home(), catalog stays in index/category
MenuController::home() renders the marketing landing at / (name 'home'); MenuController::index()/category() remain the order catalog at /menu. Both call handleLocationToken(). Featured picks is_featured first then sort; heroImages = first 3 visible with image. Missing CMS settings (business.address/phone) fall back to safe PRD copy in the view.

## Cart AJAX JSON contract + test parity
add/update/remove branch on $request->wantsJson() and return {count, subtotal, lines, pricing}; otherwise keep redirect for tests. Customer cart forms are tagged data-cart-ajax and intercepted by resources/js/cart.js (fetch, patches DOM nodes + Alpine stores hydrated from #cart-state JSON in layouts/kiosk). Keep the drawer/cart rows server-rendered — KioskUiFeatureTest pins name="quantity" value="1"/"3", asset("storage/...") URLs, "Habis", and no "+ Tambah" when sold out. Menu cards render both the stepper and "+ Tambah" forms (hidden toggled via JS).
