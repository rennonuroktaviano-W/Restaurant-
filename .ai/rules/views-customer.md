---
paths:
  - 'resources/views/customer/**'
---

# Views Customer

## Kiosk UI parity strings must survive restyles
KioskUiFeatureTest pins markup you must not break: keep asset('storage/'.$image) URLs, product names, steppers with name="quantity" value="{{ $qty-1 }}"/"{{ $qty+1 }}" on the menu card, literal "Habis" sold-out state, and no "+ Tambah" when sold out. Menu uses {{ $products->links('partials.pagination') }} so admin pagination (default tailwind view) stays dark.
