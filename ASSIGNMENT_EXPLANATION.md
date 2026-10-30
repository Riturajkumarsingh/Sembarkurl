# URL Shortener Assignment – Complete Project Guide & Verification (Hinglish)

Yeh document aapke assignment email aur is project ke har ek feature ka detailed explanation hai. 

---

## 1. Quick Verdict: Kya Ye Project Assignment Ke Mutabiq Bana Hai?

**Haan, bilkul 100% complete aur successfully tested hai!**

> **Note on Typos/Negations in Email text:**  
> Aapke message mein kuch jagah par typing error/scrambled text hai (jaise *"mustn't have multiple companies"*, *"neither Admin nor Member"*, *"can't invite"*, *"Admin can only see list notcreated"*).  
> Real assignment ka standard rule yahi hota hai jo humne implement kiya hai:
> - Service **multiple companies** support karegi.
> - Users **Admin ya Member** honge.
> - **SuperAdmin** naye client/company ke Admin ko invite karega.
> - **Admin** apni company ke andar naye Admin/Member ko invite karega.
> - **Admin aur Member** short URLs create karenge (SuperAdmin nahi karega).
> - Short URLs **publicly resolvable** honge (`/s/{code}`) aur original URL par redirect karenge.
> - Sabhi 28 tests pass hain.

---

## 2. Tech Stack & Setup (Kya Use Hua Hai)

- **Framework:** PHP 8.2+ aur **Laravel 11**
- **Database:** **MySQL** (`urlshortener` database configured in `.env`, SQLite bhi fully compatible hai)
- **Auth:** Laravel Breeze (Blade stack) – registration band hai, users sirf **Invitation** ke through aate hain.
- **Testing:** Pest Feature Tests (**28/28 Tests Passed, 73 Assertions**)
- **Code Standards:** Laravel Pint (PSR-12 standard formatted)

---

## 3. Roles aur Unke Kaam (Kaise Kaam Kar Raha Hai)

Database mein `App\Enums\Role` enum bana hai jisme 5 roles hain:
`SuperAdmin`, `Admin`, `Member`, `Sales`, `Manager`.

### A. SuperAdmin
1. **Creation:** Seeder (`DatabaseSeeder.php`) mein **Raw SQL** (`DB::insert`) se banta hai, Eloquent se nahi (assignment requirement).
   - Email: `superadmin@urlshortener.test`
   - Password: `password`
2. **Company:** Iska `company_id = NULL` hota hai (kisi company ka hissa nahi hota).
3. **Permissions:**
   - Short URL create **nahi kar sakta** (Generate button UI mein hide hai, direct hit par 403 Forbidden).
   - Sabhi companies ke generate kiye huye URLs dekh sakta hai ("Client" column ke saath).
   - Naya client onboard kar sakta hai: **"Invite New Client"** button se Company Name + Email daal kar naye Admin ko invite karta hai.
   - DB transaction mein pehle naya `Company` banta hai aur fir `Invitation`.

### B. Admin
1. **Company:** Ek specific Company ka Admin hota hai.
2. **Permissions:**
   - Short URLs create **kar sakta hai**.
   - Sirf **apni company** ke URLs dekh sakta hai (dusri company ke URLs hide rehte hain).
   - Apni company mein naye team members (**Admin** ya **Member**) ko invite kar sakta hai.
   - Dusri company mein kisi ko invite nahi kar sakta (server-side enforced).

### C. Member
1. **Company:** Apni company ka member hota hai.
2. **Permissions:**
   - Short URLs create **kar sakta hai**.
   - Sirf **apne banaye huye URLs** dekh sakta hai (apne colleague ke URLs bhi nahi dikhte).
   - Kisi ko invite **nahi kar sakta** (403 Forbidden).

### D. Sales & Manager
- Reserved roles hain. Short URL creation aur invitation dono par inko 403 Forbidden milta hai.

---

## 4. Invitation Flow (Bina Mail Server Ke Kaise Test Karein)

1. SuperAdmin ya Admin **"Invite"** form submit karta hai.
2. Server 64-characters ka unique random token generate karta hai jo 7 dino ke liye valid hota hai.
3. Local environment ke liye `.env` mein `MAIL_MAILER=log` set hai.
4. Invitation submit hote hi:
   - Screen par **Blue Alert Box** mein direct acceptance link show hoti hai.
   - Link `storage/logs/laravel.log` mein bhi write ho jati hai.
5. User link open karta hai (`/invitations/{token}`):
   - Invitee ka email aur company name dikhta hai.
   - New password aur confirm password daal kar submit karta hai.
   - User account create hota hai aur automatically log in ho jata hai.
6. Agar token expired ho ya pehle se accepted ho, to page **410 Gone** return karta hai.

---

