# Laravel Auditor Report

**Generated:** 2026-08-20 18:03:31

## Project

- **name:** Pinkary
- **environment:** local
- **php version:** 8.4.23
- **laravel version:** v13.24.0
- **database:** mysql
- **test framework:** pest
- **frontend:** detected, detected, 1

## Summary

**Findings:** 4

| Severity | Count |
| --- | --- |
| Critical | 0 |
| High | 2 |
| Medium | 2 |
| Low | 0 |
| Info | 0 |

| Domain | Count |
| --- | --- |
| Security | 1 |
| Database | 1 |
| Architecture | 1 |
| Performance | 1 |

## Priority synthesis

**Final partition:** 4 unique recommendation(s). Every promoted ID appears exactly once.

- **P0 - correctness, security, or data-loss risk** (2): F-2026-0001, F-2026-0002
- **P1 - concrete correctness or high-leverage contract work** (2): F-2026-0004, F-2026-0003
- **P2 - material invariant improvements with narrower impact** (0): none
- **P3 - lower-impact telemetry, diagnostics, or maintainability** (0): none

## Domains Audited

- Security
- Performance
- Architecture
- Database
- Testing
- Laravel conventions

## Findings

### [HIGH] Cross-account notification deletion via unscoped route binding `F-2026-0001`

**Rule:** `AUD-SEC-001` — Security
**Severity:** High
**Confidence:** Confirmed
**Status:** Open

**Summary**

GET notifications/{notification} binds a DatabaseNotification by UUID and deletes it without verifying that the current user is the notification's recipient (notifiable_id/notifiable_type). Any authenticated user who obtains another user's notification UUID can delete that notification via the redirect that fires when the linked question has an answer.

**Why it matters**

This is a missing authorization boundary on a mutating action. The endpoint is reachable by any authenticated user, no Notification policy is registered, and the UUIDs leak through browser history, screenshots, and logs. A wrong account can silently delete a notification the owner never acted on — a cross-account data mutation (AUD-SEC-001). Ownership of the notification/delete behavior is also unclear (AUD-DSA-002).

**Evidence**

- `file` — routes/web.php:52-57
  - notifications routes are only behind ['auth','verified']; GET notifications/{notification} has no can: or ownership scoping.
- `file` — app/Http/Controllers/NotificationController.php:25-38
  - show(DatabaseNotification $notification) reads $notification->data['question_id'], then calls $notification->delete() when the question is answered, with no comparison of notifiable_id to auth()->id().
- `other` — policies_authorization context
  - No policy is registered for DatabaseNotification; registered policies are Bookmark, Like, Link, Question, User.
- `test` — tests/Http/Notifications/ShowTest.php
  - Only exercises the notification owner (guest redirect, owner deletes, owner keeps). No test asserts a different user's notification is not deleted.

**Affected resources**

- `app/Http/Controllers/NotificationController.php`
- `routes/web.php`
- `tests/Http/Notifications/ShowTest.php`

**Recommendation**

Scope the lookup to the authenticated user before mutating, e.g. auth()->user()->notifications()->findOrFail($notification->id), or add a NotificationPolicy registered as the authorization boundary for DatabaseNotification.

**Remediation**

1) Resolve the notification through the current user: $notification = $request->user()->notifications()->findOrFail($notification->id) so a foreign UUID is a 404 instead of a deletable record. 2) Alternatively register a policy for DatabaseNotification and attach can:show,notification (or similar) middleware on the route. 3) Add a test: user B requests user A's answered-question notification → notification is neither deleted nor visible.

**Verification notes**

Verified against routes/web.php (group middleware), NotificationController.php source, the registered policies list, and the ShowTest.php coverage gap. The deletion is reachable for any authenticated user who supplies a valid DatabaseNotification UUID.

### [HIGH] Poll one-vote-per-user-per-poll invariant is not enforceable at the database level `F-2026-0002`

**Rule:** `AUD-DSA-001` — Database
**Severity:** High
**Confidence:** Confirmed
**Status:** Open

**Summary**

poll_votes has no (user_id, question_id) uniqueness. The only unique index is [user_id, poll_option_id], so the same user can hold rows for multiple options of the same poll. PollVoting::vote() enforces the one-vote rule with a check-then-insert read, which races: two concurrent votes for different options of the same poll both pass the check and both insert.

