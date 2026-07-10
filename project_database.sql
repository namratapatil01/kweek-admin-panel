-- =============================================================================
-- KWEEK Admin Panel — project_database.sql
-- Generated from Laravel migrations + live MySQL schema verification
-- Purpose: CREATE required tables + minimal seed data to run the project
--
-- Seed data uses INSERT IGNORE (or conditional insert for permissions).
-- Safe to import on a database that already has data — existing rows are kept.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE DATABASE IF NOT EXISTS `kweek-admin-panel` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kweek-admin-panel`;

-- Table: advertisements
CREATE TABLE IF NOT EXISTS `advertisements`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `vendorId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advertisements_sectionid_index` (`sectionId`),
  KEY `advertisements_vendorid_index` (`vendorId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: app_users
CREATE TABLE IF NOT EXISTS `app_users`  (
  `id` varchar(64) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phoneNumber` varchar(32) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(32) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `isOwner` tinyint(1) NOT NULL DEFAULT 0,
  `isDocumentVerify` tinyint(1) NOT NULL DEFAULT 0,
  `profilePictureURL` varchar(255) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `ownerId` varchar(64) DEFAULT NULL,
  `serviceType` varchar(64) DEFAULT NULL,
  `rideType` varchar(64) DEFAULT NULL,
  `vehicleType` varchar(64) DEFAULT NULL,
  `vehicleId` varchar(64) DEFAULT NULL,
  `zoneId` varchar(64) DEFAULT NULL,
  `countryCode` varchar(8) DEFAULT NULL,
  `wallet_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `orderCompleted` int(10) unsigned NOT NULL DEFAULT 0,
  `fcmToken` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `carName` varchar(255) DEFAULT NULL,
  `carMakes` varchar(255) DEFAULT NULL,
  `carNumber` varchar(32) DEFAULT NULL,
  `carColor` varchar(32) DEFAULT NULL,
  `carPictureURL` varchar(255) DEFAULT NULL,
  `carProofPictureURL` varchar(255) DEFAULT NULL,
  `driverProofPictureURL` varchar(255) DEFAULT NULL,
  `driverRate` varchar(32) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `lastOnlineTimestamp` timestamp NULL DEFAULT NULL,
  `userBankDetails` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`userBankDetails`)),
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `shippingAddress` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shippingAddress`)),
  `carInfo` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`carInfo`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_users_role_isactive_index` (`role`,`isActive`),
  KEY `app_users_email_index` (`email`),
  KEY `app_users_phonenumber_index` (`phoneNumber`),
  KEY `app_users_role_index` (`role`),
  KEY `app_users_active_index` (`active`),
  KEY `app_users_sectionid_index` (`sectionId`),
  KEY `app_users_section_id_index` (`section_id`),
  KEY `app_users_vendorid_index` (`vendorID`),
  KEY `app_users_ownerid_index` (`ownerId`),
  KEY `app_users_zoneid_index` (`zoneId`),
  KEY `app_users_createdat_index` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: arropay_disbursement_withdrawals
