---
paths:
  - 'resources/views/layouts/**'
---

# Layouts

## Premium customer layouts load customer.css (internal keeps app.css)
Customer/auth layouts (layouts/kiosk.blade.php, layouts/guest.blade.php) load resources/css/customer.css + @fonts — the ivory/forest/gold premium theme. Internal admin/cashier/kitchen app.blade.php keeps the dark resources/css/app.css. Never swap these: the two themes are separate Tailwind v4 entries. kiosk header supports a transparent hero via @section('header-mode', 'transparent') + @section('hero').
