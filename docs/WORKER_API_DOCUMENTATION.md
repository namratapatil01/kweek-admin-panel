# Worker API Documentation

Base URL: `{{base_url}}/api/worker`  
Authentication: Laravel Sanctum Bearer Token  
Flutter source: `Nexa_worker` (emart_worker)

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
  "worker": {}
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
| State management | GetX |
| Self-register | **No** — workers are provider-created |
| Auth | Email/password against `providers_workers` |
| Job accept/reject | Not in Flutter; optional APIs added |
| Core job flow | Assigned → Ongoing → Complete (OTP) |
| Wallet UI | **None** — completion credits **provider** wallet |
| Chat | `chat_worker` (restaurant* fields = worker) |
| Online/Offline | Profile toggle `online` |
| KYC / Attendance / Reports | Not in Flutter |

---

## Firebase → MySQL → Worker API Mapping

| Firebase Collection | MySQL Table | Laravel Model | Worker API |
|---|---|---|---|
| `providers_workers` | `providers_workers` | `ProviderWorker` | Auth, Profile, Availability |
| *(Sanctum sync)* | `app_users` (role=`worker`) | `AppUser` | Bearer tokens via Sanctum |
| `provider_orders` | `provider_orders` | `ProviderOrder` | `/jobs` |
| `users` (provider/customer) | `app_users` | `AppUser` | `/provider`, wallet credit target |
| `wallet` | `wallet` | `Wallet` | Written on complete → provider |
| `chat_worker` + `thread` | `chat_worker` + `chat_threads` | `ChatWorker` / `ChatThread` | `/chat/*` |
| `items_review` | `items_reviews` | `ItemReview` | `/reviews` |
| `settings` | `settings` | `Setting` | `/home`, `/terms`, `/privacy` |
| `on_boarding` (type=worker) | `on_boarding` | `OnBoarding` | `/home` |
| `currencies` | `currencies` | `Currency` | `/home` |
| `notifications` | `notifications` | `AppNotification` | `/notifications` |
| `documents` / `documents_verify` | same | `Document` / `DocumentVerify` | `/documents` |

No new tables were created. Password stored as `payload.password_hash` on `providers_workers`, with synced `app_users.password` for Sanctum.

---

## Authentication

### Login
`POST /api/worker/login` (Public)

```json
{
  "email": "worker@example.com",
  "password": "password123",
  "fcmToken": ""
}
```

### Register (optional — not used by Flutter; for seeding / API testing)
`POST /api/worker/register` (Public)

```json
{
  "firstName": "Raj",
  "lastName": "Worker",
  "email": "worker@example.com",
  "phoneNumber": "9999999999",
  "password": "password123",
  "password_confirmation": "password123",
  "providerId": "EXISTING_PROVIDER_APP_USER_ID",
  "address": "Bangalore",
  "salary": "15000"
}
```

`providerId` is required. Prefer creating workers via Provider API `POST /api/provider/workers` (now syncs Sanctum user automatically).

### Forgot / Reset Password
- `POST /api/worker/password/forgot` `{ "email": "..." }`
- `POST /api/worker/password/reset` `{ "email", "token", "password", "password_confirmation" }`

### Logout / Delete
- `POST /api/worker/logout`
- `DELETE /api/worker/account`

---

## Profile & Availability

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Get worker profile |
| PUT | `/profile` | Update profile fields |
| POST | `/profile/image` | Upload photo (`multipart image`) |
| PUT | `/availability` | `{ "online": true }` |
| GET | `/provider` | Parent provider info |

---

## Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/home` | Public onboarding + settings |
| GET | `/dashboard` | Counts (upcoming/assigned/ongoing/completed/today) |

---

## Jobs / Tasks

Tabs: `GET /jobs?tab=upcoming|today|ongoing|completed|history|cancelled`

Statuses (exact Flutter strings):

```
Order Assigned → Order Ongoing → Order Completed
```

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/jobs?tab=` | List assigned jobs |
| GET | `/jobs/{id}` | Job details |
| POST | `/jobs/{id}/accept` | Optional acknowledge |
| POST | `/jobs/{id}/reject` | Optional unassign + reason |
| POST | `/jobs/{id}/start` | Start (schedule-gated) |
| POST | `/jobs/{id}/stop-timer` | Hourly stop time |
| POST | `/jobs/{id}/extra-charges` | Add once |
| POST | `/jobs/{id}/complete` | `{ "otp": "123456" }` |
| PATCH | `/jobs/{id}/status` | Generic status update |

### Completion (Flutter parity)
1. Verify OTP against `provider_orders.payload.otp`
2. Set status `Order Completed`
3. If `priceUnit != Fixed`, credit **provider** wallet (minus admin commission)
4. First-order referral hook

---

## Chat

| Method | Endpoint |
|--------|----------|
| GET | `/chat/inbox` |
| GET | `/chat/{orderId}/messages` |
| POST | `/chat/send` |
| POST | `/chat/upload` |

Inbox field `restaurantId` = worker id (Flutter legacy naming).

---

## Reviews / Earnings / Misc

| Method | Endpoint | Notes |
|--------|----------|-------|
| GET | `/reviews` | Read-only |
| GET | `/reviews/order/{orderId}` | |
| GET | `/ratings` | |
| GET | `/earnings` | Salary + completed job count (not a wallet) |
| GET | `/notifications` | |
| GET | `/documents` | Optional KYC templates |
| GET/POST | `/documents*` | Optional KYC submit/upload |
| GET | `/terms` | Public |
| GET | `/privacy` | Public |

---

## Worker Job Lifecycle

```
Provider assigns worker → workerId set, status Order Assigned
Worker starts → Order Ongoing (+ startTime if Hourly)
Optional Stop Time / Extra Charges
Worker completes with customer OTP → Order Completed
Provider wallet credited (non-Fixed jobs)
```

---

## Notes

- Workers live in `providers_workers`; Sanctum identity is a mirrored `app_users` row with `role=worker` and same `id`.
- Creating a worker via `POST /api/provider/workers` now syncs the Sanctum account automatically.
- Attendance and reports modules are **not** in the Flutter Worker app — no APIs added for those.
- Token: `worker-api` with ability `worker`.
