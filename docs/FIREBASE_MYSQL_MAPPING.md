# Firebase → MySQL → Laravel API Mapping

Source of truth: **MySQL tables** in the KWEEK admin panel.  
Flutter project: `emart_customer` (`/Users/sujiyan/Downloads/emart_customer`)

## Core User & Auth

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `users` | `app_users` | `AppUser` | `POST /api/customer/register`, `POST /api/customer/login`, `GET /api/customer/profile` |
| `referral` | `referrals` | `Referral` | `GET /api/customer/referral` |
| `wallet` | `wallet` | `Wallet` | `GET /api/customer/wallet`, `GET /api/customer/wallet/transactions`, `POST /api/customer/wallet/topup` |
| `on_boarding` | `on_boarding` | `OnBoarding` | `GET /api/customer/home` |
| `zone` | `zones` | `Zone` | `GET /api/customer/home` |
| `sections` | `sections` | `Section` | `GET /api/customer/sections`, `GET /api/customer/home` |
| `currencies` | `currencies` | `Currency` | `GET /api/customer/home` |
| `settings` | `settings` | `Setting` | `GET /api/customer/home`, `GET /api/customer/dashboard` |

## Multi-Vendor / Food / Ecommerce

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `vendors` | `vendors` | `Vendor` | `GET /api/customer/vendors`, `GET /api/customer/catalog/vendor/{id}` |
| `vendor_categories` | `vendor_categories` | `VendorCategory` | `GET /api/customer/categories?type=vendor` |
| `vendor_products` | `vendor_products` | `VendorProduct` | `GET /api/customer/products`, `GET /api/customer/catalog/product/{id}` |
| `vendor_attributes` | `vendor_attributes` | `VendorAttribute` | Via generic `/api/v1/vendor-attributes` |
| `vendor_orders` | `vendor_orders` | `VendorOrder` | `GET /api/customer/orders?type=vendor`, `POST /api/customer/orders` |
| `coupons` | `coupons` | `Coupon` | `GET /api/customer/coupons?type=vendor` |
| `booked_table` | `booked_tables` | `BookedTable` | `GET /api/customer/orders?type=dine-in` |
| `favorite_vendor` | `favorite_vendors` | `FavoriteVendor` | `GET/POST/DELETE /api/customer/favorites/vendor` |
| `favorite_item` | `favorite_items` | `FavoriteItem` | `GET/POST/DELETE /api/customer/favorites/item` |
| `items_review` | `items_reviews` | `ItemReview` | `GET/POST /api/customer/reviews` |
| `rating` | `ratings` | `Rating` | `POST /api/customer/ratings` |
| `review_attributes` | `review_attributes` | `ReviewAttribute` | Via generic API |
| `brands` | `brands` | `Brand` | `GET /api/customer/brands` |
| `banner_items` | `banner_items` | `BannerItem` | `GET /api/customer/dashboard?section_id=` |
| `story` | `stories` | `Story` | `GET /api/customer/dashboard?section_id=` |
| `gift_cards` | `gift_cards` | `GiftCard` | `GET /api/customer/gift-cards` |
| `gift_purchases` | `gift_purchases` | `GiftPurchase` | `POST /api/customer/gift-cards/purchase` |

## Cab / Intercity

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `rides` | `rides` | `Ride` | `GET /api/customer/orders?type=ride` |
| `vehicle_type` | `vehicle_types` | `VehicleType` | Via generic API |
| `popular_destinations` | `popular_destinations` | `PopularDestination` | Via generic API |
| `promos` | `promos` | `Promo` | `GET /api/customer/coupons?type=cab` |

## Parcel

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `parcel_categories` | `parcel_categories` | `ParcelCategory` | Via generic API |
| `parcel_weight` | `parcel_weights` | `ParcelWeight` | Via generic API |
| `parcel_orders` | `parcel_orders` | `ParcelOrder` | `GET /api/customer/orders?type=parcel` |
| `parcel_coupons` | `parcel_coupons` | `ParcelCoupon` | `GET /api/customer/coupons?type=parcel` |

## Rental

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `rental_vehicle_type` | `rental_vehicle_types` | `RentalVehicleType` | Via generic API |
| `rental_packages` | `rental_packages` | `RentalPackage` | Via generic API |
| `rental_orders` | `rental_orders` | `RentalOrder` | `GET /api/customer/orders?type=rental` |
| `rental_coupons` | `rental_coupons` | `RentalCoupon` | `GET /api/customer/coupons?type=rental` |

## On-Demand Services

