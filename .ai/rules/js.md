---
paths:
  - 'resources/js/**'
---

# Js

## Cart AJAX: forward the clicked submit button via event.submitter
`new FormData(form)` NEVER includes submit buttons, so the menu/cart −/+ buttons (name="quantity" value=next-qty) sent NO quantity and the server returned 422 "quantity field is required". submitCartForm must take the clicked button (event.submitter) and do `formData.set(submitter.name, submitter.value)`. Keep the Enter key on [data-cart-quantity-input] intercepted via keydown so it submits the typed value instead of the default first submit button (−).
