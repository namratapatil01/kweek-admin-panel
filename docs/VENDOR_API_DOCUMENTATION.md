# Vendor API Documentation

Base URL: `{{base_url}}/api/vendor`  
Authentication: Laravel Sanctum Bearer Token  
Flutter source: `emart_vendor` (`/Users/sujiyan/Downloads/emart_vendor`)

## Response Format

### Success
```json
{ "status": true, "message": "Success", "data": {} }
```

### Login / Register Success
```json
{
  "status": true,
  "message": "Login successful",
  "token": "1|access_token_here",
  "vendor": {}
}
```

Protected routes: `Authorization: Bearer {token}` + `Accept: application/json`

---

## Flutter Analysis Summary

| Item | Finding |
|------|---------|
| Package | `vendor` / `kweek.app.stores` |
| State | GetX |
| Backend (current) | Firebase Auth + Firestore + Storage |
| Vendor user | `users` → `app_users` (`role=vendor`) |
| Store profile | `vendors` collection → linked via `vendorID` |
| Orders | `vendor_orders` (real-time snapshots in Flutter) |
| Products | `vendor_products` |
| Coupons | `coupons` |
| Wallet / Payouts | `wallet` + `payouts` + `withdraw_method` |
| Chat | `chat_store` (customer) + `chat_admin` (admin) |
| Dine-in | `booked_table` + vendor dine-in fields |
| Self-delivery drivers | `users` with `role=driver`, `vendorID` |
| Subscriptions | `subscription_plans` + `subscription_history` |
| Ads / Stories | `advertisements`, `story` |
| KYC | `documents` + `documents_verify` (type `restaurant`) |

### Order Status Strings (exact)
`Order Placed`, `Order Accepted`, `Order Rejected`, `Order Cancelled`, `Driver Pending`, `Driver Accepted`, `Driver Rejected`, `Order Shipped`, `In Transit`, `Order Completed`

### Key Workflows
1. Register → (optional subscription) → Create store (`vendors`)
2. Document verification → product management
3. Real-time orders → accept/reject/cancel/complete
4. Self-delivery: assign own driver → `In Transit`
5. Ecommerce: ship with courier tracking
6. Wallet credit on accept → withdraw via payouts

---

## Firebase → MySQL → Vendor API Mapping

| Firebase Collection | MySQL Table | Laravel Model | Vendor API |
|---|---|---|---|
| `users` (role=vendor) | `app_users` | `AppUser` | Auth, Profile |
| `vendors` | `vendors` | `Vendor` | `/store` |
| `vendor_orders` | `vendor_orders` | `VendorOrder` | `/orders` |
| `vendor_products` | `vendor_products` | `VendorProduct` | `/products` |
| `vendor_categories` | `vendor_categories` | `VendorCategory` | `/catalog` |
| `vendor_attributes` | `vendor_attributes` | `VendorAttribute` | `/catalog` |
| `brands` | `brands` | `Brand` | `/catalog` |
| `coupons` | `coupons` | `Coupon` | `/coupons` |
| `wallet` | `wallet` | `Wallet` | `/wallet` |
| `payouts` | `payouts` | `Payout` | `/wallet/payouts`, `/wallet/withdraw` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | `/withdraw-method` |
| `items_review` | `items_reviews` | `ItemReview` | `/reviews` |
| `rating` | `ratings` | `Rating` | `/ratings` |
| `booked_table` | `booked_tables` | `BookedTable` | `/dine-in/bookings` |
| `chat_store` + `thread` | `chat_store` + `chat_threads` | `ChatStore` / `ChatThread` | `/chat` (customer) |
| `chat_admin` + `thread` | `chat_admin` + `chat_threads` | `ChatAdmin` / `ChatThread` | `/chat?type=admin` |
| `users` (self-delivery driver) | `app_users` | `AppUser` | `/drivers` |
| `subscription_plans` | `subscription_plans` | `SubscriptionPlan` | `/subscriptions/plans` |
| `subscription_history` | `subscription_histories` | `SubscriptionHistory` | `/subscriptions` |
| `advertisements` | `advertisements` | `Advertisement` | `/advertisements` |
| `story` | `stories` | `Story` | `/story` |
| `documents` / `documents_verify` | same | `Document` / `DocumentVerify` | `/documents` |
| `settings` | `settings` | `Setting` | `/home`, `/dashboard` |
| `on_boarding` (type=store) | `on_boarding` | `OnBoarding` | `/home` |
| `sections` | `sections` | `Section` | `/home` |
| `zone` | `zones` | `Zone` | `/catalog` |
| `notifications` | `notifications` | `AppNotification` | `/notifications` |

