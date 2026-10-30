# URL Shortener

A multi-company URL shortener built with Laravel 11. Users belong to companies and join strictly through email invitations. There is no public self-registration.

## Role Permissions

| Role | Create URLs | View URLs | Can Invite | Notes |
| --- | --- | --- | --- | --- |
| SuperAdmin | No | All companies | Yes | Can invite an Admin into a new company. Has no company (`company_id = null`). |
| Admin | Yes | Own company only | Yes | Can invite Admins or Members into their own company only. |
| Member | Yes | Own URLs only | No | Cannot see URLs created by colleagues in the same company. |
| Sales | No | No | No | Reserved role with no URL or invitation permissions. |
| Manager | No | No | No | Reserved role with no URL or invitation permissions. |

## Tech Stack

- PHP 8.2+
- Laravel 11
- MySQL (default in `.env`) and SQLite support
- Laravel Breeze (Blade stack, login/logout only)
- Pest feature tests
- Laravel Pint for code styling

## Local Setup

### 1. Clone repository and install dependencies
```bash
git clone <repo-url>
cd sembarkurlshort
composer install
```

### 2. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database configuration

#### Option A: MySQL (Default)
Make sure MySQL is running (e.g. in XAMPP on port 3306), create the database `urlshortener`, and confirm your `.env`:
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
To switch to SQLite, update `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=
```
And create the database file:
```bash
touch database/database.sqlite
```

### 4. Run migrations and seeder
```bash
php artisan migrate --seed
```

### 5. Start the local server
```bash
php artisan serve
```
Open `http://127.0.0.1:8000` in your browser.

## Seeded SuperAdmin Account

| Field | Default Value | Environment Variable |
| --- | --- | --- |
| Email | `superadmin@urlshortener.test` | `SUPERADMIN_EMAIL` |
| Password | `password` | `SUPERADMIN_PASSWORD` |

The SuperAdmin account is created via raw SQL (`DB::insert`) with bound parameters and `Hash::make()` inside `DatabaseSeeder.php`, without using Eloquent or factories.

## Testing the Invitation Flow

The app uses `MAIL_MAILER=log`, so no real mail server is needed.

1. Log in as SuperAdmin (`superadmin@urlshortener.test` / `password`).
2. Click "Invite New Client", enter a company name and email, and submit.
3. The generated acceptance link will appear in a flash message on the page. It is also logged to `storage/logs/laravel.log`.
4. Open the link in an incognito window (`/invitations/{token}`).
5. Enter and confirm a password. The account is created, associated with the company, and automatically logged in.
6. The token is marked accepted. Attempting to reuse or visit an expired token returns a 410 response.

Admins can invite team members to their own company using the same flow from their dashboard.

## Running Tests

Automated tests are written with Pest and use an in-memory SQLite database (`:memory:`) configured in `phpunit.xml` so the development database is never wiped.

```bash
php artisan test --no-coverage
```

All 28 tests pass, covering:
- Role permissions on short URL creation (Admin/Member allowed; SuperAdmin/Sales/Manager forbidden with 403)
- Multi-company visibility isolation (SuperAdmin sees all, Admin sees own company, Member sees only their own)
- Public redirect (`/s/{code}`) and atomic hit counter increments
- Invitation creation, role restrictions, token expiry, reuse prevention, and duplicate email checks
- Date interval filters
- Streamed CSV export authorization and scoping
- SuperAdmin seeder verification

## Routes

