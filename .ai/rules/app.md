---
paths:
  - 'app/**'
---

# App

## Prefer constructor/method injection over app()
Type-hint dependencies on controller actions, job `handle()` methods, and Livewire lifecycle hooks. Only reach for `app(X::class)` inside closures/static contexts where injection isn't reachable.

## No custom Events/Listeners
Decouple side effects via Eloquent Observers (`#[ObservedBy]`) and `Job::dispatch()`, not custom Event/Listener classes. Reserve `event()` for framework-native auth events only.

## Use helpers over facades
Use `auth()`/`config()` helper functions instead of `Auth::`/`Config::` facades; facades are the rare exception.

## Full-sentence translation keys
Call `__()` with the literal full English sentence as the key (no lang/*.php or lang/*.json files defined). Do not introduce short dot-notation keys for new project strings.

## Idempotent create writes
Use `relation()->firstOrCreate([...])` for get-or-create writes (e.g. likes, bookmarks, blocked accounts) instead of manual find-then-create checks.

## Named routes only for URLs
Generate URLs/redirects with `route()`/`to_route()` (named routes). Never use raw `url()` or `action([Controller::class, ...])`.

## Prefer static Str:: helpers
Use static `Str::` facade methods (e.g. `Str::limit`, `Str::startsWith`, `Str::contains`) for string manipulation rather than `Str::of()` fluent chains or native `str_*`/`strto*` functions.

## CarbonImmutable date policy
Dates are immutable app-wide via `Date::use(CarbonImmutable::class)` in AppServiceProvider. Use `now()`/`today()` helpers and typehint date properties/params as `CarbonImmutable`, not `Carbon`.
