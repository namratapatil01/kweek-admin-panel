# Provider API Documentation

Base URL: `{{base_url}}/api/provider`  
Authentication: Laravel Sanctum Bearer Token  
Flutter source: `nexa_provider` (emartprovider)

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
  "provider": {}
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

### Paginated
```json
{
  "status": true,
  "message": "Success",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
```

Protected routes require:

```
Authorization: Bearer {access_token}
Accept: application/json
```

---

## Firebase → MySQL → Provider API Mapping

| Firebase Collection | MySQL Table | Laravel Model | Provider API |
|---|---|---|---|
| `users` (role=provider) | `app_users` | `AppUser` | Auth, Profile |
| `providers_services` | `providers_services` | `ProviderService` | `/services` |
| `provider_categories` | `provider_categories` | `ProviderCategory` | `/categories` |
| `provider_orders` | `provider_orders` | `ProviderOrder` | `/bookings` |
| `providers_workers` | `providers_workers` | `ProviderWorker` | `/workers` |
| `providers_coupons` | `providers_coupons` | `ProviderCoupon` | `/coupons` |
| `wallet` | `wallet` | `Wallet` | `/wallet` |
| `payouts` | `payouts` / `driver_payouts` | `Payout` / `DriverPayout` | `/wallet/withdraw`, `/wallet/payouts` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | `/withdraw-method` |
| `items_review` | `items_reviews` | `ItemReview` | `/reviews` |
| `chat_provider` + `thread` | `chat_provider` + `chat_threads` | `ChatProvider` / `ChatThread` | `/chat/*` |
| `chat_worker` + `thread` | `chat_worker` + `chat_threads` | `ChatWorker` / `ChatThread` | `/chat/*?type=worker` |
| `subscription_plans` | `subscription_plans` | `SubscriptionPlan` | `/subscriptions/plans` |
| `subscription_history` | `subscription_histories` | `SubscriptionHistory` | `/subscriptions` |
| `sections` | `sections` | `Section` | `/sections`, `/home` |
| `settings` | `settings` | `Setting` | `/home`, `/dashboard`, `/terms`, `/privacy` |
| `on_boarding` | `on_boarding` | `OnBoarding` | `/home` |
| `notifications` | `notifications` | `AppNotification` | `/notifications` |
| `documents` / `documents_verify` | `documents` / `documents_verify` | `Document` / `DocumentVerify` | `/documents` |

No new tables were created. Extra Flutter fields are stored in existing JSON `payload` columns.

---

## Authentication

### Register
`POST /api/provider/register` (Public)

```json
{
  "firstName": "Alex",
  "lastName": "Provider",
  "email": "provider@example.com",
  "phoneNumber": "9876543210",
  "countryCode": "+91",
  "password": "password123",
  "password_confirmation": "password123",
  "sectionId": "",
  "latitude": 28.6139,
  "longitude": 77.2090,
  "street": "MG Road",
  "area": "Bangalore",
  "fcmToken": ""
}
```

If `settings.provider.auto_approve_provider` is `false`, response includes `pending_approval: true` without a token.

### Login
`POST /api/provider/login` (Public)

```json
{
  "email": "provider@example.com",
  "password": "password123",
  "fcmToken": ""
}
```

### Apple Login
`POST /api/provider/auth/apple` (Public)

```json
{
  "id_token": "APPLE_IDENTITY_TOKEN",
  "fcmToken": "",
  "firstName": "Alex",
  "lastName": "Provider",
  "email": "provider@example.com",
  "auto_register": true
}
```

### Phone Login
`POST /api/provider/auth/phone` (Public)

```json
{
  "phoneNumber": "9876543210",
  "countryCode": "+91",
  "firstName": "Alex",
  "auto_register": true,
  "fcmToken": ""
}
```

### Forgot / Reset Password
- `POST /api/provider/password/forgot` `{ "email": "..." }`
- `POST /api/provider/password/reset` `{ "email": "...", "token": "...", "password": "...", "password_confirmation": "..." }`

### Logout / Delete Account
- `POST /api/provider/logout` (Auth)
- `DELETE /api/provider/account` (Auth)

---

## Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Get provider profile |
| PUT | `/profile` | Update profile |
| POST | `/profile/image` | Upload profile image (`multipart image`) |
| PUT | `/bank-details` | Update bank details |

---

## Dashboard & Catalog

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/home` | Public onboarding, sections, currency, settings |
| GET | `/dashboard` | Counts, wallet, subscription, settings |
| GET | `/sections` | On-demand sections |
| GET | `/categories?sectionId=&parentCategoryId=` | Categories / subcategories |

---

## Services

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/services` | List my services |
| POST | `/services` | Create service |
| GET | `/services/{id}` | Service details |
| PUT | `/services/{id}` | Update service |
| DELETE | `/services/{id}` | Delete service |
| POST | `/services/{id}/images` | Upload images (`images[]`) |

Create body example:

```json
{
  "title": "AC Repair",
  "description": "Split AC service",
  "price": 499,
  "disPrice": 399,
  "priceUnit": "Fixed",
  "categoryId": "",
  "subCategoryId": "",
  "sectionId": "",
  "address": "Bangalore",
  "latitude": 12.97,
  "longitude": 77.59,
  "publish": true,
  "startTime": "09:00",
  "endTime": "18:00",
  "days": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
}
```

Availability is stored on each service (`days`, `startTime`, `endTime`) — matching Flutter.

---

## Bookings

Booking tabs via `GET /bookings?tab=new|today|upcoming|completed|cancelled`

Statuses (exact Flutter strings):

