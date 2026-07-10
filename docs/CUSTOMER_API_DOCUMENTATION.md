# Customer API Documentation

Base URL: `{{base_url}}/api/customer`  
Authentication: Laravel Sanctum Bearer Token

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
  "customer": {}
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

### Environment Variables

Add these to `.env` for social login:

```env
# Comma-separated OAuth client IDs (Android, iOS, Web)
GOOGLE_CLIENT_IDS=your-android-client-id.apps.googleusercontent.com,your-ios-client-id.apps.googleusercontent.com

# Comma-separated Apple bundle/service IDs
APPLE_CLIENT_IDS=com.yourcompany.emart.customer
```

---

## Authentication

### Register
`POST /api/customer/register` (Public)

**Body:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "phoneNumber": "9876543210",
  "countryCode": "+91",
  "password": "password123",
  "password_confirmation": "password123",
  "fcmToken": "optional_fcm_token"
}
```

### Login
`POST /api/customer/login` (Public)

**Body:**
```json
{
  "email": "john@example.com",
  "password": "password123",
  "fcmToken": "optional_fcm_token"
}
```

### Google Login
`POST /api/customer/auth/google` (Public)

**Body:**
```json
{
  "id_token": "google_id_token_from_mobile_sdk",
  "fcmToken": "optional_fcm_token",
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "auto_register": true
}
```

If the account does not exist and `auto_register` is `false`, the API returns:
```json
{
  "status": true,
  "message": "Registration required",
  "data": {
    "is_new_user": true,
    "profile": {
      "email": "john@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "provider": "google",
      "provider_uid": "google_sub_id"
    }
  }
}
```

### Apple Login
`POST /api/customer/auth/apple` (Public)

**Body:**
```json
{
  "id_token": "apple_identity_token",
  "fcmToken": "optional_fcm_token",
  "firstName": "John",
  "lastName": "Doe",
  "email": "john@example.com",
  "auto_register": true
}
```

### Forgot Password
`POST /api/customer/password/forgot` (Public)

**Body:**
```json
{
  "email": "john@example.com"
}
```

Sends a reset token to the customer's email if the account exists.

### Reset Password
`POST /api/customer/password/reset` (Public)

**Body:**
```json
{
  "email": "john@example.com",
  "token": "token_from_email",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Headers for protected routes:**
```
Authorization: Bearer {access_token}
Accept: application/json
```

### Logout
`POST /api/customer/logout` (Protected)

---

## Profile

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/profile` | Get customer profile |
| PUT | `/profile` | Update profile |
| POST | `/profile/image` | Upload profile image (multipart: `image`) |

**Update profile body:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "phoneNumber": "9876543210",
  "countryCode": "+91",
  "zoneId": "zone_id",
  "latitude": 28.6139,
  "longitude": 77.2090,
  "shippingAddress": [
    {
      "id": "addr_1",
      "address": "123 Main St",
      "locality": "City",
      "landmark": "Near Park",
      "isDefault": true,
      "location": { "latitude": 28.6139, "longitude": 77.2090 }
    }
  ],
  "fcmToken": "token"
}
```

---

## Dashboard & Home

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/home` | Public | Sections, currencies, zones, onboarding, settings |
| GET | `/dashboard` | Protected | Home data + section banners/stories |

**Query params:** `section_id` or `sectionId`

---

## Catalog

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/sections` | Active service sections |
| GET | `/categories?type=vendor\|provider&section_id=` | Vendor or provider categories |
| GET | `/vendors?section_id=&category_id=` | Vendor list |
| GET | `/products?section_id=&vendor_id=&category_id=` | Product list |
| GET | `/services?section_id=&category_id=` | On-demand services |
| GET | `/brands?section_id=` | Brand list |
| GET | `/search?q=query&type=all\|vendor\|product\|service` | Search |
| GET | `/catalog/{type}/{id}` | Single item details |

**Catalog types:** `vendor`, `product`, `service`, `category`, `provider-category`, `brand`

---

## Orders & Bookings

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orders?type=vendor\|parcel\|rental\|ride\|provider\|dine-in` | Order history |
| POST | `/orders` | Create order |
| GET | `/orders/{type}/{id}` | Order details |
| PATCH | `/orders/{type}/{id}/status` | Update order status |

**Create order body:**
```json
{
  "type": "vendor",
  "section_id": "section_id",
  "vendorID": "vendor_id",
  "status": "Order Placed",
  "products": [],
  "address": {},
  "payment_method": "cod",
  "subTotal": 500,
  "deliveryCharge": 50,
  "couponCode": "SAVE10",
  "takeAway": false
}
```

---

## Favorites

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/favorites/{type}?section_id=` | List favorites |
| POST | `/favorites/{type}` | Add favorite |
| DELETE | `/favorites/{type}/{id}` | Remove favorite |

**Types:** `vendor`, `item`, `service`, `provider`

---

## Wallet

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/wallet` | Wallet balance |
| GET | `/wallet/transactions` | Transaction history |
| POST | `/wallet/topup` | Top up wallet |

**Top-up body:**
```json
{
  "amount": 100,
  "payment_method": "stripe",
  "payment_status": "success",
  "note": "Wallet top-up"
}
```

---

## Reviews & Ratings

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reviews?order_id=` | List reviews |
| POST | `/reviews` | Submit review |
| POST | `/ratings` | Submit rating |

---

## Coupons

`GET /api/customer/coupons?type=vendor|parcel|rental|provider|cab&section_id=&vendor_id=`

---

## Notifications & Misc

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | Customer notifications |
| GET | `/referral` | Referral data |
| GET | `/gift-cards` | Available gift cards |
| POST | `/gift-cards/purchase` | Purchase gift card |
| POST | `/complaints` | Submit complaint |
| POST | `/sos` | Send SOS alert |

---

## Error Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad request |
| 401 | Unauthenticated |
| 403 | Forbidden (non-customer role) |
| 404 | Not found |
| 422 | Validation error |
| 500 | Server error |
