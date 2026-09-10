---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## Pass actor ID in inventory adjust and audit logs
InventoryService::adjust() and AuditLogger::log() accept an actor_id/actorId parameter. Always pass `auth()->id()` when logging admin stock adjustments and audit events, otherwise the actor column is stored NULL and the admin UI shows "-" as the actor.
