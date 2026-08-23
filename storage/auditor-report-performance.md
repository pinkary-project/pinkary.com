# Laravel Auditor Report

**Generated:** 2026-08-23 18:56:05

## Project

- **name:** Pinkary
- **environment:** local
- **php version:** 8.4.24
- **laravel version:** v13.24.0
- **database:** mysql
- **test framework:** pest
- **frontend:** detected, detected, 1

## Summary

**Findings:** 2

| Severity | Count |
| --- | --- |
| Critical | 0 |
| High | 0 |
| Medium | 0 |
| Low | 1 |
| Info | 1 |

| Domain | Count |
| --- | --- |
| Performance | 2 |

## Priority synthesis

**Final partition:** 2 unique recommendation(s). Every promoted ID appears exactly once.

- **P0 - correctness, security, or data-loss risk** (0): none
- **P1 - concrete correctness or high-leverage contract work** (0): none
- **P2 - material invariant improvements with narrower impact** (0): none
- **P3 - lower-impact telemetry, diagnostics, or maintainability** (2): F-2026-P001, F-2026-P002

## Domains Audited

- Security
- Performance
- Architecture
- Database
- Testing
- Laravel conventions

## Findings

### [LOW] Verified-users recommendation query runs uncached with ORDER BY RAND() on every home render `F-2026-P001`

**Rule:** `AUD-PER-007` — Performance
**Severity:** Low
**Confidence:** High
**Status:** Fixed

**Summary**

Home\Users::verifiedUsers() and PeopleToFollowRecommendations::verifiedUserIds() issue a whereHas('links') query with leading-wildcard LIKE conditions plus inRandomOrder() on every request. The sibling famousUsers path caches its id pool ('top-50-users' via Cache::remember until endOfDay) and randomizes over the cached ids; the verified path skips that treatment entirely.

**Why it matters**

ORDER BY RAND() forces MySQL to scan every row matching the verified/sponsor filter and filesort the set to pick 2 users, and the leading-wildcard LIKE inside the correlated EXISTS cannot use an index. The candidate set is curated (verified + sponsors) and therefore small - measured at 0 rows locally, realistically dozens to low hundreds in production - so per-request cost is sub-millisecond today. The value of the fix is consistency with the cached famousUsers pattern and immunity if the verified set is ever opened up (e.g. self-serve verification), at which point this becomes a per-render scan of an unbounded set.

**Evidence**

- `file` — 
- `file` — 
- `file` — 
- `file` — 
- `file` — 

**Affected resources**

- `app/Livewire/Home/Users.php`
- `app/Services/PeopleToFollowRecommendations.php`
- `users table`
- `links table`

**Recommendation**

Cache the verified-users id pool the same way famousUsers caches 'top-50-users' (Cache::remember, endOfDay or multi-hour TTL), then pick the 2 random ids in PHP (array_rand). Apply the same treatment to PeopleToFollowRecommendations::verifiedUserIds().

**Remediation**

Implemented: Home\Users::verifiedUsers() now caches the verified-id pool ('verified-user-ids', endOfDay TTL, same pattern as 'top-50-users') and randomizes over it with whereIn. PeopleToFollowRecommendations::verifiedUserIds() caches a per-authenticated-user pool ('verified-user-ids:{authId}') built without excludeIds, then applies exclusions in PHP and picks via shuffle+slice - distribution identical to the previous inRandomOrder query.

**Verification notes**

Severity corrected during review: initially rated medium on hot-path frequency alone. Runtime data check (database-query) measured the candidate set at 0 verified users locally (4,594 users, 236 links total); production set is curated and expected to stay small, so amplification is bounded and per-request cost is sub-millisecond. Kept as low because the fix is nearly free, matches the shipped famousUsers caching pattern, and future-proofs against the verified set growing. Verified famousUsers() already uses the cached-pool + PHP-random pattern, so the fix is behaviorally consistent with shipped code. Verified defaultUsers() shuffles results afterwards (line 83), so no ordering contract exists. No other runtime measurements taken; impact described by mechanism.

### [INFO] User search uses leading-wildcard LIKE over name and username on every debounced keystroke `F-2026-P002`

**Rule:** `AUD-PER-003` — Performance
**Severity:** Info
**Confidence:** Medium
**Status:** Open

**Summary**

Home\Users::usersByQuery() filters users with whereAny(['name','username'], 'like', "%{$query}%"). A leading wildcard prevents B-tree index usage, so each search (debounced at 500ms) scans the users table. Results are bounded by limit(10) but the scan is not.

**Why it matters**

Search is user-initiated and debounced, so impact today is modest, but scan cost grows linearly with user count on a home-page widget. If search volume or user count grows, the structural fix is a FULLTEXT (or trigram) index; a regular index cannot serve leading-wildcard LIKE.

**Evidence**

- `file` — 
- `file` — 
- `file` — 

**Affected resources**

- `app/Livewire/Home/Users.php`
- `users table`

**Recommendation**

Consider a FULLTEXT index on users (name, username) with a whereFullText search path once search volume or user count justifies it. Not urgent at current scale.

**Remediation**

Migration: $table->fullText(['name', 'username']); then switch usersByQuery() to ->whereFullText(['name', 'username'], $query) when needed.

**Verification notes**

Checked the query is debounced (500ms), user-initiated, and result-bounded - which is why this is info, not medium. No runtime measurement; cost described by mechanism.

