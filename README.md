# URL Shortener

A multi-tenant URL Shortener assignment built with Laravel 11. Companies (clients) are onboarded through an invitation-only workflow; users never self-register.

---

## Features by Role

| Role | Create Short URLs | View Short URLs | Invite Users | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **SuperAdmin** | No (403 Forbidden) | All companies | Admin (creates new company) | No company affiliation (`company_id = null`) |
| **Admin** | Yes | Own company only | Admin, Member (own company only) | Extra "Created By" column on URL list |
| **Member** | Yes | Own URLs only | No (403 Forbidden) | Cannot see colleague URLs in same company |
| **Sales** | No (403 Forbidden) | None (403 Forbidden) | No (403 Forbidden) | Valid role with no URL permissions |
| **Manager** | No (403 Forbidden) | None (403 Forbidden) | No (403 Forbidden) | Valid role with no URL permissions |

---

## Tech Stack

- **PHP**: 8.2+
- **Framework**: Laravel 11
- **Database**: MySQL (XAMPP compatible) and SQLite (switchable via `.env` only)
- **Authentication**: Laravel Breeze (Blade stack) — login and logout only (self-registration and password resets removed)
- **Frontend**: Semantic HTML Blade templates with unified layout, responsive forms, CSRF tokens, flash messages, and validation feedback
- **Test Suite**: Pest Feature Tests (28 tests, 73 assertions, RefreshDatabase)
- **Code Style**: PSR-12 standard (formatted via Laravel Pint)

---

## Local Setup

### 1. Clone & Install Dependencies
```bash
git clone <repo-url>
cd sembarkurlshort
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup

#### Option A: MySQL (Default with XAMPP)
Ensure MySQL is running (e.g. in XAMPP on port 3306), create the database, and verify `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=urlshortener
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
```

#### Option B: SQLite
To use SQLite instead, set the following in `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=
```
And create the SQLite file:
```bash
touch database/database.sqlite
```

### 4. Run Migrations & Seeders
```bash
php artisan migrate --seed
```

### 5. Start Development Server
```bash
php artisan serve
```
Open **[http://127.0.0.1:8000](http://127.0.0.1:8000)** in your browser.

---

## Seeded SuperAdmin Credentials

| Field | Default Value | Config Key | Environment Variable |
| :--- | :--- | :--- | :--- |
| **Email** | `superadmin@urlshortener.test` | `superadmin.email` | `SUPERADMIN_EMAIL` |
| **Password** | `password` | `superadmin.password` | `SUPERADMIN_PASSWORD` |

> As required, the bootstrap SuperAdmin is created in `DatabaseSeeder.php` using **raw SQL** (`DB::insert`) with bound parameters and `Hash::make()` — bypassing Eloquent models and factories.

---

## How to Test the Invitation Flow (Without a Mail Server)

The application uses the `log` mail driver (`MAIL_MAILER=log`), so no SMTP server is required for testing:

1. Log in as **SuperAdmin** and click **"Invite New Client"** on the dashboard.
2. Enter a Client (Company) name and the Admin's email, then submit.
3. Upon submission:
   - The direct invitation acceptance link is displayed in an informational flash alert on the page.
   - The same link is written to `storage/logs/laravel.log` via the `InvitationMail` mailable.
4. Open the invitation link in an incognito window (`/invitations/{token}`).
5. Set a password with confirmation and submit.
6. The user account is created with the invited role and company, and logged in directly.
7. Attempting to use an expired token, an already-accepted token, or an existing user's email will be rejected.

---

## Running Automated Tests

Tests are written using Pest and execute against an in-memory SQLite database (`:memory:`) via `phpunit.xml`, ensuring the local development database is never wiped or altered during test execution:

```bash
php artisan test
```

Or for clean output without code coverage overhead:
```bash
php artisan test --no-coverage
```

### Test Coverage Summary (28 Feature Tests, 73 Assertions):
- Admin and Member can create short URLs.
- SuperAdmin, Sales, and Manager cannot create short URLs (assert 403).
- SuperAdmin views short URLs across all companies, but cannot create.
- Admin views only short URLs of their own company.
- Member views only short URLs created by themselves.
- Short URLs are publicly resolvable without login and redirect (`assertRedirect`); unknown code returns 404.
- Atomic hit counter increments by 1 per visit.
- SuperAdmin invites an Admin with a new company within a database transaction.
- Admin invites Admin or Member within their own company only.
- Member cannot invite (assert 403).
- Accepting an invitation creates the user with the correct role/company; expired and reused tokens are rejected with 410.
- SuperAdmin seeder verification (role `SuperAdmin`, `company_id = null`).
- Date filter returns only URLs inside selected calendar interval.
- CSV export streams only rows visible to the authenticated user.
- SuperAdmin Clients page aggregates company member count and hit totals.

---

## Routes

| Method | URI | Route Name | Middleware | Description |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/` | — | None | Redirects to `/dashboard` |
| `GET` | `/login` | `login` | `guest` | Displays login form |
| `POST` | `/login` | — | `guest` | Authenticates user |
| `POST` | `/logout` | `logout` | `auth` | Terminates session |
| `GET` | `/dashboard` | `dashboard` | `auth` | Renders role-specific dashboard |
| `GET` | `/short-urls` | `short-urls.index` | `auth` | Paginated short URLs table |
| `GET` | `/short-urls/create` | `short-urls.create` | `auth` | Short URL generation form |
| `POST` | `/short-urls` | `short-urls.store` | `auth` | Validates & generates short URL |
| `GET` | `/short-urls/export` | `short-urls.export` | `auth` | Streamed CSV download |
| `GET` | `/invitations/create` | `invitations.create` | `auth` | Role-based invite form |
| `POST` | `/invitations` | `invitations.store` | `auth` | Generates token & sends invite |
| `GET` | `/invitations/{token}` | `invitations.accept` | Public | Invitation acceptance & password form |
| `POST` | `/invitations/{token}` | `invitations.accept.store` | Public | Creates account & logs in user |
| `GET` | `/clients` | `clients.index` | `auth` | SuperAdmin client companies overview |
| `GET` | `/team` | `team.index` | `auth` | Admin team members list |
| `GET` | `/s/{code}` | `redirect` | Public | Resolves short URL, increments hit, redirects |

