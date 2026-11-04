-- ============================================================
-- KANAK BEAUTY PARLOUR - Complete Database Schema
-- Database: kanak_beauty_parlour
-- Author: Kanak Beauty Parlour Dev Team
-- Version: 1.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS `kanak_beauty_parlour`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `kanak_beauty_parlour`;

-- ============================================================
-- TABLE: admins
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
  `full_name`  VARCHAR(100) NOT NULL DEFAULT 'Admin',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login` DATETIME     NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: username=admin, password=Admin@1234
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`) VALUES
('admin', 'admin@kanakbeauty.com', '$2y$12$UNzDuMkHW5cN5P/2DVHjaeAGb3UPNdBCa0BSXxVAeIxTxAX7HA7Iy', 'Kavita Kawadker');

-- ============================================================
-- TABLE: customers
-- ============================================================
-- The legacy seed account is disabled; create a private first admin with
-- admin/create_admin.php after importing this schema.
UPDATE `admins` SET `is_active` = 0
WHERE `username` = 'admin' AND `email` = 'admin@kanakbeauty.com';

CREATE TABLE IF NOT EXISTS `customers` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `mobile`     VARCHAR(15)  NOT NULL,
  `email`      VARCHAR(100) NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customers_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: services
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150)  NOT NULL,
  `slug`        VARCHAR(160)  NOT NULL UNIQUE,
  `description` TEXT          NOT NULL,
  `price`       DECIMAL(10,2) NULL     COMMENT 'NULL = price on request',
  `duration`    VARCHAR(50)   NULL     COMMENT 'e.g. 2-3 hours',
  `image`       VARCHAR(255)  NULL,
  `category`    VARCHAR(80)   NOT NULL DEFAULT 'General',
  `is_active`   TINYINT(1)    NOT NULL DEFAULT 1,
  `sort_order`  INT           NOT NULL DEFAULT 0,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_category` (`category`),
  KEY `idx_services_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`name`, `slug`, `description`, `price`, `duration`, `category`, `sort_order`) VALUES
('Bridal Makeup',     'bridal-makeup',     'Transform into the most beautiful bride on your special day. Complete bridal makeup with premium products, long-lasting finish, and traditional or contemporary looks as per your choice.',  NULL, '3-4 hours', 'Bridal',    1),
('Party Makeup',      'party-makeup',      'Look stunning at every party and event with our professional party makeup service. Glamorous looks tailored to your outfit and occasion.',                                                   NULL, '1-2 hours', 'Makeup',    2),
('Engagement Makeup', 'engagement-makeup', 'Shine at your engagement ceremony with a flawless makeup look. We create beautiful, picture-perfect looks for your memorable occasion.',                                                    NULL, '2-3 hours', 'Bridal',    3),
('Reception Makeup',  'reception-makeup',  'Look radiant at your reception with a glamorous makeup look designed to last the entire evening and photograph beautifully.',                                                               NULL, '2-3 hours', 'Bridal',    4),
('HD Makeup',         'hd-makeup',         'High-definition makeup using premium HD products for a flawless, camera-ready finish. Perfect for photography sessions, videos, and special events.',                                       NULL, '1-2 hours', 'Makeup',    5),
('Basic Makeup',      'basic-makeup',      'Fresh and natural everyday makeup for a polished, put-together look. Ideal for office, casual outings, and simple occasions.',                                                              NULL, '45-60 min', 'Makeup',    6),
('Hairstyling',       'hairstyling',       'Professional hairstyling for all occasions. From traditional hairstyles to modern updos, curls, braids, and more — we create stunning looks.',                                             NULL, '1-2 hours', 'Hair',      7),
('Saree Draping',     'saree-draping',     'Expert saree draping in various styles — Bengali, Gujarati, Maharashtrian, Nivi, and more. Look elegant and perfectly dressed for every occasion.',                                        NULL, '30-45 min', 'Styling',   8),
('Facial & Skincare', 'facial-skincare',   'Rejuvenate your skin with our premium facial and skincare treatments. We use quality products to give you a glowing, refreshed complexion.',                                               NULL, '45-90 min', 'Skincare',  9),
('Hair Care',         'hair-care',         'Complete hair care services including hair spa, oil massage, conditioning treatments, and more for healthy, lustrous hair.',                                                                NULL, '1-2 hours', 'Hair',     10),
('Beauty Treatments', 'beauty-treatments', 'Comprehensive beauty treatments including threading, waxing, bleach, cleanup, and more for complete beauty care and grooming.',                                                             NULL, '30-60 min', 'Beauty',   11);