| Method | URI | Name | Middleware | Description |
| --- | --- | --- | --- | --- |
| GET | `/` | - | None | Redirects to `/dashboard` |
| GET | `/login` | `login` | `guest` | Login form |
| POST | `/login` | - | `guest` | Authenticate user |
| POST | `/logout` | `logout` | `auth` | Log out |
| GET | `/dashboard` | `dashboard` | `auth` | Role-based dashboard |
| GET | `/short-urls` | `short-urls.index` | `auth` | Paginated URL list |
| GET | `/short-urls/create` | `short-urls.create` | `auth` | URL creation form |
| POST | `/short-urls` | `short-urls.store` | `auth` | Store new short URL |
| GET | `/short-urls/export` | `short-urls.export` | `auth` | Streamed CSV download |
| GET | `/invitations/create` | `invitations.create` | `auth` | Invite form |
| POST | `/invitations` | `invitations.store` | `auth` | Store invitation and send mail |
| GET | `/invitations/{token}` | `invitations.accept` | None | Invitation acceptance page |
| POST | `/invitations/{token}` | `invitations.accept.store` | None | Set password and create user |
| GET | `/clients` | `clients.index` | `auth` | SuperAdmin clients view |
| GET | `/team` | `team.index` | `auth` | Admin team members view |
| GET | `/s/{code}` | `redirect` | None | Public redirect to original URL |

## Database Schema

```
companies
- id (bigint, primary key)
- name (string)
- created_at, updated_at

users
- id (bigint, primary key)
- company_id (bigint, foreign key -> companies, nullable, nullOnDelete)
- name (string)
- email (string, unique)
- password (string)
- role (string, default 'Member')
- remember_token, email_verified_at, timestamps

short_urls
- id (bigint, primary key)
- company_id (bigint, foreign key -> companies, cascadeOnDelete)
- user_id (bigint, foreign key -> users, cascadeOnDelete)
- original_url (text)
- short_code (string 10, unique)
- hits (unsigned big integer, default 0)
- created_at, updated_at
- indexes: (company_id, created_at), (user_id, created_at)

invitations
- id (bigint, primary key)
- company_id (bigint, foreign key -> companies, cascadeOnDelete)
- invited_by (bigint, foreign key -> users, cascadeOnDelete)
- name (string, nullable)
- email (string)
- role (string)
- token (string 64, unique)
- expires_at (timestamp)
- accepted_at (timestamp, nullable)
- created_at, updated_at
```

## Features

### Public Redirect and Atomic Hit Counter
When a user visits `/s/{code}`, the application looks up the record, calls `$shortUrl->increment('hits')` atomically, and issues a 302 redirect to the original URL. If the code does not exist, a 404 is returned.

### Date Filter
The short URL lists allow filtering by calendar intervals:
- `today`: URLs created today
- `last_week`: URLs created in the previous calendar week
- `this_month`: URLs created in the current calendar month
- `last_month`: URLs created in the previous calendar month

The filter logic lives in `ShortUrl::scopeInInterval()` and persists across pagination links using `withQueryString()`.

### Streamed CSV Export
The `/short-urls/export` endpoint uses `response()->streamDownload()` and `fputcsv()`. It reads records in chunks of 500 to keep memory usage low. The export applies the same `scopeVisibleTo()` and `scopeInInterval()` as the web view, preventing users from exporting data outside their permissions.

## Design Decisions

### Policy and Query Scope Separation
Authorization is handled in two separate layers:
1. **Policies (`ShortUrlPolicy`, `InvitationPolicy`)**: Check action-level permissions (e.g. `$this->authorize('create', ShortUrl::class)`). If a user cannot perform an action, the request aborts with a 403 before any query executes.
2. **Query Scope (`ShortUrl::scopeVisibleTo()`)**: Restricts row-level visibility. SuperAdmin sees all records, Admin sees only records for their company, and Member sees only their own URLs. The same scope is used across dashboards, list views, and the CSV export so visibility rules remain consistent in one place.

### Invitation-Only Registration
Standard Breeze registration routes (`/register`) and password reset routes were removed from `routes/auth.php`. User accounts can only be created by accepting a valid invitation token, ensuring every company user is explicitly invited.

### Short Code Generation
`App\Services\ShortCodeGenerator` generates 6 to 8 character alphanumeric strings with a collision retry loop (up to 10 attempts). The `short_code` column has a unique database constraint as a safety net.

## AI Usage

Used ChatGPT / Cursor for Laravel syntax lookups, initial route scaffolding, and debugging test environment setup. Application architecture, database schema, authorization logic, and test cases were written and verified manually for this assignment.
