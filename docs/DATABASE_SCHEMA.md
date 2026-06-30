# KWEEK MySQL Database Schema

Production schema migrated from Firebase Firestore (71 collections) to normalized MySQL.

## Design Principles

| Decision | Rationale |
|----------|-----------|
| **Primary keys** | `VARCHAR(64)` — preserves Firebase document IDs for zero-downtime mobile app migration |
| **Hybrid columns** | Frequently queried fields as indexed columns; nested/array data in `JSON payload` |
| **Embedded docs** | Order `author`, `driver`, `vendor`, `products` kept as JSON (denormalized snapshots) |
| **Admin users** | Separate `users` table (Laravel session auth) — not mixed with `app_users` |
| **Settings** | Key-value: document ID = setting key, `value` JSON column |
| **Favorites** | Pivot-style tables with composite string IDs |
| **Chat** | Parent tables (`chat_*`) + `chat_threads` for subcollection messages |

## Entity Relationship Overview

```
sections ──┬── vendors ──┬── vendor_categories
           │             ├── vendor_products
           │             ├── coupons, stories, booked_tables
           │             └── vendor_orders
           │
           ├── vehicle_types, rental_vehicle_types, taxes, banners
           ├── subscription_plans ── subscription_histories
           └── app_users (via sectionId)

app_users ──┬── vendor_orders (authorID)
            ├── rides / parcel_orders / rental_orders / provider_orders
            ├── wallet / wallet_transactions
            ├── documents_verify
            └── driver_payouts / payouts / withdraw_methods

zones ── rides, rental_orders, vendors (geo-fencing)
```

## Core Tables

### `app_users` (Firebase: `users`)
Mobile app users — customers, vendors, drivers, providers.

| Column | Type | Index | Notes |
|--------|------|-------|-------|
| id | VARCHAR(64) PK | ✓ | Firebase UID |
| email | VARCHAR | ✓ | Login credential |
| phoneNumber | VARCHAR(32) | ✓ | |
| password | VARCHAR | | bcrypt hash (replaces Firebase Auth) |
| role | VARCHAR(32) | ✓ | customer, vendor, driver, provider |
| sectionId | VARCHAR(64) | ✓ | Service vertical |
| vendorID | VARCHAR(64) | ✓ | Linked store |
| ownerId | VARCHAR(64) | ✓ | Fleet owner for drivers |
| wallet_amount | DECIMAL(15,2) | | Current balance |
| latitude, longitude | DECIMAL | | Live location |
| userBankDetails, settings, shippingAddress | JSON | | Nested Firebase fields |
| payload | JSON | | Overflow fields |

**Roles (RBAC):** `customer`, `vendor`, `driver`, `provider`. Fleet owners: `role=driver`, `isOwner=true`.

### `sections` (Firebase: `sections`)
Multi-vertical service configuration (delivery, cab, parcel, rental, on-demand).

### `vendors` (Firebase: `vendors`)
Stores/restaurants with geo coordinates, wallet, reviews, working hours.

### Order Tables
Shared pattern across `vendor_orders`, `rides`, `parcel_orders`, `rental_orders`, `provider_orders`:

| Column | Purpose |
|--------|---------|
| status | Order lifecycle state |
| section_id / sectionId | Service vertical |
| authorID | Customer user ID |
| vendorID / driverID / workerId | Assigned resources |
| subTotal, discount, tip_amount, adminCommission | Financials |
| author, driver, vendor, provider, products | Embedded JSON snapshots |
| payload | Additional Firestore fields |

### `settings` (Firebase: `settings/{key}`)
46+ configuration documents (payments, maps, commissions, templates).

### `wallet` / `wallet_transactions`
Ledger entries for top-ups, order debits, refunds.

### Catalog Tables
`vendor_categories`, `vendor_products`, `vendor_attributes`, `vendor_filters`, `brands`, `coupons`, `taxes`, `vehicle_types`, `car_makes`, `car_models`, `parcel_categories`, `provider_categories`, `providers_services`, `providers_workers`, `rental_packages`, etc.

### Chat Tables
| Table | Purpose |
|-------|---------|
| chat_admin, chat_driver, chat_store, chat_provider, chat_worker | Conversation headers |
| chat_threads | Message subcollection (`thread/{msgId}`) |

## Indexing Strategy

1. **Foreign key strings** — all `*ID`, `*Id`, `section_id`, `user_id` columns indexed
2. **Status filters** — `status`, `paymentStatus`, `active`, `isActive`, `publish`
3. **Temporal** — `createdAt`, `date` for order/report queries
4. **Search** — `title`, `name`, `email`, `firstName`, `phoneNumber` via API layer
5. **Composite** — `chat_threads(chat_type, chat_id)`, `app_users(role, isActive)`

## Migration Files

| File | Purpose |
|------|---------|
| `2026_06_29_120000_create_complete_kweek_schema.php` | Full schema (drops legacy stubs) |
| `2026_06_29_130000_add_supplementary_tables.php` | `vendor_filters`, `chat_threads` |

Run: `php artisan migrate`

## Firebase Collection → MySQL Mapping

Full mapping in `config/kweek_entities.php` (69 entities).

## Data Import

```bash
php artisan kweek:import-collections --file=collections.json --chunk=500
php artisan kweek:import-collections --only=users,vendors,vendor_orders
php artisan kweek:import-collections --no-truncate
```
