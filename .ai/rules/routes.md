---
paths:
  - 'routes/*.php'
---

# Routes

## Controller classes as route handlers
Point routes at controller classes (or `[Controller::class, 'method']`), never at closures containing request-handling logic. Closures are only used for `Route::group()` grouping.

## Assign middleware on routes
Attach middleware via `->middleware()` on the route/route group definition. Do not implement `HasMiddleware` or `#[Middleware]` on controllers.
