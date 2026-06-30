# KWEEK Laravel API — Deployment Guide

Migrate from Firebase (Firestore + Auth + Storage) to this Laravel 10 MySQL API.

## Prerequisites

- PHP 8.1+
- MySQL 8.0+
- Composer 2.x
- Redis (recommended for cache/queues)

## 1. Environment Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
```

### Required `.env` variables

```env
APP_URL=https://api.yourdomain.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=kweek
DB_USERNAME=kweek
DB_PASSWORD=your-secure-password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

FILESYSTEM_DISK=s3
SANCTUM_STATEFUL_DOMAINS=yourdomain.com

KWEEK_UPLOAD_MAX_KB=10240
```

Remove all `FIREBASE_*` variables after migration.

## 2. Database

```bash
php artisan migrate --force
php artisan storage:link
```

## 3. Firebase Data Import

Place export at `collections.json`, then:

```bash
php artisan kweek:import-collections
php artisan kweek:import-collections --only=sections,users,vendors
php artisan kweek:import-collections --chunk=1000 --no-truncate
```

Firebase Auth passwords cannot be exported — use password reset or API registration for new credentials.

## 4. File Storage

Sync Firebase Storage to S3, then update URLs in DB. Upload via:

```http
POST /api/v1/uploads
Authorization: Bearer {token}
```

## 5. API Authentication

```http
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/refresh   (Bearer token)
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

## 6. Entity CRUD

```http
GET    /api/v1/entities
GET    /api/v1/{entity}?status=...&per_page=20&search=...
GET    /api/v1/{entity}/{id}
POST   /api/v1/{entity}
PATCH  /api/v1/{entity}/{id}
DELETE /api/v1/{entity}/{id}
```

Entity slugs: `vendor-orders`, `parcel-orders`, `subscription-plans`, etc.

## 7. Production

```bash
php artisan config:cache
php artisan route:cache
composer install --optimize-autoloader --no-dev
```

Run queue worker and scheduler cron. See `docs/DATABASE_SCHEMA.md` for full schema reference.