**Why it matters**

A user can end up with multiple votes in a single poll under a double-click, retry, or parallel request, because MySQL will happily store the second row. Poll results are silently inflated and the invalid state combination (AUD-DSA-001) has no DB-level guard, so it cannot be fixed by app logic alone under concurrency.

**Evidence**

- `migration` — database/migrations/2025_07_18_150156_create_poll_votes_table.php
  - unique(['user_id','poll_option_id']) at line 25; no question_id column, so no one-row-per-poll constraint exists.
- `other` — Live MySQL schema: poll_votes
  - Indexes: primary, poll_votes_poll_option_id_index, unique(poll_votes_user_id_poll_option_id_unique). No (user_id, question_id) key.
- `file` — app/Livewire/Questions/PollVoting.php:55-73
  - vote() reads an existing vote (whereHas pollOption.question_id) then PollVote::create(...) outside any transaction/lock; two concurrent requests both see 'no existing vote' and both insert.
- `file` — app/Models/PollVote.php:56-60
  - Question identity is only reachable through pollOption.question; PollVote stores no question_id.
- `test` — tests/Unit/Livewire/Questions/PollVotingTest.php
  - Covers sequential vote/change/remove flows only; no concurrent-duplicate-vote test exists.

**Affected resources**

- `database/migrations/2025_07_18_150156_create_poll_votes_table.php`
- `app/Models/PollVote.php`
- `app/Models/PollOption.php`
- `app/Livewire/Questions/PollVoting.php`
- `tests/Unit/Livewire/Questions/PollVotingTest.php`
- `database/factories/PollVoteFactory.php`

**Recommendation**

Denormalize question_id onto poll_votes and make (user_id, question_id) a unique index, then make vote() atomic (insert-or-ignore inside a transaction or catch the unique violation) so the invariant is enforced at the storage layer, not by a racy read.

**Remediation**

1) New migration: add poll_votes.question_id (nullable), backfill from poll_options.question_id, set NOT NULL, add unique(['user_id','question_id']), and drop the now-redundant [user_id, poll_option_id] unique if desired. 2) Update PollVote model casts/relations and the PollVoteFactory to set question_id (derive from the poll option). 3) Rewrite vote() to use an atomic insert that reports whether the row was created (insertOrIgnore + affected, or catch the unique violation) and only increment/decrement votes_count for an actually created/deleted row. 4) Add a concurrency-oriented test (two 'vote' calls for different options of the same poll asserting only one row exists).

**Verification notes**

Verified via the migration file, the live MySQL schema (unique index set), PollVoting::vote() control flow, and the model definitions. The race window is a plain check-then-insert with no lock/transaction. Laravel's session-blocking is OFF here (config('session.block') defaults to false, no 'block' key in config/session.php), so not even same-session double-click requests are serialized; the finding does not depend on this win-dow anyway because the schema permits the invalid state unconditionally.

### [MEDIUM] Notifications index performs per-notification question queries and deletes rows during render `F-2026-0004`

**Rule:** `AUD-PER-001` — Performance
**Severity:** Medium
**Confidence:** Confirmed
**Status:** Open

**Summary**

The notifications page loads all of the user's notifications with no pagination or eager loading, then the Blade view runs Question::find() — plus lazy from/to/parent relation loads — once per notification, and deletes 'orphan' notifications as a side effect of rendering.

**Why it matters**

Rendering the page issues O(notifications × relations) queries, and Livewire re-renders re-run the whole block every time a notification event fires. The delete-during-render is a write performed inside a GET render path, which Livewire can trigger repeatedly and which is surprising ownership for a view. The list is unbounded, so this grows over time.

**Evidence**

- `file` — resources/views/livewire/notifications/index.blade.php:13-23
  - foreach loads Question::find($notification->data['question_id']) per item and deletes the notification when the question is null (render-time mutation).
- `file` — resources/views/livewire/notifications/index.blade.php:37-101
  - Lazy relation access $question->from, $question->to, $question->parent, $question->parent->* per notification row.
- `file` — app/Livewire/Notifications/Index.php:45-51
  - render() returns $user->notifications()->get() with no with(), no limit, no simplePaginate.

**Affected resources**

- `app/Livewire/Notifications/Index.php`
- `resources/views/livewire/notifications/index.blade.php`

