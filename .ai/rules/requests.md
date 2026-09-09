---
paths:
  - app/Http/Requests/CheckoutRequest.php
---

# Requests

## Customer name/phone validation rules
customer_name: nullable|regex /^[\p{L}\p{M}\s.,'-]+$/u (letters only, no digits). customer_phone: nullable|regex /^[0-9]{8,15}$/ — do NOT use the "integer" rule; PHP filter_var rejects Indonesian numbers with a leading zero (e.g. 08123456).
