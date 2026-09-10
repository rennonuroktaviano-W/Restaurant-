---
paths:
  - 'resources/views/partials/**'
---

# Partials

## Sidebar links must match route role middleware
Admin-only routes are wrapped in `role:admin|manager` (routes/web.php:110). Sidebar gates must never show a link a role cannot actually visit — cashier & kitchen both hold `inventory.view` but cannot reach `admin.inventory.*`, so the Inventori link is gated on `inventory.update` (admin+manager only), same as Gudang/Pemasok. When adding a sidebar item, gate it with the same bridge used on its routes.
