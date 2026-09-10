---
paths:
  - 'database/seeders/**'
---

# Seeders

## Demo products must flag is_kitchen or KDS stays empty
Kitchen board only renders orders with `has_kitchen_items=true`, which is derived from `products.is_kitchen`. The DemoMasterDataSeeder marks ALL demo products (food AND drinks) as `is_kitchen=true` via `updateOrCreate`, so every order flows to the KDS. When adding demo products, include the kitchen flag or the kitchen display stays empty. It is safe to re-run `php artisan db:seed` locally to resync the flag on existing rows.
