---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Thin controllers delegate to Jobs/Services/Models
Keep controller methods thin: validate/authorize inline, then delegate the actual work to a Job, Service/Response class, or Eloquent model method rather than embedding business logic in the controller.

## Implicit route model binding by default
Use implicit type-hinted Eloquent model binding for route parameters. Only add an explicit `Route::bind()` when the lookup needs custom logic (e.g. case-insensitive matching), as done for `username`.