CREATE TABLE IF NOT EXISTS `arropay_disbursement_withdrawals`  (
  `id` varchar(64) NOT NULL,
  `transaction_id` varchar(64) DEFAULT NULL,
  `order_number` varchar(128) DEFAULT NULL,
  `channel` varchar(32) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `account_number` varchar(64) DEFAULT NULL,
  `bank_code` varchar(32) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notify_url` varchar(255) DEFAULT NULL,
  `status` varchar(32) NOT NULL DEFAULT 'PENDING',
  `otp_hash` varchar(255) DEFAULT NULL,
  `provider_order_number` varchar(64) DEFAULT NULL,
  `gateway` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  PRIMARY KEY (`id`),
  KEY `arropay_disbursement_withdrawals_transaction_id_index` (`transaction_id`),
  KEY `arropay_disbursement_withdrawals_order_number_index` (`order_number`),
  KEY `arropay_disbursement_withdrawals_channel_index` (`channel`),
  KEY `arropay_disbursement_withdrawals_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: arropay_v2_payments
CREATE TABLE IF NOT EXISTS `arropay_v2_payments`  (
  `id` varchar(128) NOT NULL,
  `payment_id` varchar(128) DEFAULT NULL,
  `refno` varchar(128) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `flow` varchar(64) DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arropay_v2_payments_payment_id_index` (`payment_id`),
  KEY `arropay_v2_payments_refno_index` (`refno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: banner_items
CREATE TABLE IF NOT EXISTS `banner_items`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_items_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: booked_tables
CREATE TABLE IF NOT EXISTS `booked_tables`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booked_tables_vendorid_index` (`vendorID`),
  KEY `booked_tables_authorid_index` (`authorID`),
  KEY `booked_tables_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: brands
CREATE TABLE IF NOT EXISTS `brands`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brands_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: car_makes
CREATE TABLE IF NOT EXISTS `car_makes`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: car_models
CREATE TABLE IF NOT EXISTS `car_models`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `car_make_id` varchar(64) DEFAULT NULL,
  `car_make_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `car_models_car_make_id_index` (`car_make_id`),
  KEY `car_models_car_make_name_index` (`car_make_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cashback_redeems
CREATE TABLE IF NOT EXISTS `cashback_redeems`  (
  `id` varchar(64) NOT NULL,
  `cashbackId` varchar(64) NOT NULL,
  `userId` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashback_redeems_cashbackid_index` (`cashbackId`),
  KEY `cashback_redeems_userid_index` (`userId`),
  KEY `cashback_redeems_orderid_index` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cashbacks
CREATE TABLE IF NOT EXISTS `cashbacks`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `cashbackType` varchar(32) DEFAULT NULL,
  `cashbackAmount` decimal(15,2) DEFAULT NULL,
  `cashbackValue` decimal(15,2) DEFAULT NULL,
  `maximumDiscount` decimal(15,2) DEFAULT NULL,
  `minumumPurchaseAmount` decimal(15,2) DEFAULT NULL,
  `redeemLimit` int(10) unsigned DEFAULT NULL,
  `allCustomer` tinyint(1) NOT NULL DEFAULT 0,
  `allPayment` tinyint(1) NOT NULL DEFAULT 0,
  `isEnabled` tinyint(1) NOT NULL DEFAULT 1,
  `startDate` timestamp NULL DEFAULT NULL,
  `endDate` timestamp NULL DEFAULT NULL,
  `customerIds` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customerIds`)),
  `paymentMethods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`paymentMethods`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cashbacks_isenabled_index` (`isEnabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_admin
CREATE TABLE IF NOT EXISTS `chat_admin`  (
  `id` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  `restaurantId` varchar(64) DEFAULT NULL,
  `lastSenderId` varchar(64) DEFAULT NULL,
  `chatType` varchar(32) DEFAULT NULL,
  `lastMessage` text DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_admin_orderid_index` (`orderId`),
  KEY `chat_admin_customerid_index` (`customerId`),
  KEY `chat_admin_restaurantid_index` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_driver
CREATE TABLE IF NOT EXISTS `chat_driver`  (
  `id` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  `restaurantId` varchar(64) DEFAULT NULL,
  `lastSenderId` varchar(64) DEFAULT NULL,
  `chatType` varchar(32) DEFAULT NULL,
  `lastMessage` text DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_driver_orderid_index` (`orderId`),
  KEY `chat_driver_customerid_index` (`customerId`),
  KEY `chat_driver_restaurantid_index` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_provider
CREATE TABLE IF NOT EXISTS `chat_provider`  (
  `id` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  `restaurantId` varchar(64) DEFAULT NULL,
  `lastSenderId` varchar(64) DEFAULT NULL,
  `chatType` varchar(32) DEFAULT NULL,
  `lastMessage` text DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_provider_orderid_index` (`orderId`),
  KEY `chat_provider_customerid_index` (`customerId`),
  KEY `chat_provider_restaurantid_index` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_store
CREATE TABLE IF NOT EXISTS `chat_store`  (
  `id` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  `restaurantId` varchar(64) DEFAULT NULL,
  `lastSenderId` varchar(64) DEFAULT NULL,
  `chatType` varchar(32) DEFAULT NULL,
  `lastMessage` text DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_store_orderid_index` (`orderId`),
  KEY `chat_store_customerid_index` (`customerId`),
  KEY `chat_store_restaurantid_index` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_threads
CREATE TABLE IF NOT EXISTS `chat_threads`  (
  `id` varchar(64) NOT NULL,
  `chat_id` varchar(64) NOT NULL,
  `chat_type` varchar(32) NOT NULL,
  `message` text DEFAULT NULL,
  `messageType` varchar(32) DEFAULT NULL,
  `senderId` varchar(64) DEFAULT NULL,
  `receiverId` varchar(64) DEFAULT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `videoThumbnail` varchar(255) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_threads_chat_type_chat_id_index` (`chat_type`,`chat_id`),
  KEY `chat_threads_chat_id_index` (`chat_id`),
  KEY `chat_threads_chat_type_index` (`chat_type`),
  KEY `chat_threads_senderid_index` (`senderId`),
  KEY `chat_threads_receiverid_index` (`receiverId`),
  KEY `chat_threads_orderid_index` (`orderId`),
  KEY `chat_threads_createdat_index` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: chat_worker
CREATE TABLE IF NOT EXISTS `chat_worker`  (
  `id` varchar(64) NOT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  `restaurantId` varchar(64) DEFAULT NULL,
  `lastSenderId` varchar(64) DEFAULT NULL,
  `chatType` varchar(32) DEFAULT NULL,
  `lastMessage` text DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_worker_orderid_index` (`orderId`),
  KEY `chat_worker_customerid_index` (`customerId`),
  KEY `chat_worker_restaurantid_index` (`restaurantId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cms_pages
CREATE TABLE IF NOT EXISTS `cms_pages`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cms_pages_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: complaints
CREATE TABLE IF NOT EXISTS `complaints`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  `driverId` varchar(64) DEFAULT NULL,
  `customerId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaints_orderid_index` (`orderId`),
  KEY `complaints_driverid_index` (`driverId`),
  KEY `complaints_customerid_index` (`customerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: coupons
CREATE TABLE IF NOT EXISTS `coupons`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coupons_vendorid_index` (`vendorID`),
  KEY `coupons_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: currencies
CREATE TABLE IF NOT EXISTS `currencies`  (
  `id` varchar(64) NOT NULL,
  `country` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `symbol` varchar(16) DEFAULT NULL,
  `code` varchar(8) DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 0,
  `symbolAtRight` tinyint(1) NOT NULL DEFAULT 0,
  `decimal_degits` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `currencies_code_index` (`code`),
  KEY `currencies_isactive_index` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: documents
CREATE TABLE IF NOT EXISTS `documents`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(32) DEFAULT NULL,
  `frontSide` tinyint(1) NOT NULL DEFAULT 0,
  `backSide` tinyint(1) NOT NULL DEFAULT 0,
  `enable` tinyint(1) NOT NULL DEFAULT 1,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_type_index` (`type`),
  KEY `documents_enable_index` (`enable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: documents_verify
CREATE TABLE IF NOT EXISTS `documents_verify`  (
  `id` varchar(64) NOT NULL,
  `type` varchar(32) DEFAULT NULL,
  `documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: driver_payouts
CREATE TABLE IF NOT EXISTS `driver_payouts`  (
  `id` varchar(64) NOT NULL,
  `driverID` varchar(64) DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `role` varchar(32) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `adminNote` text DEFAULT NULL,
  `withdrawMethod` varchar(64) DEFAULT NULL,
  `paidDate` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `driver_payouts_driverid_index` (`driverID`),
  KEY `driver_payouts_vendorid_index` (`vendorID`),
  KEY `driver_payouts_paymentstatus_index` (`paymentStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: dynamic_notifications
CREATE TABLE IF NOT EXISTS `dynamic_notifications`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  `service_type` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dynamic_notifications_type_index` (`type`),
  KEY `dynamic_notifications_service_type_index` (`service_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: email_templates
CREATE TABLE IF NOT EXISTS `email_templates`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_templates_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorite_items
CREATE TABLE IF NOT EXISTS `favorite_items`  (
  `id` varchar(64) NOT NULL,
  `product_id` varchar(64) DEFAULT NULL,
  `store_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorite_items_product_id_index` (`product_id`),
  KEY `favorite_items_store_id_index` (`store_id`),
  KEY `favorite_items_user_id_index` (`user_id`),
  KEY `favorite_items_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorite_providers
CREATE TABLE IF NOT EXISTS `favorite_providers`  (
  `id` varchar(64) NOT NULL,
  `provider_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorite_providers_provider_id_index` (`provider_id`),
  KEY `favorite_providers_user_id_index` (`user_id`),
  KEY `favorite_providers_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorite_services
CREATE TABLE IF NOT EXISTS `favorite_services`  (
  `id` varchar(64) NOT NULL,
  `service_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `service_author_id` varchar(64) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorite_services_service_id_index` (`service_id`),
  KEY `favorite_services_user_id_index` (`user_id`),
  KEY `favorite_services_section_id_index` (`section_id`),
  KEY `favorite_services_service_author_id_index` (`service_author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: favorite_vendors
CREATE TABLE IF NOT EXISTS `favorite_vendors`  (
  `id` varchar(64) NOT NULL,
  `store_id` varchar(64) DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `favorite_vendors_store_id_index` (`store_id`),
  KEY `favorite_vendors_user_id_index` (`user_id`),
  KEY `favorite_vendors_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: gift_cards
CREATE TABLE IF NOT EXISTS `gift_cards`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: gift_purchases
CREATE TABLE IF NOT EXISTS `gift_purchases`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `giftId` varchar(64) DEFAULT NULL,
  `userid` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gift_purchases_giftid_index` (`giftId`),
  KEY `gift_purchases_userid_index` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: items_reviews
CREATE TABLE IF NOT EXISTS `items_reviews`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `orderid` varchar(64) DEFAULT NULL,
  `productId` varchar(64) DEFAULT NULL,
  `VendorId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_reviews_orderid_index` (`orderid`),
  KEY `items_reviews_productid_index` (`productId`),
  KEY `items_reviews_vendorid_index` (`VendorId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: migrations
CREATE TABLE IF NOT EXISTS `migrations`  (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notifications
CREATE TABLE IF NOT EXISTS `notifications`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: on_boarding
CREATE TABLE IF NOT EXISTS `on_boarding`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `on_boarding_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: order_transactions
CREATE TABLE IF NOT EXISTS `order_transactions`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `order_id` varchar(64) DEFAULT NULL,
  `driverId` varchar(64) DEFAULT NULL,
  `vendorId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_transactions_order_id_index` (`order_id`),
  KEY `order_transactions_driverid_index` (`driverId`),
  KEY `order_transactions_vendorid_index` (`vendorId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parcel_categories
CREATE TABLE IF NOT EXISTS `parcel_categories`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcel_categories_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parcel_coupons
CREATE TABLE IF NOT EXISTS `parcel_coupons`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcel_coupons_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parcel_orders
CREATE TABLE IF NOT EXISTS `parcel_orders`  (
  `id` varchar(64) NOT NULL,
  `status` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `subTotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `tip_amount` decimal(15,2) DEFAULT NULL,
  `adminCommission` decimal(15,2) DEFAULT NULL,
  `adminCommissionType` varchar(32) DEFAULT NULL,
  `payment_method` varchar(64) DEFAULT NULL,
  `paymentMethod` varchar(64) DEFAULT NULL,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `couponId` varchar(64) DEFAULT NULL,
  `couponCode` varchar(64) DEFAULT NULL,
  `taxSetting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxSetting`)),
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`author`)),
  `driver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver`)),
  `vendor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vendor`)),
  `provider` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider`)),
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver`)),
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sender`)),
  `rejectedByDrivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejectedByDrivers`)),
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `driverID` varchar(64) DEFAULT NULL,
  `parcelCategoryID` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcel_orders_status_index` (`status`),
  KEY `parcel_orders_section_id_index` (`section_id`),
  KEY `parcel_orders_sectionid_index` (`sectionId`),
  KEY `parcel_orders_authorid_index` (`authorID`),
  KEY `parcel_orders_createdat_index` (`createdAt`),
  KEY `parcel_orders_driverid_index` (`driverID`),
  KEY `parcel_orders_parcelcategoryid_index` (`parcelCategoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: parcel_weights
CREATE TABLE IF NOT EXISTS `parcel_weights`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_resets
CREATE TABLE IF NOT EXISTS `password_resets`  (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payouts
CREATE TABLE IF NOT EXISTS `payouts`  (
  `id` varchar(64) NOT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `role` varchar(32) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `adminNote` text DEFAULT NULL,
  `withdrawMethod` varchar(64) DEFAULT NULL,
  `paidDate` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payouts_vendorid_index` (`vendorID`),
  KEY `payouts_paymentstatus_index` (`paymentStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: permissions
CREATE TABLE IF NOT EXISTS `permissions`  (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission` varchar(255) NOT NULL,
  `routes` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permissions_role_id_index` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens`  (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` varchar(64) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: phone_otps
CREATE TABLE IF NOT EXISTS `phone_otps`  (
  `id` char(36) NOT NULL,
  `phone_number` varchar(32) NOT NULL,
  `country_code` varchar(8) DEFAULT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(32) NOT NULL DEFAULT 'driver',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phone_otps_phone_number_country_code_role_index` (`phone_number`,`country_code`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: popular_destinations
CREATE TABLE IF NOT EXISTS `popular_destinations`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `popular_destinations_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: promos
CREATE TABLE IF NOT EXISTS `promos`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promos_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: provider_categories
CREATE TABLE IF NOT EXISTS `provider_categories`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `parentCategoryId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_categories_sectionid_index` (`sectionId`),
  KEY `provider_categories_parentcategoryid_index` (`parentCategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: provider_orders
CREATE TABLE IF NOT EXISTS `provider_orders`  (
  `id` varchar(64) NOT NULL,
  `status` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `subTotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `tip_amount` decimal(15,2) DEFAULT NULL,
  `adminCommission` decimal(15,2) DEFAULT NULL,
  `adminCommissionType` varchar(32) DEFAULT NULL,
  `payment_method` varchar(64) DEFAULT NULL,
  `paymentMethod` varchar(64) DEFAULT NULL,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `couponId` varchar(64) DEFAULT NULL,
  `couponCode` varchar(64) DEFAULT NULL,
  `taxSetting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxSetting`)),
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`author`)),
  `driver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver`)),
  `vendor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vendor`)),
  `provider` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider`)),
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver`)),
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sender`)),
  `rejectedByDrivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejectedByDrivers`)),
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `workerId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_orders_status_index` (`status`),
  KEY `provider_orders_section_id_index` (`section_id`),
  KEY `provider_orders_sectionid_index` (`sectionId`),
  KEY `provider_orders_authorid_index` (`authorID`),
  KEY `provider_orders_createdat_index` (`createdAt`),
  KEY `provider_orders_workerid_index` (`workerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: providers_coupons
CREATE TABLE IF NOT EXISTS `providers_coupons`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `providerId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `providers_coupons_sectionid_index` (`sectionId`),
  KEY `providers_coupons_providerid_index` (`providerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: providers_services
CREATE TABLE IF NOT EXISTS `providers_services`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `categoryId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `providers_services_sectionid_index` (`sectionId`),
  KEY `providers_services_categoryid_index` (`categoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: providers_workers
CREATE TABLE IF NOT EXISTS `providers_workers`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `providerId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `providers_workers_providerid_index` (`providerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ratings
CREATE TABLE IF NOT EXISTS `ratings`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `orderid` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratings_orderid_index` (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: referrals
CREATE TABLE IF NOT EXISTS `referrals`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `referralBy` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referrals_referralby_index` (`referralBy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_coupons
CREATE TABLE IF NOT EXISTS `rental_coupons`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_coupons_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_orders
CREATE TABLE IF NOT EXISTS `rental_orders`  (
  `id` varchar(64) NOT NULL,
  `status` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `subTotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `tip_amount` decimal(15,2) DEFAULT NULL,
  `adminCommission` decimal(15,2) DEFAULT NULL,
  `adminCommissionType` varchar(32) DEFAULT NULL,
  `payment_method` varchar(64) DEFAULT NULL,
  `paymentMethod` varchar(64) DEFAULT NULL,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `couponId` varchar(64) DEFAULT NULL,
  `couponCode` varchar(64) DEFAULT NULL,
  `taxSetting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxSetting`)),
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`author`)),
  `driver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver`)),
  `vendor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vendor`)),
  `provider` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider`)),
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver`)),
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sender`)),
  `rejectedByDrivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejectedByDrivers`)),
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `driverId` varchar(64) DEFAULT NULL,
  `vehicleId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_orders_status_index` (`status`),
  KEY `rental_orders_section_id_index` (`section_id`),
  KEY `rental_orders_sectionid_index` (`sectionId`),
  KEY `rental_orders_authorid_index` (`authorID`),
  KEY `rental_orders_createdat_index` (`createdAt`),
  KEY `rental_orders_driverid_index` (`driverId`),
  KEY `rental_orders_vehicleid_index` (`vehicleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_packages
CREATE TABLE IF NOT EXISTS `rental_packages`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `vehicleTypeId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_packages_sectionid_index` (`sectionId`),
  KEY `rental_packages_vehicletypeid_index` (`vehicleTypeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_vehicle_types
CREATE TABLE IF NOT EXISTS `rental_vehicle_types`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: review_attributes
CREATE TABLE IF NOT EXISTS `review_attributes`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rides
CREATE TABLE IF NOT EXISTS `rides`  (
  `id` varchar(64) NOT NULL,
  `status` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `subTotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `tip_amount` decimal(15,2) DEFAULT NULL,
  `adminCommission` decimal(15,2) DEFAULT NULL,
  `adminCommissionType` varchar(32) DEFAULT NULL,
  `payment_method` varchar(64) DEFAULT NULL,
  `paymentMethod` varchar(64) DEFAULT NULL,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `couponId` varchar(64) DEFAULT NULL,
  `couponCode` varchar(64) DEFAULT NULL,
  `taxSetting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxSetting`)),
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`author`)),
  `driver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver`)),
  `vendor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vendor`)),
  `provider` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider`)),
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver`)),
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sender`)),
  `rejectedByDrivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejectedByDrivers`)),
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `driverId` varchar(64) DEFAULT NULL,
  `vehicleId` varchar(64) DEFAULT NULL,
  `scheduleDateTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rides_status_index` (`status`),
  KEY `rides_section_id_index` (`section_id`),
  KEY `rides_sectionid_index` (`sectionId`),
  KEY `rides_authorid_index` (`authorID`),
  KEY `rides_createdat_index` (`createdAt`),
  KEY `rides_driverid_index` (`driverId`),
  KEY `rides_vehicleid_index` (`vehicleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: role
CREATE TABLE IF NOT EXISTS `role`  (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sections
CREATE TABLE IF NOT EXISTS `sections`  (
  `id` varchar(64) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `serviceType` varchar(64) DEFAULT NULL,
  `serviceTypeFlag` varchar(64) DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 1,
  `sectionImage` varchar(255) DEFAULT NULL,
  `color` varchar(32) DEFAULT NULL,
  `nearByRadius` int(10) unsigned DEFAULT NULL,
  `delivery_charge` int(10) unsigned DEFAULT NULL,
  `adminCommision` varchar(32) DEFAULT NULL,
  `dine_in_active` tinyint(1) NOT NULL DEFAULT 0,
  `rideType` varchar(64) DEFAULT NULL,
  `is_product_details` tinyint(1) NOT NULL DEFAULT 0,
  `cab_service_template` varchar(64) DEFAULT NULL,
  `enableCashbackOffer` tinyint(1) NOT NULL DEFAULT 0,
  `theme` varchar(64) DEFAULT NULL,
  `referralAmount` int(10) unsigned DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sections_servicetype_index` (`serviceType`),
  KEY `sections_isactive_index` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: services
CREATE TABLE IF NOT EXISTS `services`  (
  `id` varchar(64) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `flag` varchar(32) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: settings
CREATE TABLE IF NOT EXISTS `settings`  (
  `id` varchar(128) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sos
CREATE TABLE IF NOT EXISTS `sos`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `orderId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sos_orderid_index` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: stories
CREATE TABLE IF NOT EXISTS `stories`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `sectionID` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stories_vendorid_index` (`vendorID`),
  KEY `stories_sectionid_index` (`sectionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: subscription_histories
CREATE TABLE IF NOT EXISTS `subscription_histories`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_histories_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: subscription_plans
CREATE TABLE IF NOT EXISTS `subscription_plans`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_plans_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: taxes
CREATE TABLE IF NOT EXISTS `taxes`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxes_sectionid_index` (`sectionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
CREATE TABLE IF NOT EXISTS `users`  (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vehicle_types
CREATE TABLE IF NOT EXISTS `vehicle_types`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendor_attributes
CREATE TABLE IF NOT EXISTS `vendor_attributes`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendor_categories
CREATE TABLE IF NOT EXISTS `vendor_categories`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_categories_section_id_index` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendor_filters
CREATE TABLE IF NOT EXISTS `vendor_filters`  (
  `id` varchar(64) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_filters_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendor_orders
CREATE TABLE IF NOT EXISTS `vendor_orders`  (
  `id` varchar(64) NOT NULL,
  `status` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `sectionId` varchar(64) DEFAULT NULL,
  `authorID` varchar(64) DEFAULT NULL,
  `subTotal` decimal(15,2) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT NULL,
  `tip_amount` decimal(15,2) DEFAULT NULL,
  `adminCommission` decimal(15,2) DEFAULT NULL,
  `adminCommissionType` varchar(32) DEFAULT NULL,
  `payment_method` varchar(64) DEFAULT NULL,
  `paymentMethod` varchar(64) DEFAULT NULL,
  `paymentStatus` varchar(32) DEFAULT NULL,
  `couponId` varchar(64) DEFAULT NULL,
  `couponCode` varchar(64) DEFAULT NULL,
  `taxSetting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`taxSetting`)),
  `author` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`author`)),
  `driver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`driver`)),
  `vendor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vendor`)),
  `provider` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider`)),
  `products` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`products`)),
  `address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`address`)),
  `receiver` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receiver`)),
  `sender` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sender`)),
  `rejectedByDrivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rejectedByDrivers`)),
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `driverID` varchar(64) DEFAULT NULL,
  `takeAway` tinyint(1) NOT NULL DEFAULT 0,
  `scheduleTime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_orders_status_index` (`status`),
  KEY `vendor_orders_section_id_index` (`section_id`),
  KEY `vendor_orders_sectionid_index` (`sectionId`),
  KEY `vendor_orders_authorid_index` (`authorID`),
  KEY `vendor_orders_createdat_index` (`createdAt`),
  KEY `vendor_orders_vendorid_index` (`vendorID`),
  KEY `vendor_orders_driverid_index` (`driverID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendor_products