-- ============================================================
-- TABLE: appointments
-- ============================================================
CREATE TABLE IF NOT EXISTS `appointments` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id`   VARCHAR(20)  NOT NULL UNIQUE COMMENT 'e.g. KBP20241104001',
  `customer_id`  INT UNSIGNED NOT NULL,
  `service_id`   INT UNSIGNED NOT NULL,
  `appt_date`    DATE         NOT NULL,
  `appt_time`    TIME         NOT NULL,
  `instructions` TEXT         NULL,
  `status`       ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `admin_notes`  TEXT         NULL,
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_booking_id` (`booking_id`),
  KEY `idx_appt_customer` (`customer_id`),
  KEY `idx_appt_service`  (`service_id`),
  KEY `idx_appt_date`     (`appt_date`),
  KEY `idx_appt_status`   (`status`),
  CONSTRAINT `fk_appt_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_appt_service`  FOREIGN KEY (`service_id`)  REFERENCES `services`  (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `appointment_id`  INT UNSIGNED  NOT NULL,
  `amount`          DECIMAL(10,2) NOT NULL,
  `method`          ENUM('cash','upi','razorpay') NOT NULL DEFAULT 'cash',
  `status`          ENUM('pending','successful','failed','refunded') NOT NULL DEFAULT 'pending',
  `razorpay_order_id`   VARCHAR(100) NULL,
  `razorpay_payment_id` VARCHAR(100) NULL,
  `razorpay_signature`  VARCHAR(255) NULL,
  `transaction_id`  VARCHAR(100) NULL,
  `notes`           TEXT         NULL,
  `paid_at`         DATETIME     NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_appt`   (`appointment_id`),
  KEY `idx_payment_status` (`status`),
  CONSTRAINT `fk_payment_appt` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: gallery
-- ============================================================
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(150) NULL,
  `image`      VARCHAR(255) NOT NULL,
  `category`   VARCHAR(80)  NOT NULL DEFAULT 'General',
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gallery_category` (`category`),
  KEY `idx_gallery_active`   (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: reviews
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(100) NOT NULL,
  `service`     VARCHAR(150) NULL,
  `rating`      TINYINT      NOT NULL DEFAULT 5 CHECK (`rating` BETWEEN 1 AND 5),
  `review_text` TEXT         NOT NULL,
  `avatar`      VARCHAR(255) NULL,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: business_settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `business_settings` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `setting_key`   VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT         NULL,
  `setting_label` VARCHAR(200) NULL,
  `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `business_settings` (`setting_key`, `setting_value`, `setting_label`) VALUES
('business_name',    'Kanak Beauty Parlour',                             'Business Name'),
('tagline',          'Beauty That Makes You Shine.',                     'Tagline'),
('owner_name',       'Kavita Kawadker',                                  'Owner Name'),
('phone',            '8839131870',                                       'Phone Number'),
('whatsapp',         '918839131870',                                     'WhatsApp Number'),
('email',            'info@kanakbeauty.com',                             'Email Address'),
('address',          'Chandrashekhar Ward, Sadar, Betul, Madhya Pradesh, Near Sai Mandir', 'Address'),
('city',             'Betul',                                            'City'),
('state',            'Madhya Pradesh',                                   'State'),
('pincode',          '460001',                                           'PIN Code'),
('business_hours',   '10:00 AM – 8:00 PM',                              'Business Hours'),
('instagram',        'https://www.instagram.com/kavita_kawadker_makeup_artist', 'Instagram URL'),
('google_maps_embed','https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3697.123456789!2d77.9!3d21.9!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjHCsDU0JzAwLjAiTiA3N8KwNTQnMDAuMCJF!5e0!3m2!1sen!2sin!4v1234567890', 'Google Maps Embed URL'),
('razorpay_key_id',  '',                                                 'Razorpay Key ID'),
('meta_description', 'Kanak Beauty Parlour – Best bridal makeup artist & beauty salon in Betul, Madhya Pradesh. Book appointments for bridal makeup, party makeup, hairstyling & more.', 'Meta Description'),
('currency',         'INR',                                              'Currency');

-- ============================================================
-- TABLE: contact_messages
-- ============================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `mobile`     VARCHAR(15)  NOT NULL,
  `email`      VARCHAR(100) NULL,
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Done
-- ============================================================
