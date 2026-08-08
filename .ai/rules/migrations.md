---
paths:
  - 'database/migrations/**'
---

# Migrations

## Foreign key definitions
Use `$table->foreignIdFor(Model::class)->constrained()->cascadeOnDelete()` for standard bigint foreign keys. Only fall back to manual `$table->foreign()->references()->on()` when the referenced key is non-standard (e.g. UUID or string primary key).

## One-way migrations
Do not implement `down()` reverse logic; migrations are one-way (omit the `down()` method).

## Enum column storage
Store enum-backed columns as `string()` in migrations; cast to a PHP backed enum via the model's `casts()` method rather than using a DB `->enum()` column.
