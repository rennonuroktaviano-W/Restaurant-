---
paths:
  - 'resources/js/**'
  - resources/js/cart.js
---

# Js

## Cart AJAX: forward the clicked submit button via event.submitter
`new FormData(form)` NEVER includes submit buttons, so the menu/cart −/+ buttons (name="quantity" value=next-qty) sent NO quantity and the server returned 422 "quantity field is required". submitCartForm must take the clicked button (event.submitter) and do `formData.set(submitter.name, submitter.value)`. Keep the Enter key on [data-cart-quantity-input] intercepted via keydown so it submits the typed value instead of the default first submit button (−).

## Rebuild assets after JS changes for inline window.* hooks
Inline Blade scripts call window.startBoardPolling on cashier/kitchen boards. This function is exposed by resources/js/app.js -> board-polling.js but was missing from the compiled public/build bundle, causing "window.startBoardPolling is not a function" errors. After any change to resources/js, run `npm run build` and verify the new bundle (public/build/manifest.json) contains the hook.

## Cart drawer rows are server-rendered; cart.js inserts new lines
_cart-drawer.blade.php rows are server-rendered, so cart.js applyCartState() must call patchDrawer() to insert rows for newly added products (patchLine only updates existing rows). drawerLineHTML() must mirror the Blade markup, including the qty==1 minus as the <form id="cart-remove-{id}"> referenced by the row's X button. Rebuild with npm run build after any change.
