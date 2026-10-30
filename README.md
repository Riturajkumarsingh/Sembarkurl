# URL Shortener

A multi-company URL shortener built with Laravel 11. Users belong to companies and join strictly through email invitations. There is no public self-registration.

## Role Permissions

| Role | Create URLs | View URLs | Can Invite | Notes |
| --- | --- | --- | --- | --- |
| SuperAdmin | No | All companies | Yes | Can invite an Admin into a new company. Has no company (`company_id = null`). |
| Admin | Yes | Own company only | Yes | Can invite Admins or Members into their own company only. |
| Member | Yes | Own URLs only | No | Cannot see URLs created by colleagues in the same company. |

## Tech Stack

- PHP 8.2+
- Laravel 11
- MySQL
- Laravel Breeze (Blade stack, login/logout only)

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
Make sure MySQL is running (e.g. in XAMPP on port 3306), create the database `urlshortener`, and set your `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=urlshortener
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
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

### Public Redirect and Hit Counter
Visiting `/s/{code}` calls `$shortUrl->increment('hits')` atomically and redirects (302) to the original URL. If the code does not exist, a 404 is returned.

### Date Filter
The short URL lists allow filtering by calendar intervals:
- `today`: URLs created today
- `last_week`: URLs created in the previous calendar week
- `this_month`: URLs created in the current calendar month
- `last_month`: URLs created in the previous calendar month

The filter logic lives in `ShortUrl::scopeInInterval()` and persists across pagination links using `withQueryString()`.

### Streamed CSV Export
The `/short-urls/export` endpoint uses `response()->streamDownload()` and `fputcsv()`. It reads records in chunks of 500 to keep memory usage low. The export applies the same visibility and interval scopes as the web view.

## AI Usage

Used ChatGPT for Laravel syntax lookups, initial route scaffolding, and debugging. Application logic, authorization rules, and database schema were implemented manually.
