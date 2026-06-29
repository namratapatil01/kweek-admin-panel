# KWEEK Firebase → Laravel MySQL Migration Guide

## Overview

This admin panel is migrating from **Firebase Firestore** to a **Laravel 10 REST API** backed by **MySQL**. Mobile apps and the admin UI should call `/api/v1/*` instead of the Firestore SDK.

### Architecture

| Layer | Location |
|-------|----------|
| REST API routes | `routes/api.php` |
| Controllers | `app/Http/Controllers/Api/V1/` |
| Services | `app/Services/` |
| Repositories | `app/Repositories/` |
| Models | `app/Models/` |
| Entity registry | `config/kweek_entities.php` |
| Schema migration | `database/migrations/2026_06_29_120000_create_complete_kweek_schema.php` |

### Primary key strategy

Firebase document IDs are preserved as **string primary keys** (`VARCHAR(64)`).

### Storage strategy

- **Indexed scalar columns** — `status`, `section_id`, `authorID`, `role`
- **`payload` JSON** — nested Firebase fields
- **`settings`** — document ID = setting key, `value` JSON = full document

---

## 1. Export Firebase data

Export Firestore to `collections.json`:

```json
{
  "__collections__": {
    "users": { "DOC_ID": { "field": "value" } }
  }
}
```

A sample export exists at `collections.json` in the project root.

---

## 2. Server setup

```bash
cd kweek-admin-panel/adminpanel
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan kweek:import-collections --truncate --chunk=500
php artisan config:cache
php artisan route:cache
php artisan storage:link
```

### Key `.env` values

```env
DB_CONNECTION=mysql
DB_DATABASE=kweek
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
FILESYSTEM_DISK=s3
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
```

Remove Firebase variables after migration completes.

---

## 3. Data import

```bash
php artisan kweek:import-collections --truncate --chunk=500
php artisan kweek:import-collections --collection=users --truncate
```

---

## 4. Authentication (Sanctum)

```http
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/refresh   (Bearer token)
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

---

## 5. REST API

Entity URLs use kebab-case collection names:

```http
GET    /api/v1/sections
GET    /api/v1/vendor-orders?status=Order+Placed&per_page=20
POST   /api/v1/vendors
PATCH  /api/v1/users/{id}
DELETE /api/v1/sections/{id}
POST   /api/v1/files/upload
```

Standard response:

```json
{ "success": true, "message": "Success", "data": {} }
```

---

## 6. Replace Firebase Cloud Functions

| Firebase JS | Laravel scheduler |
|-------------|-------------------|
| `autoCancelOrder.js` | `kweek:auto-cancel-orders` |
| `scheduleRide.js` | `kweek:dispatch-scheduled-rides` |
| `scheduleNotification.js` | `kweek:schedule-notifications` |

---

## 7. Production checklist

- [ ] `php artisan migrate --force`
- [ ] Import with `--truncate`
- [ ] Configure S3 + Redis
- [ ] Remove Firebase credentials
- [ ] Update mobile apps to REST API
- [ ] `APP_DEBUG=false`