| Firebase Collection | MySQL Table | Laravel Model | Customer API | Provider API |
|---|---|---|---|---|
| `provider_categories` | `provider_categories` | `ProviderCategory` | `GET /api/customer/categories?type=provider` | `GET /api/provider/categories` |
| `providers_services` | `providers_services` | `ProviderService` | `GET /api/customer/services` | `CRUD /api/provider/services` |
| `providers_workers` | `providers_workers` | `ProviderWorker` | Via generic API | `CRUD /api/provider/workers` + Worker auth `/api/worker/*` |
| `provider_orders` | `provider_orders` | `ProviderOrder` | `GET /api/customer/orders?type=provider` | `CRUD lifecycle /api/provider/bookings` + `/api/worker/jobs` |
| `providers_coupons` | `providers_coupons` | `ProviderCoupon` | `GET /api/customer/coupons?type=provider` | `CRUD /api/provider/coupons` |
| `favorite_service` | `favorite_services` | `FavoriteService` | `GET/POST/DELETE /api/customer/favorites/service` | — |
| `users` (role=provider) | `app_users` | `AppUser` | — | Auth + Profile `/api/provider/*` |
| `users` / worker profile | `providers_workers` + `app_users` (role=worker) | `ProviderWorker` / `AppUser` | — | Auth + Jobs `/api/worker/*` |
| `subscription_plans` | `subscription_plans` | `SubscriptionPlan` | — | `GET /api/provider/subscriptions/plans` |
| `subscription_history` | `subscription_histories` | `SubscriptionHistory` | — | `GET/POST /api/provider/subscriptions*` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | — | `GET/PUT /api/provider/withdraw-method` |
| `payouts` | `payouts` / `driver_payouts` | `Payout` / `DriverPayout` | — | `POST /api/provider/wallet/withdraw` |

## Chat

| Firebase Collection | MySQL Table | Laravel Model | Customer API | Provider API |
|---|---|---|---|---|
| `chat_driver` | `chat_driver` | `ChatDriver` | Planned (Vendor/Rider phase) | — | `GET/POST /api/driver/chat/*` |
| `chat_store` | `chat_store` | `ChatStore` | Planned | — |
| `chat_worker` | `chat_worker` | `ChatWorker` | Planned | `GET/POST /api/provider/chat/*?type=worker` + `/api/worker/chat/*` |
| `chat_provider` | `chat_provider` | `ChatProvider` | Planned | `GET/POST /api/provider/chat/*` |
| `thread` (subcollection) | `chat_threads` | `ChatThread` | Planned | Via provider/worker chat message endpoints | Via `/api/driver/chat/*` |

## Driver App (`emart_driver`)

| Firebase Collection | MySQL Table | Laravel Model | Driver API |
|---|---|---|---|
| `users` (role=driver) | `app_users` | `AppUser` | `POST /api/driver/register`, `POST /api/driver/login`, Profile |
| `vendor_orders` | `vendor_orders` | `VendorOrder` | `/api/driver/orders?type=vendor` |
| `rides` | `rides` | `Ride` | `/api/driver/orders?type=ride` |
| `parcel_orders` | `parcel_orders` | `ParcelOrder` | `/api/driver/orders?type=parcel` |
| `rental_orders` | `rental_orders` | `RentalOrder` | `/api/driver/orders?type=rental` |
| `wallet` | `wallet` | `Wallet` | `/api/driver/wallet`, `/api/driver/wallet/transactions` |
| `driver_payouts` | `driver_payouts` | `DriverPayout` | `/api/driver/wallet/payouts`, `/api/driver/wallet/withdraw` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | `GET/PUT /api/driver/withdraw-method` |
| `chat_driver` + `thread` | `chat_driver` + `chat_threads` | `ChatDriver` / `ChatThread` | `/api/driver/chat/*` |
| `items_review` | `items_reviews` | `ItemReview` | `/api/driver/reviews` |
| `rating` | `ratings` | `Rating` | `/api/driver/ratings` |
| `documents` / `documents_verify` | same | `Document` / `DocumentVerify` | `/api/driver/documents` |
| `settings` | `settings` | `Setting` | `/api/driver/home`, `/api/driver/dashboard` |
| `on_boarding` (type=driver) | `on_boarding` | `OnBoarding` | `/api/driver/home` |
| `sections` | `sections` | `Section` | `/api/driver/home`, `/api/driver/catalog` |
| `zone` | `zones` | `Zone` | `/api/driver/catalog` |
| `vehicle_type` | `vehicle_types` | `VehicleType` | `/api/driver/catalog` |
| `carMake` / `car_model` | `car_makes` / `car_models` | `CarMake` / `CarModel` | `/api/driver/catalog` |
| `notifications` | `notifications` | `AppNotification` | `/api/driver/notifications` |
| `currencies` | `currencies` | `Currency` | `/api/driver/home` |

