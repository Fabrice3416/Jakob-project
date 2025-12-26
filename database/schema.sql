-- JaKòb Database Schema
-- MySQL Database with support for 2 user types: Donors and Influencers

-- Drop tables if exist (for fresh install)
DROP TABLE IF EXISTS `donations`;
DROP TABLE IF EXISTS `campaigns`;
DROP TABLE IF EXISTS `followers`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `payment_methods`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `influencers`;
DROP TABLE IF EXISTS `donors`;
DROP TABLE IF EXISTS `users`;

-- ============================================
-- USERS TABLE (Base for both types)
-- ============================================
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) UNIQUE NOT NULL,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `password` VARCHAR(191) NOT NULL,
    `user_type` ENUM('donor', 'influencer') NOT NULL,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `email_verified_at` TIMESTAMP NULL,
    `phone_verified_at` TIMESTAMP NULL,
    `last_login_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_type` (`user_type`),
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONORS TABLE (Specific info for donors)
-- ============================================
CREATE TABLE `donors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED UNIQUE NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `avatar_url` VARCHAR(191) NULL,
    `bio` TEXT NULL,
    `location` VARCHAR(191) NULL,
    `total_donated` DECIMAL(10, 2) DEFAULT 0.00,
    `donation_count` INT UNSIGNED DEFAULT 0,
    `favorite_categories` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INFLUENCERS TABLE (Specific info for creators)
-- ============================================
CREATE TABLE `influencers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED UNIQUE NOT NULL,
    `display_name` VARCHAR(150) NOT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `avatar_url` VARCHAR(191) NULL,
    `cover_image_url` VARCHAR(191) NULL,
    `bio` TEXT NULL,
    `category` ENUM('art', 'music', 'education', 'youth', 'heritage') NOT NULL,
    `location` VARCHAR(191) NULL,
    `verified` BOOLEAN DEFAULT FALSE,
    `total_raised` DECIMAL(10, 2) DEFAULT 0.00,
    `total_campaigns` INT UNSIGNED DEFAULT 0,
    `total_followers` INT UNSIGNED DEFAULT 0,
    `social_links` JSON NULL COMMENT '{"facebook": "", "instagram": "", "twitter": "", "youtube": ""}',
    `bank_account` VARCHAR(191) NULL,
    `moncash_number` VARCHAR(20) NULL,
    `natcash_number` VARCHAR(20) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_username` (`username`),
    INDEX `idx_category` (`category`),
    INDEX `idx_verified` (`verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CAMPAIGNS TABLE
-- ============================================
CREATE TABLE `campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `influencer_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(191) NOT NULL,
    `slug` VARCHAR(191) UNIQUE NOT NULL,
    `description` TEXT NOT NULL,
    `story` TEXT NULL,
    `goal_amount` DECIMAL(10, 2) NOT NULL,
    `raised_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'HTG',
    `status` ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    `category` ENUM('art', 'music', 'education', 'youth', 'heritage') NOT NULL,
    `image_url` VARCHAR(191) NULL,
    `video_url` VARCHAR(191) NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `backers_count` INT UNSIGNED DEFAULT 0,
    `views_count` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`category`),
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONATIONS TABLE
-- ============================================
CREATE TABLE `donations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `donor_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED NULL,
    `influencer_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'HTG',
    `payment_method` ENUM('moncash', 'natcash', 'card', 'bank') NOT NULL,
    `transaction_id` VARCHAR(191) UNIQUE NOT NULL,
    `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `message` TEXT NULL,
    `is_anonymous` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`donor_id`) REFERENCES `donors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `campaigns`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
    INDEX `idx_donor` (`donor_id`),
    INDEX `idx_campaign` (`campaign_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_transaction` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FOLLOWERS TABLE (Donors following Influencers)
-- ============================================
CREATE TABLE `followers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `donor_id` INT UNSIGNED NOT NULL,
    `influencer_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`donor_id`) REFERENCES `donors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`influencer_id`) REFERENCES `influencers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_follow` (`donor_id`, `influencer_id`),
    INDEX `idx_donor` (`donor_id`),
    INDEX `idx_influencer` (`influencer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENT METHODS TABLE
-- ============================================
CREATE TABLE `payment_methods` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('moncash', 'natcash', 'card', 'bank') NOT NULL,
    `provider` VARCHAR(50) NULL,
    `account_number` VARCHAR(191) NOT NULL,
    `account_name` VARCHAR(191) NULL,
    `is_default` BOOLEAN DEFAULT FALSE,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `balance` DECIMAL(10, 2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRANSACTIONS TABLE (Wallet transactions)
-- ============================================
CREATE TABLE `transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('donation', 'topup', 'withdrawal', 'refund') NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'HTG',
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `description` VARCHAR(191) NULL,
    `reference_id` VARCHAR(191) NULL COMMENT 'External transaction ID',
    `metadata` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`type`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('donation', 'campaign', 'follower', 'system') NOT NULL,
    `title` VARCHAR(191) NOT NULL,
    `message` TEXT NOT NULL,
    `icon` VARCHAR(50) NULL,
    `link` VARCHAR(191) NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA (Sample users for testing)
-- ============================================

-- Insert sample donor
INSERT INTO `users` (`email`, `phone`, `password`, `user_type`, `is_verified`) VALUES
('marie@example.com', '+50912345678', '$2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.', 'donor', TRUE);

SET @donor_user_id = LAST_INSERT_ID();

INSERT INTO `donors` (`user_id`, `first_name`, `last_name`, `bio`, `location`) VALUES
(@donor_user_id, 'Marie', 'Joseph', 'Passionate about supporting Haitian culture and local artists 🇭🇹', 'Port-au-Prince');

-- Insert sample influencer
INSERT INTO `users` (`email`, `phone`, `password`, `user_type`, `is_verified`) VALUES
('basquiat@example.com', '+50987654321', '$2y$12$gr36jBxylQF1beUFx2kJiunjOpOYGI1YMHMiU9.oWrRiQXDFjOvH.', 'influencer', TRUE);

SET @influencer_user_id = LAST_INSERT_ID();

INSERT INTO `influencers` (`user_id`, `display_name`, `username`, `bio`, `category`, `location`, `verified`) VALUES
(@influencer_user_id, 'Jean-Michel Basquiat', 'basquiat_art', 'Visual artist bringing Haitian culture to life through contemporary art', 'art', 'Port-au-Prince', TRUE);

-- Note: Password for both test users is 'password123' (hashed with bcrypt using PASSWORD_BCRYPT)
