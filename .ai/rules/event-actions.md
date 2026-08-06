---
paths:
  - 'app/EventActions/**'
---

# Event Actions

## EventActions is a naming convention, not events
Classes here are plain single-purpose action classes with a public `handle()` method, invoked directly via `new X(...)->handle()`. The directory name is historical and unrelated to Laravel's Events/Listeners system.
