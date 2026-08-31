---
paths:
  - 'app/Queries/**'
---

# Queries

## Query objects expose builder()
Extract complex/reusable query logic into a `final readonly class` under app/Queries with a single `builder(): Builder` method. Construct it with query parameters and call `->builder()` from the consumer (Livewire/controller) to paginate or execute.