CREATE TABLE IF NOT EXISTS `vendor_products`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `vendorID` varchar(64) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `categoryID` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_products_vendorid_index` (`vendorID`),
  KEY `vendor_products_section_id_index` (`section_id`),
  KEY `vendor_products_categoryid_index` (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: vendors
CREATE TABLE IF NOT EXISTS `vendors`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `categoryPhoto` varchar(255) DEFAULT NULL,
  `section_id` varchar(64) DEFAULT NULL,
  `zoneId` varchar(64) DEFAULT NULL,
  `categoryID` varchar(64) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `phonenumber` varchar(32) DEFAULT NULL,
  `reststatus` tinyint(1) NOT NULL DEFAULT 1,
  `walletAmount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reviewsSum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reviewsCount` int(10) unsigned NOT NULL DEFAULT 0,
  `adminCommission` decimal(8,2) DEFAULT NULL,
  `dine_in_active` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` timestamp NULL DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `workingHours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`workingHours`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filters`)),
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendors_title_index` (`title`),
  KEY `vendors_section_id_index` (`section_id`),
  KEY `vendors_zoneid_index` (`zoneId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: wallet
CREATE TABLE IF NOT EXISTS `wallet`  (
  `id` varchar(64) NOT NULL,
  `user_id` varchar(64) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `isTopUp` tinyint(1) NOT NULL DEFAULT 1,
  `payment_method` varchar(64) DEFAULT NULL,
  `payment_status` varchar(32) NOT NULL DEFAULT 'success',
  `transactionUser` varchar(64) DEFAULT NULL,
  `order_id` varchar(64) DEFAULT NULL,
  `subscription_id` varchar(64) DEFAULT NULL,
  `serviceType` varchar(64) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_user_id_index` (`user_id`),
  KEY `wallet_payment_status_index` (`payment_status`),
  KEY `wallet_order_id_index` (`order_id`),
  KEY `wallet_date_index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: wallet_transactions
CREATE TABLE IF NOT EXISTS `wallet_transactions`  (
  `id` varchar(64) NOT NULL,
  `userId` varchar(64) DEFAULT NULL,
  `userType` varchar(32) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) DEFAULT NULL,
  `orderType` varchar(64) DEFAULT NULL,
  `paymentType` varchar(64) DEFAULT NULL,
  `transactionId` varchar(64) DEFAULT NULL,
  `createdDate` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_userid_index` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: withdraw_methods
CREATE TABLE IF NOT EXISTS `withdraw_methods`  (
  `id` varchar(64) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT NULL,
  `isEnable` tinyint(1) DEFAULT NULL,
  `publish` tinyint(1) DEFAULT NULL,
  `isEnabled` tinyint(1) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `createdAt` timestamp NULL DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `userId` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdraw_methods_userid_index` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: zones
CREATE TABLE IF NOT EXISTS `zones`  (
  `id` varchar(64) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `area` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`area`)),
  `publish` tinyint(1) NOT NULL DEFAULT 1,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zones_publish_index` (`publish`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- Required initial data
-- Uses INSERT IGNORE where primary/unique keys exist.
-- Permissions block runs only when role_id=1 has zero rows.
-- =============================================================================

INSERT IGNORE INTO `role` (`id`, `role_name`, `created_at`, `updated_at`) VALUES
(1, "Super Admin", NOW(), NOW());

-- Admin panel: admin@emart.com / 12345678
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, "Admin", "admin@emart.com", '$2y$10$/rYS8fCmXBMlqMs.SBV8/e9zujjiK9cdw2gN7HwD/S.VGgBvcga6O', 1, NOW(), NOW());

INSERT IGNORE INTO `settings` (`id`, `value`, `created_at`, `updated_at`) VALUES
('globalSettings', '{\"admin_panel_color\":\"#000000\",\"theme_color\":\"primary\",\"website_color\":\"#1eb01c\",\"applicationName\":\"KWEEK\",\"appLogo\":\"\\/images\\/kweek-logo.png\",\"defaultCountryCode\":\"+63\",\"isSelfDelivery\":true,\"isEnableAdsFeature\":false,\"store_panel_color\":\"#ff3838\",\"provider_panel_color\":\"#9928eb\",\"app_customer_color\":\"#211612\",\"app_driver_color\":\"#27c57d\",\"app_store_color\":\"#1da4fe\",\"worker_app_color\":\"#398e70\"}', NOW(), NOW()),
('placeHolderImage', '{\"image\":\"\"}', NOW(), NOW()),
('languages', '{\"list\":[{\"slug\":\"en\",\"title\":\"English\",\"isActive\":true,\"is_rtl\":false}]}', NOW(), NOW()),
('Version', '{\"web_version\":\"1.0.0\",\"app_version\":\"1.0.0\"}', NOW(), NOW()),
('googleMapKey', '{\"key\":\"\"}', NOW(), NOW()),
('DriverNearBy', '{\"driverNearBy\":10}', NOW(), NOW()),
('notification_setting', '{\"senderId\":\"\",\"serviceJson\":\"\"}', NOW(), NOW()),
('termsAndConditions', '{\"terms_and_condition\":\"<p>Terms and conditions. Update via admin panel.<\\/p>\"}', NOW(), NOW()),
('privacyPolicy', '{\"privacy_policy\":\"<p>Privacy policy. Update via admin panel.<\\/p>\"}', NOW(), NOW()),
('walletSettings', '{\"isEnabled\":true}', NOW(), NOW()),
('vendor', '{\"auto_approve_store\":false,\"auto_approve_vendor\":true,\"subscription_model\":true}', NOW(), NOW()),
('provider', '{\"auto_approve_provider\":true}', NOW(), NOW()),
('document_verification_settings', '{\"isEnabled\":false}', NOW(), NOW()),
('maintenance_settings', '{\"isMaintenance\":false}', NOW(), NOW());

INSERT IGNORE INTO `currencies` (`id`, `country`, `name`, `symbol`, `code`, `isActive`, `symbolAtRight`, `decimal_degits`, `created_at`, `updated_at`) VALUES
("kweek-php-peso", "Philippines", "Philippine Peso", "₱", "PHP", 1, 0, 2, NOW(), NOW());

INSERT IGNORE INTO `services` (`id`, `name`, `flag`, `payload`, `created_at`, `updated_at`) VALUES
('nwjicjbMYwb5hPoEitAS', 'On Demand Service', 'ondemand-service', NULL, NOW(), NOW()),
('TGTP44PgU5G6BU2uP7iY', 'Multivendor Delivery Service', 'delivery-service', NULL, NOW(), NOW()),
('ny3sssVJ7FCrPgxvsZNO', 'Ecommerce Service', 'ecommerce-service', NULL, NOW(), NOW()),
('zxzjypGIugTmlb0ZeOT0', 'Cab Service', 'cab-service', NULL, NOW(), NOW()),
('sDsB9pMGXLBMnbQiTMKF', 'Parcel Delivery Service', 'parcel_delivery', NULL, NOW(), NOW()),
('FDOAplq4EHOQ3U5SLsRr', 'Rental Service', 'rental-service', NULL, NOW(), NOW());

INSERT IGNORE INTO `sections` (`id`, `name`, `serviceType`, `serviceTypeFlag`, `isActive`, `color`, `nearByRadius`, `delivery_charge`, `referralAmount`, `dine_in_active`, `is_product_details`, `enableCashbackOffer`, `payload`, `created_at`, `updated_at`) VALUES
("6285dd3281531", "Shop", "Ecommerce Service", "ecommerce-service", 1, "#6045c8", 13000, 12, 20, 0, 0, 0, '{\"adminCommision\":{\"enable\":true,\"commission\":10,\"type\":\"percentage\"}}', NOW(), NOW());

-- Super Admin RBAC permissions (skipped if any permission exists for role_id=1)
INSERT INTO `permissions` (`id`, `role_id`, `permission`, `routes`, `created_at`, `updated_at`)
SELECT * FROM (
  SELECT 1 AS id, 1 AS role_id, 'zone' AS permission, 'zone.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 2 AS id, 1 AS role_id, 'zone' AS permission, 'zone.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 3 AS id, 1 AS role_id, 'zone' AS permission, 'zone.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 4 AS id, 1 AS role_id, 'zone' AS permission, 'zone.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 5 AS id, 1 AS role_id, 'section-service' AS permission, 'section-service.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 6 AS id, 1 AS role_id, 'section-service' AS permission, 'section.service.save' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 7 AS id, 1 AS role_id, 'section-service' AS permission, 'section.service.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 8 AS id, 1 AS role_id, 'section-service' AS permission, 'section.service.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 9 AS id, 1 AS role_id, 'roles' AS permission, 'role.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 10 AS id, 1 AS role_id, 'roles' AS permission, 'role.save' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 11 AS id, 1 AS role_id, 'roles' AS permission, 'role.store' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 12 AS id, 1 AS role_id, 'roles' AS permission, 'role.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 13 AS id, 1 AS role_id, 'roles' AS permission, 'role.update' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 14 AS id, 1 AS role_id, 'roles' AS permission, 'role.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 15 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 16 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 17 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users.store' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 18 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 19 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users.update' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 20 AS id, 1 AS role_id, 'admins' AS permission, 'admin.users.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 21 AS id, 1 AS role_id, 'users' AS permission, 'users' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 22 AS id, 1 AS role_id, 'users' AS permission, 'users.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 23 AS id, 1 AS role_id, 'users' AS permission, 'users.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 24 AS id, 1 AS role_id, 'users' AS permission, 'users.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 25 AS id, 1 AS role_id, 'users' AS permission, 'users.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 26 AS id, 1 AS role_id, 'vendors' AS permission, 'vendors' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 27 AS id, 1 AS role_id, 'vendors' AS permission, 'vendors.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 28 AS id, 1 AS role_id, 'vendors' AS permission, 'vendors.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 29 AS id, 1 AS role_id, 'vendors' AS permission, 'vendors.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 30 AS id, 1 AS role_id, 'approve_vendors' AS permission, 'approve.vendors.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 31 AS id, 1 AS role_id, 'approve_vendors' AS permission, 'approve.vendors.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 32 AS id, 1 AS role_id, 'pending_vendors' AS permission, 'pending.vendors.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 33 AS id, 1 AS role_id, 'pending_vendors' AS permission, 'pending.vendors.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 34 AS id, 1 AS role_id, 'vendors-document' AS permission, 'vendor.document.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 35 AS id, 1 AS role_id, 'vendors-document' AS permission, 'vendor.document.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 36 AS id, 1 AS role_id, 'providers' AS permission, 'providers' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 37 AS id, 1 AS role_id, 'providers' AS permission, 'providers.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 38 AS id, 1 AS role_id, 'providers' AS permission, 'providers.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 39 AS id, 1 AS role_id, 'providers' AS permission, 'providers.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 40 AS id, 1 AS role_id, 'providers' AS permission, 'providers.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 41 AS id, 1 AS role_id, 'stores' AS permission, 'stores' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 42 AS id, 1 AS role_id, 'stores' AS permission, 'stores.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 43 AS id, 1 AS role_id, 'stores' AS permission, 'stores.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 44 AS id, 1 AS role_id, 'stores' AS permission, 'stores.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 45 AS id, 1 AS role_id, 'stores' AS permission, 'stores.copy' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 46 AS id, 1 AS role_id, 'stores' AS permission, 'stores.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 47 AS id, 1 AS role_id, 'owners' AS permission, 'owner.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 48 AS id, 1 AS role_id, 'owners' AS permission, 'owner.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 49 AS id, 1 AS role_id, 'owners' AS permission, 'owner.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 50 AS id, 1 AS role_id, 'owners' AS permission, 'owner.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 51 AS id, 1 AS role_id, 'owners' AS permission, 'owner.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 52 AS id, 1 AS role_id, 'approve_owners' AS permission, 'approve.owner.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 53 AS id, 1 AS role_id, 'approve_owners' AS permission, 'approve.owner.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 54 AS id, 1 AS role_id, 'pending_owners' AS permission, 'pending.owner.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 55 AS id, 1 AS role_id, 'pending_owners' AS permission, 'pending.owner.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 56 AS id, 1 AS role_id, 'drivers' AS permission, 'drivers' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 57 AS id, 1 AS role_id, 'drivers' AS permission, 'drivers.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 58 AS id, 1 AS role_id, 'drivers' AS permission, 'drivers.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 59 AS id, 1 AS role_id, 'drivers' AS permission, 'drivers.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 60 AS id, 1 AS role_id, 'drivers' AS permission, 'drivers.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 61 AS id, 1 AS role_id, 'fleet-drivers' AS permission, 'fleet.drivers' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 62 AS id, 1 AS role_id, 'fleet-drivers' AS permission, 'fleet.drivers.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 63 AS id, 1 AS role_id, 'fleet-drivers' AS permission, 'fleet.drivers.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 64 AS id, 1 AS role_id, 'fleet-drivers' AS permission, 'fleet.drivers.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 65 AS id, 1 AS role_id, 'fleet-drivers' AS permission, 'fleet.drivers.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 66 AS id, 1 AS role_id, 'approve_drivers' AS permission, 'approve.driver.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 67 AS id, 1 AS role_id, 'approve_drivers' AS permission, 'approve.driver.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 68 AS id, 1 AS role_id, 'pending_drivers' AS permission, 'pending.driver.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 69 AS id, 1 AS role_id, 'pending_drivers' AS permission, 'pending.driver.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 70 AS id, 1 AS role_id, 'drivers-document' AS permission, 'driver.document.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 71 AS id, 1 AS role_id, 'drivers-document' AS permission, 'driver.document.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 72 AS id, 1 AS role_id, 'categories' AS permission, 'categories' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 73 AS id, 1 AS role_id, 'categories' AS permission, 'categories.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 74 AS id, 1 AS role_id, 'categories' AS permission, 'categories.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 75 AS id, 1 AS role_id, 'categories' AS permission, 'categories.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 76 AS id, 1 AS role_id, 'brands' AS permission, 'brands' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 77 AS id, 1 AS role_id, 'brands' AS permission, 'brands.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 78 AS id, 1 AS role_id, 'brands' AS permission, 'brands.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 79 AS id, 1 AS role_id, 'brands' AS permission, 'brands.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 80 AS id, 1 AS role_id, 'destinations' AS permission, 'destinations' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 81 AS id, 1 AS role_id, 'destinations' AS permission, 'destinations.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 82 AS id, 1 AS role_id, 'destinations' AS permission, 'destinations.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 83 AS id, 1 AS role_id, 'destinations' AS permission, 'destinations.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 84 AS id, 1 AS role_id, 'item-attributes' AS permission, 'item.attributes' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 85 AS id, 1 AS role_id, 'item-attributes' AS permission, 'item.attributes.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 86 AS id, 1 AS role_id, 'item-attributes' AS permission, 'item.attributes.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 87 AS id, 1 AS role_id, 'item-attributes' AS permission, 'item.attributes.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 88 AS id, 1 AS role_id, 'review-attributes' AS permission, 'review.attributes' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 89 AS id, 1 AS role_id, 'review-attributes' AS permission, 'review.attributes.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 90 AS id, 1 AS role_id, 'review-attributes' AS permission, 'review.attributes.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 91 AS id, 1 AS role_id, 'review-attributes' AS permission, 'review.attributes.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 92 AS id, 1 AS role_id, 'report' AS permission, 'sales' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 93 AS id, 1 AS role_id, 'items' AS permission, 'items' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 94 AS id, 1 AS role_id, 'items' AS permission, 'items.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 95 AS id, 1 AS role_id, 'items' AS permission, 'items.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 96 AS id, 1 AS role_id, 'items' AS permission, 'items.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 97 AS id, 1 AS role_id, 'god-eye' AS permission, 'map' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 98 AS id, 1 AS role_id, 'orders' AS permission, 'orders' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 99 AS id, 1 AS role_id, 'orders' AS permission, 'orders.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 100 AS id, 1 AS role_id, 'orders' AS permission, 'orders.print' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 101 AS id, 1 AS role_id, 'orders' AS permission, 'orders.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 102 AS id, 1 AS role_id, 'deliveryman' AS permission, 'deliveryman' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 103 AS id, 1 AS role_id, 'deliveryman' AS permission, 'deliveryman.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 104 AS id, 1 AS role_id, 'deliveryman' AS permission, 'deliveryman.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 105 AS id, 1 AS role_id, 'deliveryman' AS permission, 'deliveryman.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 106 AS id, 1 AS role_id, 'gift-cards' AS permission, 'gift-card.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 107 AS id, 1 AS role_id, 'gift-cards' AS permission, 'gift-card.save' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 108 AS id, 1 AS role_id, 'gift-cards' AS permission, 'gift-card.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 109 AS id, 1 AS role_id, 'gift-cards' AS permission, 'gift-card.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 110 AS id, 1 AS role_id, 'coupons' AS permission, 'coupons' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 111 AS id, 1 AS role_id, 'coupons' AS permission, 'coupons.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 112 AS id, 1 AS role_id, 'coupons' AS permission, 'coupons.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 113 AS id, 1 AS role_id, 'coupons' AS permission, 'coupons.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 114 AS id, 1 AS role_id, 'advertisements' AS permission, 'advertisements' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 115 AS id, 1 AS role_id, 'advertisements' AS permission, 'advertisements.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 116 AS id, 1 AS role_id, 'advertisements' AS permission, 'advertisements.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 117 AS id, 1 AS role_id, 'advertisements' AS permission, 'advertisements.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 118 AS id, 1 AS role_id, 'advertisements' AS permission, 'advertisements.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 119 AS id, 1 AS role_id, 'advertisements-list' AS permission, 'advertisements.request' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 120 AS id, 1 AS role_id, 'banners' AS permission, 'banners' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 121 AS id, 1 AS role_id, 'banners' AS permission, 'banners.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 122 AS id, 1 AS role_id, 'banners' AS permission, 'banners.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 123 AS id, 1 AS role_id, 'banners' AS permission, 'banners.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 124 AS id, 1 AS role_id, 'documents' AS permission, 'documents.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 125 AS id, 1 AS role_id, 'documents' AS permission, 'documents.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 126 AS id, 1 AS role_id, 'documents' AS permission, 'documents.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 127 AS id, 1 AS role_id, 'documents' AS permission, 'documents.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 128 AS id, 1 AS role_id, 'subscription-plans' AS permission, 'subscription-plans' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 129 AS id, 1 AS role_id, 'subscription-plans' AS permission, 'subscription-plans.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 130 AS id, 1 AS role_id, 'subscription-plans' AS permission, 'subscription-plans.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 131 AS id, 1 AS role_id, 'subscription-plans' AS permission, 'subscription-plans.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 132 AS id, 1 AS role_id, 'subscription-history' AS permission, 'subscription.history' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 133 AS id, 1 AS role_id, 'ondemand-categories' AS permission, 'ondemand.categories' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 134 AS id, 1 AS role_id, 'ondemand-categories' AS permission, 'ondemand.categories.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 135 AS id, 1 AS role_id, 'ondemand-categories' AS permission, 'ondemand.categories.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 136 AS id, 1 AS role_id, 'ondemand-categories' AS permission, 'ondemand.categories.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 137 AS id, 1 AS role_id, 'ondemand-coupons' AS permission, 'ondemand.coupons' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 138 AS id, 1 AS role_id, 'ondemand-coupons' AS permission, 'ondemand.coupons.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 139 AS id, 1 AS role_id, 'ondemand-coupons' AS permission, 'ondemand.coupons.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 140 AS id, 1 AS role_id, 'ondemand-coupons' AS permission, 'ondemand.coupons.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 141 AS id, 1 AS role_id, 'ondemand-services' AS permission, 'ondemand.services' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 142 AS id, 1 AS role_id, 'ondemand-services' AS permission, 'ondemand.services.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 143 AS id, 1 AS role_id, 'ondemand-services' AS permission, 'ondemand.services.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 144 AS id, 1 AS role_id, 'ondemand-services' AS permission, 'ondemand.services.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 145 AS id, 1 AS role_id, 'ondemand-bookings' AS permission, 'ondemand.bookings.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 146 AS id, 1 AS role_id, 'ondemand-bookings' AS permission, 'ondemand.bookings.print' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 147 AS id, 1 AS role_id, 'ondemand-bookings' AS permission, 'ondemand.bookings.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 148 AS id, 1 AS role_id, 'ondemand-bookings' AS permission, 'ondemand.bookings.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 149 AS id, 1 AS role_id, 'ondemand-workers' AS permission, 'ondemand.workers.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 150 AS id, 1 AS role_id, 'ondemand-workers' AS permission, 'ondemand.workers.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 151 AS id, 1 AS role_id, 'ondemand-workers' AS permission, 'ondemand.workers.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 152 AS id, 1 AS role_id, 'ondemand-workers' AS permission, 'ondemand.workers.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 153 AS id, 1 AS role_id, 'on-board' AS permission, 'onboard.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 154 AS id, 1 AS role_id, 'on-board' AS permission, 'onboard.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 155 AS id, 1 AS role_id, 'parcel-service-god-eye' AS permission, 'parcel-service-map' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 156 AS id, 1 AS role_id, 'parcel-categories' AS permission, 'parcel.categories' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 157 AS id, 1 AS role_id, 'parcel-categories' AS permission, 'parcel.categories.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 158 AS id, 1 AS role_id, 'parcel-categories' AS permission, 'parcel.categories.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 159 AS id, 1 AS role_id, 'parcel-categories' AS permission, 'parcel.categories.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 160 AS id, 1 AS role_id, 'parcel-weight' AS permission, 'parcel.weight' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 161 AS id, 1 AS role_id, 'parcel-weight' AS permission, 'parcel.weight.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 162 AS id, 1 AS role_id, 'parcel-weight' AS permission, 'parcel.weight.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 163 AS id, 1 AS role_id, 'parcel-weight' AS permission, 'parcel.weight.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 164 AS id, 1 AS role_id, 'parcel-coupons' AS permission, 'parcel.coupons' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 165 AS id, 1 AS role_id, 'parcel-coupons' AS permission, 'parcel.coupons.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 166 AS id, 1 AS role_id, 'parcel-coupons' AS permission, 'parcel.coupons.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 167 AS id, 1 AS role_id, 'parcel-coupons' AS permission, 'parcel.coupons.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 168 AS id, 1 AS role_id, 'parcel-orders' AS permission, 'parcel.orders' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 169 AS id, 1 AS role_id, 'parcel-orders' AS permission, 'parcel.orders.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 170 AS id, 1 AS role_id, 'parcel-orders' AS permission, 'parcel.orders.print' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 171 AS id, 1 AS role_id, 'parcel-orders' AS permission, 'parcel.orders.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 172 AS id, 1 AS role_id, 'cab-service-god-eye' AS permission, 'cab-service-map' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 173 AS id, 1 AS role_id, 'rides' AS permission, 'rides' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 174 AS id, 1 AS role_id, 'rides' AS permission, 'rides.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 175 AS id, 1 AS role_id, 'rides' AS permission, 'rides.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 176 AS id, 1 AS role_id, 'sos-rides' AS permission, 'sos.rides' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 177 AS id, 1 AS role_id, 'sos-rides' AS permission, 'sos.rides.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 178 AS id, 1 AS role_id, 'sos-rides' AS permission, 'sos.rides.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 179 AS id, 1 AS role_id, 'cab-promo' AS permission, 'cab.promo' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 180 AS id, 1 AS role_id, 'cab-promo' AS permission, 'cab.promo.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 181 AS id, 1 AS role_id, 'cab-promo' AS permission, 'cab.promo.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 182 AS id, 1 AS role_id, 'cab-promo' AS permission, 'cab.promo.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 183 AS id, 1 AS role_id, 'complaints' AS permission, 'complaints' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 184 AS id, 1 AS role_id, 'complaints' AS permission, 'complaints.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 185 AS id, 1 AS role_id, 'complaints' AS permission, 'complaints.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 186 AS id, 1 AS role_id, 'cab-vehicle-type' AS permission, 'cab-vehicle-type' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 187 AS id, 1 AS role_id, 'cab-vehicle-type' AS permission, 'cab-vehicle-type.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 188 AS id, 1 AS role_id, 'cab-vehicle-type' AS permission, 'cab-vehicle-type.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 189 AS id, 1 AS role_id, 'cab-vehicle-type' AS permission, 'cab-vehicle-type.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 190 AS id, 1 AS role_id, 'rental-plural-god-eye' AS permission, 'rental-plural-map' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 191 AS id, 1 AS role_id, 'rental-vehicle-type' AS permission, 'rental-vehicle-type' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 192 AS id, 1 AS role_id, 'rental-vehicle-type' AS permission, 'rental-vehicle-type.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 193 AS id, 1 AS role_id, 'rental-vehicle-type' AS permission, 'rental-vehicle-type.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 194 AS id, 1 AS role_id, 'rental-vehicle-type' AS permission, 'rental-vehicle-type.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 195 AS id, 1 AS role_id, 'rental-discount' AS permission, 'rental-discount' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 196 AS id, 1 AS role_id, 'rental-discount' AS permission, 'rental-discount.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 197 AS id, 1 AS role_id, 'rental-discount' AS permission, 'rental-discount.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 198 AS id, 1 AS role_id, 'rental-discount' AS permission, 'rental-discount.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 199 AS id, 1 AS role_id, 'rental-orders' AS permission, 'rental-orders' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 200 AS id, 1 AS role_id, 'rental-orders' AS permission, 'rental-orders.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 201 AS id, 1 AS role_id, 'rental-orders' AS permission, 'rental-orders.print' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 202 AS id, 1 AS role_id, 'rental-orders' AS permission, 'rental-orders.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 203 AS id, 1 AS role_id, 'rental-vehicle' AS permission, 'rental-vehicle' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 204 AS id, 1 AS role_id, 'rental-vehicle' AS permission, 'rental-vehicle.view' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 205 AS id, 1 AS role_id, 'rental-package' AS permission, 'rental-package' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 206 AS id, 1 AS role_id, 'rental-package' AS permission, 'rental-package.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 207 AS id, 1 AS role_id, 'rental-package' AS permission, 'rental-package.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 208 AS id, 1 AS role_id, 'rental-package' AS permission, 'rental-package.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 209 AS id, 1 AS role_id, 'make' AS permission, 'make' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 210 AS id, 1 AS role_id, 'make' AS permission, 'make.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 211 AS id, 1 AS role_id, 'make' AS permission, 'make.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 212 AS id, 1 AS role_id, 'make' AS permission, 'make.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 213 AS id, 1 AS role_id, 'model' AS permission, 'model' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 214 AS id, 1 AS role_id, 'model' AS permission, 'model.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 215 AS id, 1 AS role_id, 'model' AS permission, 'model.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 216 AS id, 1 AS role_id, 'model' AS permission, 'model.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 217 AS id, 1 AS role_id, 'general-notifications' AS permission, 'notification' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 218 AS id, 1 AS role_id, 'general-notifications' AS permission, 'notification.send' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 219 AS id, 1 AS role_id, 'general-notifications' AS permission, 'notification.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 220 AS id, 1 AS role_id, 'dynamic-notifications' AS permission, 'dynamic-notification.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 221 AS id, 1 AS role_id, 'dynamic-notifications' AS permission, 'dynamic-notification.save' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 222 AS id, 1 AS role_id, 'email-template' AS permission, 'email-templates.index' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 223 AS id, 1 AS role_id, 'email-template' AS permission, 'email-templates.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 224 AS id, 1 AS role_id, 'cms' AS permission, 'cms' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 225 AS id, 1 AS role_id, 'cms' AS permission, 'cms.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 226 AS id, 1 AS role_id, 'cms' AS permission, 'cms.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 227 AS id, 1 AS role_id, 'cms' AS permission, 'cms.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 228 AS id, 1 AS role_id, 'stores-payment' AS permission, 'stores.payment' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 229 AS id, 1 AS role_id, 'stores-payout' AS permission, 'stores.payout' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 230 AS id, 1 AS role_id, 'payout-request-vendor' AS permission, 'payout-request.vendor' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 231 AS id, 1 AS role_id, 'drivers-payment' AS permission, 'drivers.payment' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 232 AS id, 1 AS role_id, 'drivers-payout' AS permission, 'drivers.payout' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 233 AS id, 1 AS role_id, 'payout-request-driver' AS permission, 'payout-request.driver' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 234 AS id, 1 AS role_id, 'provider-payment' AS permission, 'provider.payment' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 235 AS id, 1 AS role_id, 'provider-payout' AS permission, 'provider.payout' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 236 AS id, 1 AS role_id, 'payout-request-provider' AS permission, 'payout-request.provider' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 237 AS id, 1 AS role_id, 'wallet-transaction' AS permission, 'wallet-transaction' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 238 AS id, 1 AS role_id, 'payout-request-owner' AS permission, 'payout-request.owner' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 239 AS id, 1 AS role_id, 'owners-wallet-transaction' AS permission, 'owner.wallet.list' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 240 AS id, 1 AS role_id, 'stores-payout' AS permission, 'stores.payout.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 241 AS id, 1 AS role_id, 'drivers-payout' AS permission, 'drivers.payout.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 242 AS id, 1 AS role_id, 'provider-payout' AS permission, 'provider.payout.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 243 AS id, 1 AS role_id, 'owners-payout' AS permission, 'owners.payout' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 244 AS id, 1 AS role_id, 'owners-payout' AS permission, 'owners.payout.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 245 AS id, 1 AS role_id, 'global-setting' AS permission, 'settings.app.globals' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 246 AS id, 1 AS role_id, 'business-model' AS permission, 'business-model' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 247 AS id, 1 AS role_id, 'app-banners-setting' AS permission, 'settings.app.banners' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 248 AS id, 1 AS role_id, 'currency' AS permission, 'currencies' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 249 AS id, 1 AS role_id, 'currency' AS permission, 'currencies.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 250 AS id, 1 AS role_id, 'currency' AS permission, 'currencies.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 251 AS id, 1 AS role_id, 'currency' AS permission, 'currency.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 252 AS id, 1 AS role_id, 'payment-method' AS permission, 'payment-method' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 253 AS id, 1 AS role_id, 'admin-commission' AS permission, 'settings.app.adminCommission' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 254 AS id, 1 AS role_id, 'radius' AS permission, 'settings.app.radiusConfiguration' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 255 AS id, 1 AS role_id, 'scheduleOrderNotification' AS permission, 'settings.app.scheduleOrderNotification' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 256 AS id, 1 AS role_id, 'tax' AS permission, 'tax' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 257 AS id, 1 AS role_id, 'tax' AS permission, 'tax.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 258 AS id, 1 AS role_id, 'tax' AS permission, 'tax.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 259 AS id, 1 AS role_id, 'tax' AS permission, 'tax.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 260 AS id, 1 AS role_id, 'delivery-charge' AS permission, 'settings.app.deliveryCharge' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 261 AS id, 1 AS role_id, 'document-verification' AS permission, 'settings.app.documentVerification' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 262 AS id, 1 AS role_id, 'language' AS permission, 'language' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 263 AS id, 1 AS role_id, 'language' AS permission, 'language.create' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 264 AS id, 1 AS role_id, 'language' AS permission, 'language.edit' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 265 AS id, 1 AS role_id, 'language' AS permission, 'language.delete' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 266 AS id, 1 AS role_id, 'special-offer' AS permission, 'setting.specialOffer' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 267 AS id, 1 AS role_id, 'settings-maintenance' AS permission, 'settings.app.maintenance' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 268 AS id, 1 AS role_id, 'terms' AS permission, 'termsAndConditions' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 269 AS id, 1 AS role_id, 'privacy' AS permission, 'privacyPolicy' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 270 AS id, 1 AS role_id, 'home-page' AS permission, 'homepageTemplate' AS routes, NOW() AS created_at, NOW() AS updated_at UNION ALL
  SELECT 271 AS id, 1 AS role_id, 'footer' AS permission, 'footerTemplate' AS routes, NOW() AS created_at, NOW() AS updated_at
) AS seed_perms
WHERE (SELECT COUNT(*) FROM `permissions` WHERE `role_id` = 1) = 0;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Seed data summary
-- =============================================================================
-- role          : Super Admin (id=1)
-- users         : admin@emart.com / 12345678
-- settings      : 14 core keys (globalSettings, languages, Version, terms, etc.)
-- currencies    : PHP (kweek-php-peso)
-- services      : 6 service types
-- sections      : Shop (6285dd3281531) for vendor registration
-- permissions   : 271 Super Admin RBAC rows (only if none exist for role_id=1)
--
-- Not included (add via admin panel or optional seeders):
--   zones, car_makes, vehicle_types, on_boarding, dynamic_notifications,
--   legacy Firebase data (php artisan db:seed --class=LegacyCollectionSeeder)
-- =============================================================================
