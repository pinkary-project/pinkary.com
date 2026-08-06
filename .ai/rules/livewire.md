---
paths:
  - 'app/Livewire/**'
---

# Livewire

## Authorize via $this->authorize()
Call `$this->authorize('ability', $model)` inside Livewire component methods to enforce policies. Do not use `@can`, `can:` middleware, `#[Authorize]`, or `$user->can()`.

## Explicit eager loading
Add relations with an explicit `->with()` call at the query call site; do not declare a model-level `protected $with` default.

## Share Livewire behavior via Concerns traits
Extract repeated Livewire component behavior into a trait under app/Livewire/Concerns and compose it into components, rather than duplicating logic or building a base class.

## Class-based Livewire with separate view
Write Livewire components as final classes extending Component with a `render(): View` method returning `view('livewire.dot.path', [...])`. Do not use Volt or single-file components. Mount them into controller-rendered pages via `<livewire:name />` tags, not `Route::get(..., Component::class)`.

## Use simplePaginate for feed/listing components
Use `->simplePaginate()` for infinite-scroll/feed-style Livewire listings instead of `paginate()`.