`Order Placed` → `Order Accepted` → `Order Assigned` → `Order Ongoing` → `Order Completed`  
↘ `Order Rejected` / `Order Cancelled`

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/bookings?tab=` | List bookings |
| GET | `/bookings/{id}` | Booking details |
| POST | `/bookings/{id}/accept` | Accept |
| POST | `/bookings/{id}/reject` | Reject `{ reason }` |
| POST | `/bookings/{id}/assign-worker` | `{ workerId }` |
| POST | `/bookings/{id}/start` | Start job |
| POST | `/bookings/{id}/stop-timer` | Stop timer |
| POST | `/bookings/{id}/extra-charges` | `{ extraCharges, description }` |
| POST | `/bookings/{id}/complete` | `{ otp }` |
| PATCH | `/bookings/{id}/status` | Generic status update |

Accept may credit wallet for Fixed-price jobs and deduct admin commission (Flutter parity).

---

## Workers

| Method | Endpoint |
|--------|----------|
| GET/POST | `/workers` |
| GET/PUT/DELETE | `/workers/{id}` |
| POST | `/workers/{id}/image` |

---

## Coupons

| Method | Endpoint |
|--------|----------|
| GET/POST | `/coupons` |
| GET/PUT/DELETE | `/coupons/{id}` |
| POST | `/coupons/{id}/image` |

---

## Wallet / Earnings / Payouts

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wallet` | Balance |
| GET | `/wallet/transactions` | Ledger |
| GET | `/earnings` | Earnings summary |
| POST | `/wallet/withdraw` | `{ amount, note, withdrawMethod }` |
| GET | `/wallet/payouts` | Payout history |
| GET/PUT | `/withdraw-method` | Gateway withdraw methods |

---

## Realtime Polling (Firebase listener replacement)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/realtime/poll?since=ISO8601&tab=` | Yes | Returns changed bookings, notifications, inbox messages, wallet balance since timestamp |

Additional incremental polling on existing endpoints:

| Endpoint | Query |
|----------|-------|
| `GET /bookings` | `?since=ISO8601` |
| `GET /chat/{orderId}/messages` | `?since=ISO8601&type=driver\|store\|worker\|customer` |

---

## Auth (extended)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/auth/phone/send-otp` | No | Send 6-digit OTP; returns `verificationId`, `expiresIn` |
| POST | `/auth/phone/verify-otp` | No | Verify OTP then login/register; returns token + provider |

---

## Settings (public)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/settings` | Bootstrap settings bundle (provider, vendor, map key, FCM, languages, etc.) |
| GET | `/settings/payment` | Payment gateway keys (Stripe, Razorpay, PayPal, etc.) |
| GET | `/settings/languages` | Active app languages |
| GET | `/settings/{key}` | Single setting document |

`GET /home` now includes extended `settings` fields: `vendor`, `Version`, `ContactUs`, `googleMapKey`, `notification_setting`, `emailSetting`, `placeHolderImage`, `DriverNearBy`, `languages`, `walletSettings`.

---

## Subscriptions

| Method | Endpoint |
|--------|----------|
| GET | `/subscriptions/plans?sectionId=&isCommissionPlan=true\|false` |
| GET | `/subscriptions/history` |
| POST | `/subscriptions` | `{ plan_id, payment_type, amount }` |
| POST | `/subscriptions/confirm-payment` | `{ plan_id, payment_type, payment_id, amount }` |

---

## Chat

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/chat/inbox?type=customer\|worker\|driver\|store` | Inbox |
| GET | `/chat/{orderId}/messages?type=` | Thread |
| POST | `/chat/send` | Send message (FCM to recipient) |
| POST | `/chat/upload` | Upload image/video |

Inbox uses Flutter field names: `restaurantId` = provider id.

---

## Reviews / Notifications / Documents / CMS

| Method | Endpoint |
|--------|----------|
| GET | `/reviews` |
| GET | `/reviews/order/{orderId}` |
| GET | `/ratings` |
| GET | `/notifications` |
| GET | `/notifications/templates/{type}` |
| GET | `/email-templates/{type}` |
| GET | `/documents` |
| GET | `/documents/status` |
| POST | `/documents` |
| POST | `/documents/upload` |
| GET | `/terms` (public) |
| GET | `/privacy` (public) |

---

## Booking Lifecycle (Flutter parity)

1. Customer places order → `provider_orders` status `Order Placed`
2. Provider accepts/rejects (reject refunds customer wallet for non-COD fixed jobs)
3. Optional worker assignment → `Order Assigned` + FCM to worker
4. Start → `Order Ongoing` + `startTime` + FCM to customer
5. Optional extra charges / stop timer
6. Complete with customer OTP → `Order Completed` + wallet credit + referral reward on first order

Server-side FCM is sent on: `provider_accepted`, `provider_rejected`, `worker_assigned`, `service_intransit`, `service_charges`, `service_completed`, `stop_time`.

---

## Account Delete (cascade)

`DELETE /account` soft-deletes the provider and removes related: services, favorites, coupons, workers, and customer chat threads.

---

## Notes

- Providers live in `app_users` with `role = provider` (no separate providers table).
- Service ownership is stored as `payload.author` = provider id.
- Booking ownership is resolved via `provider.author` / `payload.provider.author` / `payload.providerId`.
- File uploads use the existing `FileStorageService` / public disk.
- Token ability: `provider-api` with ability `provider`.
- Payout withdraw triggers `payout_request` email template when configured.
- Configure `FCM_SERVER_KEY` in `.env` for push notifications.