---

## Database Schema

```
companies
├── id (bigint, PK)
├── name (string)
└── timestamps

users
├── id (bigint, PK)
├── company_id (nullable foreignId -> companies, nullOnDelete)
├── name (string)
├── email (string, unique)
├── password (string)
├── role (string, default 'Member')
├── remember_token (string, nullable)
├── email_verified_at (timestamp, nullable)
└── timestamps

short_urls
├── id (bigint, PK)
├── company_id (foreignId -> companies, cascadeOnDelete)
├── user_id (foreignId -> users, cascadeOnDelete)
├── original_url (text)
├── short_code (string 10, unique)
├── hits (unsignedBigInteger, default 0)
└── timestamps
Indexes: (company_id, created_at), (user_id, created_at)

invitations
├── id (bigint, PK)
├── company_id (foreignId -> companies, cascadeOnDelete)
├── invited_by (foreignId -> users, cascadeOnDelete)
├── name (string, nullable)
├── email (string)
├── role (string)
├── token (string 64, unique)
├── expires_at (timestamp)
├── accepted_at (timestamp, nullable)
└── timestamps
```

---

## Key Features

### 1. Atomic Hits Counter
Every visit to `/s/{code}` calls `$shortUrl->increment('hits')` atomically before redirecting via HTTP 302 to the original destination URL. This avoids concurrency race conditions under high traffic.

### 2. Calendar Interval Date Filter
The URL lists and dashboard include a date filter supporting:
- **Today** (`Carbon::today()`)
- **Last Week** (Previous calendar week boundary)
- **This Month** (Current calendar month and year)
- **Last Month** (Previous calendar month and year)
- **All Time** (Unfiltered)

The filter uses a dedicated `scopeInInterval` local scope on `ShortUrl` and persists in pagination links across pages via `withQueryString()`.

### 3. Streamed CSV Export
The `/short-urls/export` route streams CSV records on the fly using `response()->streamDownload` and `fputcsv()`. Records are fetched in chunks of 500 to keep memory footprint flat regardless of table size. The export applies the exact same visibility scope and interval filter as the page view, preventing data leakage.

---

## Design Decisions

### Policy + Reusable Query-Scope Architecture
Authorization is structured in two distinct, complementary layers:

1. **Policies (`ShortUrlPolicy`, `InvitationPolicy`)**:  
   Answer the question *"Can this user perform this action at all?"*  
   Enforced at controller boundaries via `$this->authorize()` and in Blade views via `@can`. Unauthorized requests receive an immediate 403 Forbidden before executing any queries.

2. **Query Scopes (`ShortUrl::scopeVisibleTo(User $user)`)**:  
   Answers the question *"Which records is this user permitted to see?"*  
   - **SuperAdmin**: Returns all records across companies.
   - **Admin**: Restricts to `company_id = $user->company_id`.
   - **Member**: Restricts to `user_id = $user->id`.
   - **Other roles**: Restricts to empty set (`1 = 0`).  
   This ensures identical multi-tenant isolation across web dashboards, paginated index views, and streamed CSV exports.

### Invitation-Only Onboarding
Self-registration and public password-reset routes provided by default authentication scaffolding have been removed. User creation is strictly gated through invitation tokens with database transactions linking the newly created user to their designated company and role upon acceptance.

### Short Code Collision Handling
`App\Services\ShortCodeGenerator` produces 6–8 character alphanumeric strings with a collision retry threshold (up to 10 attempts), reinforced by a database-level `unique` constraint on the `short_code` column as a definitive safety net.

---

## AI Usage

Used **Antigravity (Google DeepMind)** for Laravel syntax lookups, initial scaffolding structure, and debugging test environment configurations. All architectural decisions, business rules implementation, policy/scope design, and test suites were written and verified for this assignment.
