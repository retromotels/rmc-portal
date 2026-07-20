# Retro Motel Collective — Laravel portal (v1)

A real, multi-user version of the RMC portal: owner accounts, the A–H property
registration with file uploads, digitally-signed policy PDFs, a website checker,
and a head-office admin that sees every motel, who's registered vs pending, and
all signed policies.

This folder contains the **app-specific files** that drop into a fresh Laravel 11
project. You create the Laravel project once, copy these files over, install one
package, run the migrations, and you're live.

---

## 1. Requirements

- PHP 8.2+
- Composer
- A database (MySQL/MariaDB or PostgreSQL; SQLite is fine for local testing)

## 2. Create the Laravel project

```bash
composer create-project laravel/laravel rmc-portal
cd rmc-portal
```

## 3. Copy these files in

Copy the contents of this folder over the new project, keeping the same paths:

```
app/Http/Controllers/...      -> app/Http/Controllers/...
app/Http/Middleware/...       -> app/Http/Middleware/...
app/Models/...                -> app/Models/...   (overwrites the default User.php)
app/Services/...              -> app/Services/...
config/rmc.php                -> config/rmc.php
database/migrations/...       -> database/migrations/...
database/seeders/DatabaseSeeder.php -> overwrite
resources/views/...           -> resources/views/...
routes/web.php                -> overwrite
public/css/portal.css         -> public/css/portal.css
```

## 4. Install the PDF package (for the signed policy documents)

```bash
composer require barryvdh/laravel-dompdf
```

## 5. Configure the environment

Edit `.env` with your database credentials, then:

```bash
php artisan key:generate
php artisan storage:link          # makes profile photos public
php artisan migrate --seed        # builds tables + creates the admin account
```

Optional — for **live** Google PageSpeed data in the Website Checker, add a key:

```
GOOGLE_PAGESPEED_KEY=your-key-here
```

(Without it the checker returns an indicative preview and links to the live tool.)

## 6. Run it

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000`.

- **Owner:** click "Join the collective", accept the 3 policies → account is created,
  the 3 signed PDFs are generated, and you're taken to "Complete your details".
- **Admin (head office):** log in with the seeded account:
  - email: `admin@retromotel.co`
  - password: `change-me-now`  ← **change this immediately** (see below)

### Change the admin password

```bash
php artisan tinker
>>> $u = App\Models\User::where('email','admin@retromotel.co')->first();
>>> $u->password = 'a-strong-password'; $u->save();   // auto-hashed by the model cast
```

---

## What's included

| Area | Route | Notes |
|------|-------|-------|
| Login / Register | `/login`, `/register` | 3 required policy tick-boxes; generates signed PDFs |
| Complete details | `/details` | Sections A & B; tier auto-selected from room count |
| Dashboard | `/dashboard` | C–H registration tasks + completion meter |
| Property setup | `/property-setup` | All A–H forms; uploads (optional) stored privately |
| Website checker | `/website-checker` | Google PageSpeed if a key is set, else preview |
| Account | `/account` | Profile, signed-policy downloads, cancellation policy |
| Admin overview | `/admin` | Registered vs details-pending |
| Admin motels | `/admin/motels` | Every motel; click through to full detail |
| Admin signed policies | `/admin/policies` | All 3 PDFs per motel with name + timestamp + download |

## Data model

- `users` — accounts + role (owner/admin), motel, band, tier, details_complete, founding
- `registrations` — one row per user per section (A–H), answers as JSON
- `uploads` — files on the private `local` disk, linked to section + field
- `policy_documents` — the 3 signed PDFs with accepted_name + accepted_at

## Security notes for production

- Uploads and policy PDFs are stored on the **private** disk and only served through
  authenticated, ownership-checked routes — they are never publicly linkable.
- Set `APP_ENV=production`, `APP_DEBUG=false`, run behind HTTPS.
- Put file storage on S3 (or similar) once you have more than a handful of members —
  swap the `local` disk for `s3` in `config/filesystems.php`; no code changes needed.
- Add email verification and password reset (Laravel Breeze/Fortify) before launch if
  you want them — this scaffold uses plain session auth to stay dependency-light.

## Not yet built (deliberately — these are "Launching 1 September" in the MVP)

AI Assist, My Documents (weekly cadence), Supplier Directory, Monthly Roundtable,
Community/topics, Resource Library, Add-ons, Stripe billing, the founding-discount
admin toggle. All are straightforward to add on this foundation — the config-driven
`config/rmc.php` and the models are already shaped for them.
