# CLAUDE.md — sistem-terpadu (SISTA backend + web)

Laravel 13: the school web app plus the API behind the Android app (`Kemalhafizh/sista-android`). Read this before changing anything; the rules below are why recent PRs exist, and CI enforces several of them.

## Aturan main

### 1. Tidak ada angka karangan
The database is the source of truth. When a figure has no data behind it, send `null` (or an honest error), never a made-up number.
- No `rand()`, Faker, `->random()` or invented defaults dressed up as data: a 0% attendance rate, "0 dari 0 juz", today's class size used for a past semester, a sample file name.
- "Belum ada data" is `null`; the client renders "–". A real zero (nothing was paid this month) stays `0`.
- **CI guard:** `tests/Feature/Architecture/NoFabricatedRandomnessTest.php` fails on any new random call in `app/`. Its `DEBT` list holds the made-up values that existed when the guard arrived. Fix one, then lower or remove its entry; the counts are exact, so the list only shrinks. Legitimate randomness (shuffling an exam with a seed, CSPRNG secrets) goes in `ALLOWED` with the reason.

### 2. Aksi harus tersimpan
A button that changes something must change it on the server. Don't let the API answer "berhasil disimpan" for something that wasn't saved, or return an id that doesn't exist. If a feature has no backend yet, say so (404/501 with a message) instead of pretending.

### 3. Satu aturan akses, untuk web dan API
Every role and every client reads the same rule; don't copy role lists into controllers.
- Role spellings vary (`student`/`siswa`, `parent`/`orang_tua`/`orangtua`/`wali_murid`, `guru`/`teacher`). Use `App\Support\Access\StudentDataAccess` (`STUDENT_ROLES`, `PARENT_ROLES`, `TEACHER_ROLES`, `STAFF_ROLES`), never a literal like `'parent'`.
- What a user may open comes from the route's own `role:` middleware: `RouteAccess`, and `MobileCapabilities` for the app (`config/mobile_features.php`).
- Only `superadmin` bypasses access rules. `admin` does not.
- Scope data to the caller: a parent sees only their own children, a teacher only the classes they teach this academic year, a student only themself. Look records up by id or uuid **and** owner, never by name alone. Names repeat across academic years ("XI MIPA 2" exists every year), and `first()` on a name picks an arbitrary one.
- Shared rules live on the model, e.g. `Announcement::visibleTo($user)` and `WorkflowRequest::awaitingDecisionBy($user)`. Reuse them; don't re-implement them.

### 4. Setiap PR punya test
- Every behaviour change ships with a Pest/PHPUnit test that fails without it: the bug, the empty case (`null`, not 0), and the access rule (another role or another family gets 403/404).
- Don't weaken or delete a test to get green. If a test encoded the old wrong behaviour, rewrite it to the correct one and say so in the PR.

## Mobile API
- Keep fields the Android app reads; add new ones beside them. Changing a meaning or removing a field needs a matching sista-android PR, linked in both PR descriptions.
- Coordinate before changing `analytics/*`, `announcements*`, `notifications*`, `mobile/*` and `parent/*`: the app depends on them.
- Use `ApiResponseTrait` (`success`, `message`, `data`) where the endpoint already does. Error messages are Indonesian and meant for the user; the app shows them as they are.
- Times are ISO-8601 (`toIso8601String()`). Don't send only `diffForHumans()`.
- Files on the `public` disk: `Storage::disk('public')->url(...)`.

## Sebelum push
```bash
vendor/bin/pint                      # CI runs pint --test
php artisan test                     # CI runs the whole suite
vendor/bin/phpstan analyse           # level 0 with phpstan-baseline.neon
```
- `reportUnmatchedIgnoredErrors` is on. Typing a relation (`/** @return BelongsTo<X, $this> */`) can make baseline entries stale; lower or remove them in the same PR.
- Known local-only failure: `Fase85DebloatPragmatismTest > single vps` needs the production host. It passes in CI.

## Git
- Commit messages and PR descriptions in Indonesian. Describe what was wrong, why, and what changed.
- No attribution lines (`Co-Authored-By`, `Claude-Session`) in commits, and no "Generated with…" footer or session link in PR descriptions.
- Don't merge PRs or rewrite `main`. Never commit `.env` or credentials.
