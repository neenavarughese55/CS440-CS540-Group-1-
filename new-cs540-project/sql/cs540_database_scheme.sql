-- cs540-reordered-datetime.sql
-- Reordered and adjusted so import works in phpMyAdmin/MariaDB
-- Modified: use DATETIME for start_time, end_time, created_at, updated_at

CREATE DATABASE IF NOT EXISTS cs540 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cs540;

-- start: disable FK checks while building
SET @OLD_FK = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------
-- Drop any existing tables (safe to run to start clean)
-- ----------------------------------------------------
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS appointment_slots;
DROP TABLE IF EXISTS provider_profiles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

-- ----------------------------------------------------
-- 1) categories (no external refs)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 2) users (referenced by others)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` text NOT NULL,
  `username` varchar(200) DEFAULT NULL,
  `role` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 3) appointment_slots (referenced by appointments)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointment_slots` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `provider_id` bigint(20) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `slot_range` varchar(100) GENERATED ALWAYS AS (concat('[',`start_time`,', ',`end_time`,')')) STORED,
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_provider_slot_time` (`provider_id`,`start_time`,`end_time`),
  KEY `idx_slots_provider_range` (`provider_id`,`slot_range`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 4) provider_profiles (depends on users & categories)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `provider_profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `business_name` varchar(200) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` text DEFAULT NULL,
  `timezone` varchar(64) DEFAULT 'UTC',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_provider_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 5) appointments (references appointment_slots, users, categories)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `slot_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `provider_id` bigint(20) NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'booked',
  `notes` text DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `appt_range` varchar(100) GENERATED ALWAYS AS (concat('[',`start_time`,', ',`end_time`,')')) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_appointments_slot` (`slot_id`),
  KEY `idx_appointments_user_created` (`user_id`,`created_at`),
  KEY `idx_appointments_provider_start` (`provider_id`,`start_time`),
  KEY `idx_appointments_category_start` (`category_id`,`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 6) notifications (references users and appointments)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `appointment_id` bigint(20) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_notif_user_unsent` (`user_id`,`sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------
-- 7) add foreign key constraints (all referenced tables exist now)
-- ----------------------------------------------------
ALTER TABLE `provider_profiles`
  ADD CONSTRAINT `provider_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_profiles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `appointment_slots`
  ADD CONSTRAINT `appointment_slots_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`slot_id`) REFERENCES `appointment_slots` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

-- ----------------------------------------------------
-- 8) seed data
-- ----------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Medical', 'Medical services'),
(2, 'Beauty', 'Beauty & salon services'),
(3, 'Fitness', 'Fitness trainers, classes and sessions')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ----------------------------------------------------
-- 9) triggers (phpMyAdmin supports DELIMITER blocks)
-- ----------------------------------------------------
DELIMITER $$
CREATE TRIGGER `prevent_provider_overlap` BEFORE INSERT ON `appointments` FOR EACH ROW
BEGIN
  IF EXISTS (
    SELECT 1 FROM appointments
    WHERE provider_id = NEW.provider_id
      AND NEW.start_time < end_time
      AND NEW.end_time > start_time
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Provider already has an overlapping appointment';
  END IF;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `prevent_user_overlap` BEFORE INSERT ON `appointments` FOR EACH ROW
BEGIN
  IF EXISTS (
    SELECT 1 FROM appointments
    WHERE user_id = NEW.user_id
      AND NEW.start_time < end_time
      AND NEW.end_time > start_time
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User already has an overlapping appointment';
  END IF;
END
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `prevent_overlap` BEFORE INSERT ON `appointment_slots` FOR EACH ROW
BEGIN
  IF EXISTS (
    SELECT 1 FROM appointment_slots
    WHERE provider_id = NEW.provider_id
      AND NEW.start_time < end_time
      AND NEW.end_time > start_time
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Overlapping appointment slot for provider';
  END IF;
END
$$
DELIMITER ;

-- ----------------------------------------------------
-- 10) restore fk checks
-- ----------------------------------------------------
SET FOREIGN_KEY_CHECKS = @OLD_FK;
