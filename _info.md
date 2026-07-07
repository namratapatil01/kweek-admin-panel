# Kweek Admin Panel - Developer Onboarding Guide

Welcome to the Kweek Admin Panel! This guide is designed to help you quickly understand the project's structure, architecture, and business logic so you can get up to speed.

## 1. System Overview (What is this?)
This project is a **"Super App" Backend & Admin Panel** built with Laravel 10. It is designed to manage a multi-vertical application offering several core services:
- **Food / eCommerce Delivery** (Vendors, Meals, Orders)
- **Ride-Hailing / Cabs** (Rides, Drivers, Vehicles)
- **Parcel Delivery** (Couriers, Weights, Pricing)
- **On-Demand Services / Providers** (Service booking, Workers, Schedules)
- **Vehicle Rentals** (Rental packages, Owners, Bookings)

## 2. Core Entities (The Big 4)
Because this is a multi-vendor, multi-service platform, there are four primary types of actors:
1. **Users/Customers (`CustomerController` & `User.php`)**: The end-users making bookings and purchases.
2. **Vendors/Stores (`VendorController` & `Vendor.php`)**: Businesses selling food, products, or goods.
3. **Drivers (`DriverController`, `Ride.php`, etc.)**: The individuals executing cab rides and deliveries.
4. **Providers & Workers (`ProvidersController`)**: Plumbers, electricians, cleaners offering on-demand services.

*All financial lifecycles (payments, wallets, and payouts) revolve around these actors.*

## 3. Project Architecture & Routing
The application logic is primarily separated into **Web (Admin Backend)** and **API (Mobile App Consumption)** endpoints.

### A. Web Routes (`routes/web.php`)
This file is massive and handles the Admin Dashboard UI. 
- **Pattern**: Routes are extensively guarded using role-based permissions (e.g., `Route::middleware(['permission:drivers,drivers'])`).
- **Feature Grouping**: You'll find separate controller ecosystems for each vertical. For instance:
  - Deliveries & Food: `FoodController`, `OrderController`
  - Cabs & Fleet: `DriverController`, `VehicleController`, `RideController`
  - Rentals: `RentalController`
  - Parcels: `ParcelController`
- **Payouts & Finance**: Dedicated controllers like `VendorsPayoutController`, `DriversPayoutController`, and `TransactionController` manage the wallets and withdrawal requests.

### B. REST API Router (`routes/api.php`)
This is where the mobile apps connect. 
- **Dynamic Entity Registry**: Instead of hundreds of standard endpoints, Kweek uses an abstract registry `EntityApiController`. 
  - E.g., `GET /api/v1/{entity}` maps dynamically based on the requested entity (slugs). This makes it highly extensible without duplicating API boilerplate code!
- **Third-Party Gateways (ArroPay)**: Custom hardcoded endpoints exist for complex callbacks and payment gateways like ArroPay (Maya, Instapay).

## 4. Key Directories & Where to Look
- `app/Models/` (74+ Models): This maps directly to the database. If you want to understand relationships (e.g., `VendorOrder` vs `Ride`), check this folder.
- `app/Http/Controllers/`: Contains about 90 files. Look in `app/Http/Controllers/Api` for specific mobile logic.
- `resources/` & `public/`: The frontend of the admin panel is a mix of dynamic Laravel Blade templates and compiled JS/CSS (via `laravel-mix` & Bootstrap 5).
- `config/`: Configuration files handling environment variables, Firebase settings, multiple payment gateway keys (Stripe, Razorpay, Paytm, PayPal).

## 5. First Steps for Development
1. **Review Migrations/Schema**: To understand things perfectly, check `database/migrations/` or the `kweek-admin-panel.sql` dump.
2. **Setup Role**: Because of the strict `permission` middlewares in `web.php`, make sure your local user has the `Super-Admin` role, otherwise you will hit a bunch of 403 Forbidden errors when navigating the dashboard.
3. **Logging**: If you hit unexpected errors, watch the log file `storage/logs/laravel.log`.

Happy Coding!
