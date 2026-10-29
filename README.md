# URL Shortener

A multi-tenant URL shortener built with Laravel 11. Companies (clients) are onboarded through an invitation-only flow — users never self-register.

## Features by Role

| Role | Create URLs | View URLs | Invite | Notes |
|------|-------------|-----------|--------|-------|
| SuperAdmin | No | All companies | Admin (new company only) | No company affiliation |
| Admin | Yes | Own company | Admin, Member (own company) | |
| Member | Yes | Own URLs only | No | |
| Sales | No | No | No | Reserved role, no URL permissions |
| Manager | No | No | No | Reserved role, no URL permissions |

## Tech Stack

- PHP 8.2, Laravel 11.57
- SQLite by default (swap to MySQL by changing `.env` only — no code changes)
- Laravel Breeze — Blade stack, login/logout only (registration removed)
- Pest v2 test suite (28 feature tests)
- Laravel Pint (PSR-12 formatting)

## Local Setup

```bash
git clone <repo-url>
cd sembarkurlshort

composer install

cp .env.example .env
php artisan key:generate

# SQLite (default — no extra config needed):
touch database/database.sqlite
php artisan migrate --seed

# MySQL alternative: edit DB_* values in .env, then:
# php artisan migrate --seed

php artisan serve
```

Open http://localhost:8000 and log in with the seeded SuperAdmin account.

## Seeded SuperAdmin Credentials

| Field    | Default                            | Override via `.env`      |
|----------|------------------------------------|--------------------------|
| Email    | `superadmin@urlshortener.test`     | `SUPERADMIN_EMAIL`       |
| Password | `password`                         | `SUPERADMIN_PASSWORD`    |

The seeder uses raw `DB::insert` with bound parameters and `Hash::make()` — no Eloquent or factories, as required.

## Testing the Invitation Flow (no mail server needed)

The app is configured with `MAIL_MAILER=log`, so no SMTP server is required.

1. Log in as SuperAdmin and click **Invite New Client**.
2. Fill in the client name + invitee email and submit.
3. After submission, the acceptance link appears on the page in a blue info box — copy it.
4. The same link is written to `storage/logs/laravel.log` (search for `InvitationMail`).
5. Open the link in an incognito window, set a password, and the new user is logged in immediately.

An Admin inviting a team member works the same way — the link is shown on the dashboard after submitting.

## Running Tests

```bash
php artisan test
# or with verbose output:
php artisan test --no-coverage
```

All 28 tests should pass. The suite covers:

- URL creation permissions per role
- Visibility scoping (SuperAdmin sees all, Admin sees company, Member sees own)
- Public redirect and hit counter
- Invitation creation, acceptance, expiry, duplicate rejection
- SuperAdmin seeder correctness
- Date filter (this_month / last_month)
- CSV export scope isolation
- Clients page aggregates

## Routes

| Method | URI | Route Name | Auth | Description |
|--------|-----|-----------|------|-------------|
| GET | `/login` | `login` | Guest | Login form |
| POST | `/login` | — | Guest | Submit credentials |
| POST | `/logout` | `logout` | Auth | Log out |
| GET | `/dashboard` | `dashboard` | Auth | Role-based dashboard |
| GET | `/short-urls` | `short-urls.index` | Auth | All short URLs list |
| GET | `/short-urls/create` | `short-urls.create` | Auth | Generate form |
| POST | `/short-urls` | `short-urls.store` | Auth | Store new short URL |
| GET | `/short-urls/export` | `short-urls.export` | Auth | CSV download |
| GET | `/invitations/create` | `invitations.create` | Auth | Invite form |
| POST | `/invitations` | `invitations.store` | Auth | Send invitation |
| GET | `/invitations/{token}` | `invitations.accept` | Public | Accept invitation form |
| POST | `/invitations/{token}` | `invitations.accept.store` | Public | Create account from invitation |
| GET | `/clients` | `clients.index` | Auth | SuperAdmin clients list |
| GET | `/team` | `team.index` | Auth | Admin team list |
| GET | `/s/{code}` | `redirect` | Public | Resolve and redirect short URL |

