# Admin Panel — Firebase to Laravel MVC Migration

All admin modules now use **MySQL + Laravel MVC** via a shared production architecture. No Firebase code in scaffolded views.

## Architecture

```
Controller (e.g. TaxController)
    └── use ProvidesMySqlCrud
            └── AdminCrudService → Eloquent Model → MySQL
            └── config/admin_modules.php (columns, forms, permissions)
```

| Layer | Location |
|-------|----------|
| Module config | `config/admin_modules.php` |
| CRUD trait | `app/Http/Controllers/Concerns/ProvidesMySqlCrud.php` |
| Service | `app/Services/Admin/AdminCrudService.php` |
| Validation | `app/Http/Requests/Admin/StoreModuleRequest.php` |
| Routes | `routes/admin_modules.php` (included from `web.php`) |
| Blade UI | `resources/views/{module}/` (index, create, edit, show) |
| Shared partials | `resources/views/admin/partials/crud-*.blade.php` |

## Per-Module Structure (Naming Convention)

| Module | Controller | Model | Views | Route |
|--------|------------|-------|-------|-------|
| Taxes | `TaxController` | `Tax` | `resources/views/taxes/` | `Route::resource('tax', ...)` |
| Brands | `BrandController` | `Brand` | `resources/views/brands/` | `brands` |
| Customers | `CustomerController` | `AppUser` | `resources/views/users/` | `users` |
| Drivers | `DriverController` | `AppUser` | `resources/views/drivers/` | *(custom — already MySQL)* |

## Adding a New Module

1. Add entry to `config/admin_modules.php`
2. Create controller:

```php
class FooController extends Controller
{
    use ProvidesMySqlCrud;

    public function __construct() { $this->middleware('auth'); }

    protected function moduleSlug(): string { return 'foos'; }
}
```

3. Register in `routes/admin_modules.php` `$moduleControllers` array
4. Run: `php artisan kweek:scaffold-admin-views --force`

## Commands

```bash
php artisan kweek:scaffold-admin-views        # Generate Blade CRUD views
php artisan kweek:scaffold-admin-views --force # Overwrite Firebase views
php artisan route:list --name=tax.            # Verify resource routes
```

## 50+ Modules Migrated

Sections, Taxes, Currencies, Brands, Categories, Vendor Filters, Attributes, Documents, Review Attributes, Coupons, Subscription Plans, Vehicle Types, Rental Packages, Parcel Categories, Gift Cards, CMS Pages, Onboarding, Banners, Advertisements, Orders, Rides, Vendors, Customers, Zones, Notifications, Wallet Transactions, Parcel/Rental/Provider Orders, Payouts, Complaints, SOS, Referrals, Stories, Email Templates, Products, Provider modules, Car Makes/Models, Promos, and more.

## Still Custom (Complex UI)

- **Drivers** — `DriverController` (documents, wallet, fleet, DataTables)
- **Settings** — payment gateways, globals (`SettingsController`)
- **Maps / God Eye** — real-time map views
- **Chat** — subcollection threads

These retain specialized controllers; data layer is MySQL.

## Security

- `auth` middleware on all admin routes
- `permission:{module},{module}` middleware per module
- Form Request validation via `StoreModuleRequest` / `UpdateModuleRequest`
- Password hashing in `AdminCrudService` for customer accounts
