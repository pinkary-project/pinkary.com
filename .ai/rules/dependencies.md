---
paths:
  - 'package.json'
  - 'composer.json'
---

# Dependencies

## Keep the Prettier packages
Keep `prettier`, `prettier-plugin-blade` and `prettier-plugin-tailwindcss` in `package.json`. The rule `Pint/laravel_blade` in `pint.json` runs them from `node_modules/` to format the Blade views, thus a search of the code finds no use of them.