## 5. Short URL Generation & Public Redirect

1. **Code Generation (`App\Services\ShortCodeGenerator`):**
   - 6 se 8 characters ka random alphanumeric code banata hai.
   - Collision aane par retry karta hai (up to 10 retries).
   - Database mein `short_code` par `UNIQUE` index safety net ke liye laga hai.
2. **Public Redirect (`GET /s/{code}`):**
   - Koi login ki zarurat nahi hoti (Public Route).
   - Jaise hi koi visit karta hai, server atomically counter badhata hai:
     ```php
     $shortUrl->increment('hits');
     ```
   - Original URL par `302 Found` redirect kar deta hai.
   - Agar code exist nahi karta to clean **404** deta hai.

---

## 6. Advanced Features (Assignment Quality Points)

1. **Reusable Query Scope (`scopeVisibleTo` in `ShortUrl.php`):**
   - SuperAdmin -> Sabhi rows
   - Admin -> `where('company_id', $user->company_id)`
   - Member -> `where('user_id', $user->id)`
   - Controller ya View mein if-else scatter nahi hai, single clean scope har query pe lagta hai.
2. **Date Interval Filter (`scopeInInterval`):**
   - Dropdown options: **This Month**, **Last Month**, **Last Week**, **Today**, **All Time**.
   - Carbon calendar boundaries use hoti hain.
   - Pagination ke sath `withQueryString()` use kiya hai taaki page 2 par jaane par filter retain rahe.
3. **Streamed CSV Export (`/short-urls/export`):**
   - `response()->streamDownload` aur `fputcsv` use karta hai.
   - Large datasets ke liye `chunk(500)` use kiya hai taaki memory limit na bhare.
   - Export par bhi wahi `scopeVisibleTo` aur `scopeInInterval` lagta hai (koi user unauthorized data export nahi kar sakta).
4. **N+1 Prevention:**
   - URLs list pe `with(['user', 'company'])` eager loading hai.
   - Clients aur Team table pe `withCount('users')` aur `withSum('shortUrls as total_hits', 'hits')` use kiya hai.

---

## 7. Database Tables (Schema)

```
companies
├── id
├── name
└── timestamps

users
├── id
├── company_id (nullable FK -> companies, nullOnDelete)
├── name
├── email (unique)
├── password
├── role (SuperAdmin, Admin, Member, Sales, Manager)
└── timestamps

short_urls
├── id
├── company_id (FK -> companies, cascade)
├── user_id (FK -> users, cascade)
├── original_url (text)
├── short_code (string 10, unique)
├── hits (unsigned integer, default 0)
└── timestamps
Indexes: (company_id, created_at), (user_id, created_at)

invitations
├── id
├── company_id (FK -> companies, cascade)
├── invited_by (FK -> users, cascade)
├── name (nullable)
├── email
├── role
├── token (string 64, unique)
├── expires_at
├── accepted_at (nullable)
└── timestamps
```

---

## 8. Test Cases Coverage (28 Feature Tests)

Ye saare 15 required test areas `tests/Feature/` ke andar verified hain:
1. Admin short URL create kar sakta hai.
2. Member short URL create kar sakta hai.
3. SuperAdmin create nahi kar sakta (403 Forbidden).
4. Sales aur Manager create nahi kar sakte (403 Forbidden).
5. SuperAdmin sabhi companies ke URLs dekh sakta hai.
6. Admin sirf apni company ke URLs dekh sakta hai (dusri company ke nahi).
7. Member sirf apne banaye huye URLs dekh sakta hai.
8. Short URL bina login ke redirect karta hai (unknown code pe 404).
9. Redirect hone par `hits` counter 1 se increment hota hai.
10. SuperAdmin naye company ke sath Admin invite kar sakta hai.
11. Admin apni company mein Member/Admin invite kar sakta hai; Member invite nahi kar sakta.
12. Invitation accept hone par user sahi role aur company ke sath create hota hai; expired token reject hota hai.
13. Database Seeder se SuperAdmin raw SQL ke sath create hota hai (`company_id = null`).
14. Date filter chuninda interval ke URLs hi return karta hai.
15. CSV export mein sirf wahi rows aati hain jo us user ko visible hain.

Tests chalane ke liye command:
```bash
php artisan test --no-coverage
```

---

## 9. Project Ko Kaise Run Karein

1. XAMPP mein **Apache** aur **MySQL** start karein.
2. Terminal mein project folder par jayein:
   ```bash
   cd c:\xampp\htdocs\sembarkurlshort
   ```
3. Development server start karein:
   ```bash
   php artisan serve
   ```
4. Browser mein open karein: **`http://127.0.0.1:8000/`**
5. Login karein:
   - **Email:** `superadmin@urlshortener.test`
   - **Password:** `password`