**Recommendation**

Eager-load the questions (and their from/to/parent relations) for the notification batch and move orphan-notification cleanup out of the render path into a scheduled or deferred cleanup job.

**Remediation**

1) Eager-load in the component, e.g. ->with(['question.from', 'question.to', 'question.parent']) on a scoped notifications query (or fetch the referenced questions in one whereIn). 2) Paginate the notifications list (matches the Livewire feed convention of simplePaginate). 3) Move the null-question delete into a one-off cleanup (dispatch a job or add a periodic command) and make render strictly read-only. 4) Add a test asserting the page renders N notifications within a bounded number of queries.

**Verification notes**

Verified directly in notifications/index.blade.php and app/Livewire/Notifications/Index.php. The N+1 and render-time delete are both present; severity kept at medium because notification lists are typically small.

### [MEDIUM] Queued avatar jobs lack per-user generation ownership so a stale job can overwrite or wipe a newer avatar `F-2026-0003`

**Rule:** `AUD-DSA-001` — Architecture
**Severity:** Medium
**Confidence:** High
**Status:** Open

**Summary**

UpdateUserAvatar captures the user at dispatch time and, in handle(), deletes whatever the captured model points to, writes a new avatar, then blindly updates user.avatar. There is no generation/version guard, so when two jobs for the same user are queued (e.g. GitHub connect then upload), the older job can run last and overwrite — or, via the default-avatar branch or failed(), null out — a newer avatar.

**Why it matters**

Last-writer-wins depends on queue execution order instead of the user's most recent intent. failed() unconditionally nulls avatar/avatar_updated_at, so a stale job's failure wipes the current avatar. Avatar state can silently revert or disappear.

**Evidence**

- `file` — app/Jobs/UpdateUserAvatar.php:38-77
  - handle() deletes the captured avatar file (lines 40-42), writes a new file, then updates avatar/avatar_updated_at unconditionally (lines 71-75); lines 48-56 null the avatar when the resolved source is the default avatar.
- `file` — app/Jobs/UpdateUserAvatar.php:85-94
  - failed() always nulls avatar, avatar_updated_at, is_uploaded_avatar for the user.
- `file` — app/Http/Controllers/UserAvatarController.php:19-42
  - Dispatch sites: RegisteredUserController.php:59, UserGitHubUsernameController.php:64, UserController.php:57, Livewire/Links/Index.php:84 (async); UserAvatarController.php:23,34 (dispatchSync). Multiple async sites can queue jobs for the same user.
- `test` — tests/Unit/Jobs/UpdateUserAvatarTest.php
  - Exercises single-job behavior only; no interleaved/stale job scenario.

**Affected resources**

- `app/Jobs/UpdateUserAvatar.php`
- `app/Http/Controllers/UserAvatarController.php`
- `app/Http/Controllers/UserGitHubUsernameController.php`
- `app/Http/Controllers/RegisteredUserController.php`
- `app/Http/Controllers/UserController.php`
- `app/Livewire/Links/Index.php`
- `tests/Unit/Jobs/UpdateUserAvatarTest.php`

**Recommendation**

Add an avatar generation token (e.g. compare against user.avatar_updated_at) at the start of handle(); skip the delete/update when the job's snapshot is older than the current avatar_updated_at, and make failed() only reset the avatar when the failing job is the newest.

**Remediation**

1) In handle(), read $this->user->fresh()->avatar_updated_at and abort early when it is newer than the job's captured value. 2) Pass avatar_updated_at (or a generation value) into the job and guard the DB update with where('avatar_updated_at', capturedValue). 3) In failed(), only null the avatar when the fresh avatar_updated_at still matches this job's generation. 4) Add a test queueing an older-then-newer pair and asserting the newer avatar wins.

**Verification notes**

Verified against the job source, all five dispatch sites, and the job test file. The race is real whenever two jobs for one user coexist. Production explicitly uses a real queue: QUEUE_CONNECTION=database in .env.example and the README documents php artisan queue:work on Laravel Cloud (MySQL queue) — multiple workers can process jobs out of order and retries/backoff let an old job run after a newer one completed. Note: the local .env expects QUEUE_CONNECTION=sync, so this cannot be reproduced on local dev unless queues are switched on; exploitability therefore applies to production only.

