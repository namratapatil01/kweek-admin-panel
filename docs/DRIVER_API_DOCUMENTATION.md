# Driver API Documentation

Base URL: `{{base_url}}/api/driver`  
Authentication: Laravel Sanctum Bearer Token  
Flutter source: `emart_driver` (`/Users/sujiyan/Downloads/emart_driver`)

## Response Format

### Success
```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

### Login / Register Success
```json
{
  "status": true,
  "message": "Login successful",
  "token": "1|access_token_here",
  "driver": {}
}
```

### Error
```json
{
  "status": false,
  "message": "Error message",
  "errors": {}
}
```

Protected routes require:

```
Authorization: Bearer {access_token}
Accept: application/json
```

---

## Flutter Analysis Summary

| Item | Finding |
|------|---------|
| Package | `emart_driver` / `driver` |
| State management | GetX |
| Backend (current) | Firebase Auth + Firestore + Storage — **no REST APIs** |
| Driver identity | `users` collection → `app_users` with `role=driver` |
| Service modules | `delivery-service`, `cab-service`, `parcel_delivery`, `rental-service` |
| Order collections | `vendor_orders`, `rides`, `parcel_orders`, `rental_orders` |
| Order statuses | `Order Placed`, `Order Accepted`, `Driver Pending`, `Driver Accepted`, `Driver Rejected`, `Order Shipped`, `In Transit`, `Order Completed`, `Order Cancelled` |
| Auth methods | Email/password, Google, Apple, Phone OTP |
| Approval | `settings/DriverNearBy.auto_approve_driver` |
| KYC | `documents` + `documents_verify` |
| Online/offline | `isActive` + live location (`latitude`, `longitude`, `rotation`) |
| Wallet | `wallet` transactions + `driver_payouts` + `withdraw_methods` |
| Chat | `chat_driver` inbox + `chat_threads` messages |
| Fleet mode | `isOwner`, `ownerId`, sub-drivers under owner |
| Storage paths | `profileImage/{uid}/`, `driver/documents/{uid}/`, `chat/images`, `chat/videos` |

### Key Flutter Files

| Area | Path |
|------|------|
| Firestore utils | `lib/utils/fire_store_utils.dart` |
| User model | `lib/models/user_model.dart` |
| Auth | `lib/controllers/login_controller.dart`, `sign_up_controller.dart` |
| Orders | `lib/controllers/driver_order_controller.dart`, module dashboards |
| Wallet | `lib/controllers/wallet_controller.dart` |
| Vehicle info | `lib/controllers/vehicle_information_controller.dart` |

### Driver Workflow

1. **Onboarding** → read `on_boarding` (type=driver), sections, settings
2. **Register / Login** → create or authenticate driver in `users`
3. **Document verification** → upload to `documents_verify` (if enabled)
4. **Go online** → set `isActive=true`, stream location updates
5. **Receive orders** → by `serviceType`: vendor / ride / parcel / rental
6. **Accept / Reject** → assign `driverID`/`driverId`, track `rejectedByDrivers`
7. **Pickup / In Transit / Complete** → status transitions; OTP for cab/rental
8. **Wallet credit** → on complete (fleet sub-drivers credit owner wallet)
9. **Withdraw** → `driver_payouts` + `withdraw_methods`
10. **Chat** → customer per order via `chat_driver`
11. **Fleet owner** → manage sub-drivers (`ownerId`)

---

## Firebase → MySQL → Driver API Mapping

| Firebase Collection | MySQL Table | Laravel Model | Driver API |
|---|---|---|---|
| `users` (role=driver) | `app_users` | `AppUser` | Auth, Profile, Availability, Location |
| `vendor_orders` | `vendor_orders` | `VendorOrder` | `/orders?type=vendor` |
| `rides` | `rides` | `Ride` | `/orders?type=ride` |
| `parcel_orders` | `parcel_orders` | `ParcelOrder` | `/orders?type=parcel` |
| `rental_orders` | `rental_orders` | `RentalOrder` | `/orders?type=rental` |
| `wallet` | `wallet` | `Wallet` | `/wallet`, `/wallet/transactions` |
| `driver_payouts` | `driver_payouts` | `DriverPayout` | `/wallet/payouts`, `/wallet/withdraw` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | `/withdraw-method` |
| `chat_driver` + `thread` | `chat_driver` + `chat_threads` | `ChatDriver` / `ChatThread` | `/chat/*` |
| `items_review` | `items_reviews` | `ItemReview` | `/reviews` |
| `rating` | `ratings` | `Rating` | `/ratings` |
| `documents` / `documents_verify` | same | `Document` / `DocumentVerify` | `/documents` |
| `settings` | `settings` | `Setting` | `/home`, `/dashboard` |
| `on_boarding` (type=driver) | `on_boarding` | `OnBoarding` | `/home` |
| `sections` | `sections` | `Section` | `/home`, `/catalog` |
| `zone` | `zones` | `Zone` | `/catalog` |
| `vehicle_type` | `vehicle_types` | `VehicleType` | `/catalog` |
| `carMake` / `car_model` | `car_makes` / `car_models` | `CarMake` / `CarModel` | `/catalog` |
| `notifications` | `notifications` | `AppNotification` | `/notifications` |
| `currencies` | `currencies` | `Currency` | `/home` |

No new tables were created. Existing schema reused as-is.

---

## API Endpoints (51 routes)

### Auth (Public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | Email registration |
| POST | `/login` | Email/password login |
| POST | `/auth/google` | Google `id_token` login/register |
| POST | `/auth/apple` | Apple `id_token` login/register |
| POST | `/auth/phone` | Phone OTP login/register |
| POST | `/password/forgot` | Send reset email |
| POST | `/password/reset` | Reset password with token |

**Register body (example):**
```json
{
  "firstName": "Raj",
  "lastName": "Driver",
  "email": "driver@example.com",
  "phoneNumber": "9999999999",
  "password": "password123",
  "password_confirmation": "password123",
  "serviceType": "delivery-service",
  "sectionId": "section-uuid",
  "zoneId": "zone-uuid",
  "carName": "Swift",
  "carNumber": "KA01AB1234",
  "vehicleType": "bike",
  "isOwner": false
}
```

### Public Content

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/home` | Onboarding, sections, currency, settings |
| GET | `/terms` | Terms & conditions |
| GET | `/privacy` | Privacy policy |
| GET | `/catalog` | Sections, zones, vehicle types, car makes/models |

Query: `?serviceType=delivery-service`

### Profile (Protected)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Current driver profile |
| PUT | `/profile` | Update profile / vehicle / FCM |
| POST | `/profile/image` | Upload profile photo (`image` file) |
| PUT | `/availability` | Go online/offline `{ "online": true }` |
| PUT | `/location` | Update GPS `{ "latitude", "longitude", "rotation" }` |
| PUT | `/bank-details` | Update `{ "userBankDetails": {} }` |
| POST | `/logout` | Revoke token |
| DELETE | `/account` | Soft-delete account |

### Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/dashboard` | Counts (pending/active/completed/available), wallet, settings |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders` | List orders |
| GET | `/orders/{type}/{id}` | Order detail |
| POST | `/orders/{type}/{id}/accept` | Accept order |
| POST | `/orders/{type}/{id}/reject` | Reject order |
| POST | `/orders/{type}/{id}/start` | Start / In Transit (OTP optional) |
| POST | `/orders/{type}/{id}/complete` | Complete order |
| PATCH | `/orders/{type}/{id}/status` | Generic status transition |

**Query params for list:**
- `type`: `vendor` \| `ride` \| `parcel` \| `rental` (defaults from `serviceType`)
- `tab`: `pending` \| `active` \| `available` \| `completed` \| `cancelled`
- `per_page`: default 20

**Order type by serviceType:**

| serviceType | type param |
|-------------|------------|
| `delivery-service` | `vendor` |
| `cab-service` | `ride` |
| `parcel_delivery` | `parcel` |
| `rental-service` | `rental` |

### Wallet

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wallet` | Balance |
| GET | `/wallet/transactions` | Transaction history |
| GET | `/earnings` | Earnings summary |
| POST | `/wallet/withdraw` | Request payout |
| GET | `/wallet/payouts` | Payout history |
| GET | `/withdraw-method` | Get withdraw method |
| PUT | `/withdraw-method` | Save withdraw method |

### Chat

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/chat/inbox` | Chat threads |
| GET | `/chat/{orderId}/messages` | Messages for order |
| POST | `/chat/send` | Send message |
| POST | `/chat/upload` | Upload chat media |

### Reviews

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reviews` | Driver reviews |
| GET | `/reviews/order/{orderId}` | Review for order |
| GET | `/ratings` | Ratings list |

### Documents & Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | App notifications |
| GET | `/documents` | Required document templates |
| GET | `/documents/status` | Verification status |
| POST | `/documents` | Submit documents JSON |
| POST | `/documents/upload` | Upload document file |

### Fleet Owner (isOwner=true)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/owner/drivers` | List fleet drivers |
| POST | `/owner/drivers` | Create fleet driver |
| GET | `/owner/drivers/{id}` | Fleet driver detail |
| PUT | `/owner/drivers/{id}` | Update fleet driver |
| POST | `/owner/drivers/{id}/image` | Upload fleet driver photo |

---

## Laravel Implementation

```
routes/api.php
  → app/Http/Controllers/Api/Driver/*
      → app/Services/Driver/*
      → app/Http/Requests/Api/Driver/*
      → app/Http/Resources/Driver/DriverResource.php
```

Middleware: `auth:sanctum` + `app.role:driver`

---

## Test Results

| Test | Result |
|------|--------|
| `POST /api/driver/register` | ✅ Token + driver object returned |
| `POST /api/driver/login` | ✅ Token issued |
| `GET /api/driver/home` | ✅ on_boarding, sections, currency, settings |
| `DriverDashboardService::dashboard()` | ✅ counts returned (pending/active/completed/available) |
| `DriverProfileService::setOnline()` | ✅ isActive updated |
| `DriverOrderService::list(available)` | ✅ paginated available orders |
| `DriverWalletService::balance()` | ✅ wallet_amount returned |

---

## Postman Collection

Import: `docs/postman/KWEEK_Driver_API.postman_collection.json`

Set variables:
- `base_url` → `http://localhost:8000`
- `token` → auto-set on login/register
- `order_type` → `vendor` \| `ride` \| `parcel` \| `rental`
- `order_id` → order UUID from list response
