---
paths:
  - app/Services/InventoryService.php
---

# Services

## InventoryService keeps products.stock canonical
Multi-warehouse inventory: products.stock stays the canonical total used by order checkout (reserve/reverse). inventory_items tracks per-warehouse stock; stockIn/stockOut adjust both products.stock and the warehouse item; transfer only moves between warehouse items leaving products.stock unchanged. Order flows decrement/increment sync to the default (first active) warehouse. lowStockProducts alert on admin.inventory.index is products.stock-based (tests depend on this contract); per-warehouse below-minimum items shown separately via warehouseLowStock.
