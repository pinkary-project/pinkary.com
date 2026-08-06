---
paths:
  - 'app/Models/*.php'
  - 'app/Models/**'
---

# Models

## Legacy accessor/mutator methods
Define computed/mutated attributes with legacy `getXxxAttribute()`/`setXxxAttribute()` methods, not the `Attribute::make()` class syntax.

## Mass assignment is globally unguarded
`Model::unguard()` is called in AppServiceProvider, so mass assignment protection is disabled app-wide. Do not add `$fillable`/`$guarded` to models.

## Enum column storage
Enum-backed columns are stored as `string()` in migrations and cast to a PHP backed enum via the model's `casts()` method rather than using a DB `->enum()` column.
