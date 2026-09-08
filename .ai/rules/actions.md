---
paths:
  - 'app/Actions/**'
---

# Actions

## Single-purpose Action classes
Put domain writes in final readonly Action classes under app/Actions/{Domain}. Inject stable collaborators through the constructor, and pass request-time models and values to `handle()`. Resolve actions through Laravel's container in controllers, console commands, observers, Filament callbacks, and Livewire methods. No Action suffix and no base class. Do not use auth(), session(), or request(); pass models and values in.

## Inject actions and pass runtime data to handle
Resolve action classes through Laravel's container. Inject stable collaborators through the constructor; pass request-time models and validated values to handle(). Controllers, Livewire methods, observers, console commands, and Filament callbacks should receive actions through dependency injection where supported.

## Use CRUD names for resource mutations
Prefer Create{Resource}, Update{Resource}, and Delete{Resource} for resource mutations. Keep a domain-specific verb only when the operation is not naturally CRUD.