No new tables created.

---

## API Endpoints (~70 routes)

### Auth (Public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | Email registration |
| POST | `/login` | Email/password login |
| POST | `/auth/google` | Google login/register |
| POST | `/auth/apple` | Apple login/register |
| POST | `/auth/phone` | Phone OTP login/register |
| POST | `/password/forgot` | Password reset email |
| POST | `/password/reset` | Reset password |

### Public Content
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/home` | Onboarding, sections, settings |
| GET | `/terms` | Terms |
| GET | `/privacy` | Privacy |
| GET | `/catalog` | Categories, brands, attributes, zones |
| GET | `/subscriptions/plans` | Available plans |

### Profile & Store (Protected)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Vendor user + store |
| PUT | `/profile` | Update user profile |
| POST | `/profile/image` | Upload profile photo |
| PUT | `/bank-details` | Bank details |
| GET | `/store` | Store details |
| POST | `/store` | Create store (first time) |
| PUT | `/store` | Update store |
| POST | `/store/image` | Upload store photo/cover |
| POST | `/logout` | Logout |
| DELETE | `/account` | Delete account |

### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | Order counts, wallet, store summary |

### Orders
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders?tab=new\|active\|completed\|cancelled` | List orders |
| GET | `/orders/{id}` | Order detail |
| POST | `/orders/{id}/accept` | Accept (+ wallet credit) |
| POST | `/orders/{id}/reject` | Reject |
| POST | `/orders/{id}/cancel` | Cancel |
| POST | `/orders/{id}/complete` | Complete (takeaway/delivery) |
| POST | `/orders/{id}/assign-driver` | Self-delivery assign |
| POST | `/orders/{id}/ship` | Ecommerce ship + tracking |
| PATCH | `/orders/{id}` | Generic update |

### Products & Coupons
CRUD at `/products`, `/coupons` + image uploads

### Wallet
`/wallet`, `/wallet/transactions`, `/earnings`, `/wallet/withdraw`, `/wallet/payouts`, `/withdraw-method`

### Chat
`/chat/inbox?type=customer|admin`, `/chat/{orderId}/messages`, `/chat/send`, `/chat/upload`

### Drivers (Self-Delivery)
CRUD at `/drivers`

### Dine-In
`/dine-in/bookings`, accept/reject, `/dine-in/config`

### Subscriptions
`/subscriptions/history`, `POST /subscriptions`

### Advertisements & Story
CRUD `/advertisements`, `/story`

### Documents
`/documents`, `/documents/status`, submit + upload

---

## Laravel Implementation

```
app/Http/Controllers/Api/Vendor/*
app/Services/Vendor/*
app/Http/Requests/Api/Vendor/*
app/Http/Resources/Vendor/VendorResource.php
routes/api.php → Route::prefix('vendor')
```

Middleware: `auth:sanctum` + `app.role:vendor`

---

## Test Results

| Test | Result |
|------|--------|
| `VendorAuthService::register` | ✅ Token issued |
| `VendorAuthService::login` | ✅ Token issued |
| `VendorProfileService::createStore` | ✅ Store created + vendorID linked |
| `VendorDashboardService::dashboard` | ✅ Counts returned |

---

## Postman

Import: `docs/postman/KWEEK_Vendor_API.postman_collection.json`

Variables: `base_url`, `token`, `order_id`, `product_id`
