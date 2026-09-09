---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Thin controllers delegate to Actions/Jobs/Services
Keep controller methods thin: validate/authorize inline, then delegate domain writes to an Action or Job rather than embedding business logic in the controller.

## Implicit route model binding by default
Use implicit type-hinted Eloquent model binding for route parameters. Only add an explicit `Route::bind()` when the lookup needs custom logic (e.g. case-insensitive matching), as done for `username`.
