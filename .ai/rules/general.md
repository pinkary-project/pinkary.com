---
paths:
  - composer.json
  - '**/*.php'
---

# General

## Identify failing composer test stages from a full log
composer test = test:lint (rector --dry-run + pint) -> test:types (phpstan) -> test:type-coverage (min=100, prints config\*/routes\* per-file tables) -> test:unit (pest --parallel --coverage min=98.8, prints its own Total). On failure never trust a truncated tail: the config/routes table belongs to type-coverage AND line coverage prints similar tables. Capture the whole run to a log file (Out-String | Set-Content -Encoding UTF8) and search it for 'error code' / 'Total:' to find the real failing stage.

## Windows PowerShell exit codes are unreliable for piped native commands
On this Windows machine, piping native commands (composer, php artisan, vendor/bin/*) through Select-Object/Select-String wraps their stderr in NativeCommandError records, which can make the harness report exit code 1 even when the command passed (git push, passing suites). When a result looks contradictory, re-run capturing output to a UTF-8 log file via Out-String | Set-Content and judge success by the log content, not the pipeline exit code.
