---
paths:
  - 'app/Observers/**'
---

# Observers

## Observers may call Actions
Eloquent observers may call Action classes for domain writes (for example hashtag sync). Keep lifecycle fan-out (notifications, cascading deletes) in the observer.
