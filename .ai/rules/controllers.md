---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Weekly promo: exactly one active, lazy ensure
Guest pages call WeeklyPromoService::ensureCurrent() lazily (MenuController, CartController::index, Admin DiscountController::index) and pass $weeklyPromo. The promo:weekly command (scheduled Mon 00:30) and admin POST admin.discounts.weekly both go through the service so only ONE is_weekly promo is active at a time; createFor() first deactivates all active is_weekly rows. Weekly promo is code-based (NOT auto-applied) — customers must type the code (shown as hint on cart page).
