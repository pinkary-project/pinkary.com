---
paths:
  - 'app/Rules/**'
---

# Rules

## Custom validation rules as invokable objects
Implement custom validation logic as an invokable class in app/Rules and reference it inline as `new RuleClass()` inside a rules array. Do not use inline closures or `Validator::extend()`.