No new tables created for Driver APIs. See `docs/DRIVER_API_DOCUMENTATION.md`.

## Vendor App (`emart_vendor`)

| Firebase Collection | MySQL Table | Laravel Model | Vendor API |
|---|---|---|---|
| `users` (role=vendor) | `app_users` | `AppUser` | `POST /api/vendor/register`, `POST /api/vendor/login`, `/profile` |
| `vendors` | `vendors` | `Vendor` | `GET/POST/PUT /api/vendor/store` |
| `vendor_orders` | `vendor_orders` | `VendorOrder` | `/api/vendor/orders` |
| `vendor_products` | `vendor_products` | `VendorProduct` | `/api/vendor/products` |
| `vendor_categories` | `vendor_categories` | `VendorCategory` | `/api/vendor/catalog` |
| `coupons` | `coupons` | `Coupon` | `/api/vendor/coupons` |
| `wallet` | `wallet` | `Wallet` | `/api/vendor/wallet` |
| `payouts` | `payouts` | `Payout` | `/api/vendor/wallet/payouts`, `/api/vendor/wallet/withdraw` |
| `withdraw_method` | `withdraw_methods` | `WithdrawMethod` | `/api/vendor/withdraw-method` |
| `items_review` | `items_reviews` | `ItemReview` | `/api/vendor/reviews` |
| `booked_table` | `booked_tables` | `BookedTable` | `/api/vendor/dine-in/bookings` |
| `chat_store` + `thread` | `chat_store` + `chat_threads` | `ChatStore` / `ChatThread` | `/api/vendor/chat` |
| `chat_admin` + `thread` | `chat_admin` + `chat_threads` | `ChatAdmin` / `ChatThread` | `/api/vendor/chat?type=admin` |
| `users` (self-delivery driver) | `app_users` | `AppUser` | `/api/vendor/drivers` |
| `subscription_plans` | `subscription_plans` | `SubscriptionPlan` | `/api/vendor/subscriptions/plans` |
| `subscription_history` | `subscription_histories` | `SubscriptionHistory` | `/api/vendor/subscriptions` |
| `advertisements` | `advertisements` | `Advertisement` | `/api/vendor/advertisements` |
| `story` | `stories` | `Story` | `/api/vendor/story` |
| `documents` / `documents_verify` | same | `Document` / `DocumentVerify` | `/api/vendor/documents` |
| `settings` | `settings` | `Setting` | `/api/vendor/home`, `/api/vendor/dashboard` |
| `on_boarding` (type=store) | `on_boarding` | `OnBoarding` | `/api/vendor/home` |

No new tables created for Vendor APIs. See `docs/VENDOR_API_DOCUMENTATION.md`.

## Other

| Firebase Collection | MySQL Table | Laravel Model | Customer API |
|---|---|---|---|
| `notifications` | `notifications` | `AppNotification` | `GET /api/customer/notifications` |
| `complaints` | `complaints` | `Complaint` | `POST /api/customer/complaints` |
| `SOS` | `sos` | `Sos` | `POST /api/customer/sos` |
| `tax` | `taxes` | `Tax` | Via generic API |
| `advertisements` | `advertisements` | `Advertisement` | Via generic API |
| `email_templates` | `email_templates` | `EmailTemplate` | Admin only |
| `dynamic_notification` | `dynamic_notifications` | `DynamicNotification` | Admin only |

## Firebase Storage → Laravel Storage

| Firebase Path | Laravel Path | Customer API |
|---|---|---|
| `profileImage/{uid}/{file}` | `profileImage/{userId}/{uuid}.ext` | `POST /api/customer/profile/image` |
| `images/{uuid}.png` | `uploads/{uuid}.ext` | `POST /api/v1/uploads` |
| `videos/{uuid}.mp4` | `uploads/{uuid}.ext` | `POST /api/v1/uploads` |

## Authentication Flow Mapping

| Firebase Auth | Laravel Sanctum |
|---|---|
| `signInWithEmailAndPassword` | `POST /api/customer/login` |
| `createUserWithEmailAndPassword` | `POST /api/customer/register` |
| `signOut` | `POST /api/customer/logout` |
| `verifyPhoneNumber` + OTP | **Not yet implemented** |
| Google sign-in | `POST /api/customer/auth/google` |
| Apple sign-in | `POST /api/customer/auth/apple` |
| `sendPasswordResetEmail` | `POST /api/customer/password/forgot`, `POST /api/customer/password/reset` |