## Database Schema

```
companies     id, name, timestamps
users         id, company_id (nullable FK→companies, nullOnDelete),
              name, email, password, role (string, default Member),
              remember_token, email_verified_at, timestamps
short_urls    id, company_id (FK→companies, cascadeOnDelete),
              user_id (FK→users, cascadeOnDelete),
              original_url (text), short_code (string 10, unique),
              hits (unsigned bigint, default 0), timestamps
              Indexes: (company_id, created_at), (user_id, created_at)
invitations   id, company_id (FK→companies, cascadeOnDelete),
              invited_by (FK→users, cascadeOnDelete),
              name (nullable), email, role, token (64 chars, unique),
              expires_at, accepted_at (nullable), timestamps
```

### Role Enum

`App\Enums\Role` is a backed string enum: `SuperAdmin | Admin | Member | Sales | Manager`.  
The `role` column on `users` and `invitations` is cast to this enum automatically by Eloquent.

## Features

### Hits Counter
Every visit to `/s/{code}` calls `$shortUrl->increment('hits')` atomically before redirecting. This avoids race conditions on high-traffic links compared to a read-modify-write cycle.

### Date Filter
The URL list and dashboard support four intervals: **Today**, **Last Week**, **This Month**, **Last Month**. The filter uses `scopeInInterval` on `ShortUrl`, which applies Carbon boundaries for precise calendar-week and calendar-month ranges. The selected interval persists in the query string across paginator pages via `withQueryString()`.

### CSV Export
`/short-urls/export` streams the response with `response()->streamDownload` and `fputcsv`, chunking 500 rows at a time to keep memory usage flat regardless of data size. The export applies the **same** `scopeVisibleTo` and `scopeInInterval` as the list page, so a user can never export rows they cannot see.

## Design Decisions

### Policy + Query-Scope Approach

Authorization is enforced in two complementary layers:

**1. Policies** (`ShortUrlPolicy`, `InvitationPolicy`) answer _"can this user perform this action at all?"_ — checked in controllers via `$this->authorize()` and in Blade via `@can`. This is the gate: a forbidden user gets a 403 before any query runs.

**2. `ShortUrl::scopeVisibleTo(User $user)`** answers _"which rows can this user see?"_ — a single reusable Eloquent scope applied to every query that returns URLs. The same scope is used by the dashboard, the index page, and the CSV export. There is no way to bypass it by changing a query parameter.

Keeping these two concerns separate — "can you do this?" vs. "what can you see?" — makes each part easy to test and reason about independently.

### Invitation-Only Registration

Breeze's registration and password-reset routes are removed entirely from `routes/auth.php`. The only way to create an account is through a valid, unexpired, unaccepted invitation token. This enforces the multi-tenant boundary and avoids accidental self-registration.

### Raw SQL for the SuperAdmin Seeder

The assignment explicitly requires `DB::insert` with bound parameters rather than Eloquent or factories. The seeder reads credentials from `config('superadmin.email')` / `config('superadmin.password')`, which are backed by `SUPERADMIN_EMAIL` / `SUPERADMIN_PASSWORD` in `.env`. This means credentials can be changed without touching source code.

### ShortCodeGenerator Service

`App\Services\ShortCodeGenerator` generates 6–8 character alphanumeric codes, retrying up to 10 times on collision. The `short_code` column also has a `unique` database constraint as a hard safety net — if two concurrent requests somehow generate the same code after the retry limit, the database will reject the second insert cleanly.

## AI Usage

Used **Antigravity (Google DeepMind)** for code scaffolding, Laravel syntax lookups, and debugging test failures (notably the Laravel 11 `AuthorizesRequests` trait change in the base controller). Architecture choices, business logic, the policy/scope approach, and design decisions were guided by the assignment brief.
