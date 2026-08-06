---
paths:
  - 'app/Policies/*.php'
---

# Policies

## Authorization lives in Policy classes
Define authorization logic in a Policy class under app/Policies, not via `Gate::define()` in a provider.
