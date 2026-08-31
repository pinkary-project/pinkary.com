---
paths:
  - 'resources/views/**/*.blade.php'
---

# Views

## Anonymous components for UI, class components for layouts
Build reusable UI pieces as anonymous Blade components in resources/views/components with `@props`. Only create an app/View/Components class when the component needs constructor/render PHP logic (e.g. layouts).
