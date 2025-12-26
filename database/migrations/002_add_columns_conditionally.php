<?php
/**
 * Migration: Add Columns and Indexes Conditionally
 * Date: 2025-12-25
 * Description: Adds missing columns and indexes with proper conditional checks
 */

// This function will be called by the migration runner
return function($pdo) {
    $changes = [];

    // Helper function to check if column exists
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM information_schema.COLUMNS
            WHERE table_schema = DATABASE()
            AND table_name = ?
            AND column_name = ?
        ");
        $stmt->execute([$table, $column]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    // Helper function to check if index exists
    function indexExists($pdo, $table, $index) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
            AND table_name = ?
            AND index_name = ?
        ");
        $stmt->execute([$table, $index]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    // ============================================
    // 1. Add missing columns to campaigns table
    // ============================================

    if (!columnExists($pdo, 'campaigns', 'image_url')) {
        $pdo->exec("ALTER TABLE `campaigns` ADD COLUMN `image_url` VARCHAR(191) NULL AFTER `description`");
        $changes[] = "Added column 'image_url' to campaigns table";
    }

    // ============================================
    // 2. Add missing columns to influencers table
    // ============================================

    if (!columnExists($pdo, 'influencers', 'verified')) {
        $pdo->exec("ALTER TABLE `influencers` ADD COLUMN `verified` BOOLEAN NOT NULL DEFAULT FALSE AFTER `total_followers`");
        $changes[] = "Added column 'verified' to influencers table";
    }

    if (!columnExists($pdo, 'influencers', 'total_raised')) {
        $pdo->exec("ALTER TABLE `influencers` ADD COLUMN `total_raised` DECIMAL(10, 2) DEFAULT 0.00 AFTER `total_followers`");
        $changes[] = "Added column 'total_raised' to influencers table";
    }

    if (!columnExists($pdo, 'influencers', 'total_campaigns')) {
        $pdo->exec("ALTER TABLE `influencers` ADD COLUMN `total_campaigns` INT UNSIGNED DEFAULT 0 AFTER `total_raised`");
        $changes[] = "Added column 'total_campaigns' to influencers table";
    }

    // ============================================
    // 3. Add missing columns to donors table
    // ============================================

    if (!columnExists($pdo, 'donors', 'donation_count')) {
        $pdo->exec("ALTER TABLE `donors` ADD COLUMN `donation_count` INT UNSIGNED DEFAULT 0 AFTER `total_donated`");
        $changes[] = "Added column 'donation_count' to donors table";
    }

    // ============================================
    // 4. Add performance indexes
    // ============================================

    // Campaigns table indexes
    if (!indexExists($pdo, 'campaigns', 'idx_influencer_status')) {
        $pdo->exec("ALTER TABLE `campaigns` ADD INDEX `idx_influencer_status` (`influencer_id`, `status`, `created_at`)");
        $changes[] = "Added index 'idx_influencer_status' to campaigns table";
    }

    if (!indexExists($pdo, 'campaigns', 'idx_status_category')) {
        $pdo->exec("ALTER TABLE `campaigns` ADD INDEX `idx_status_category` (`status`, `category`)");
        $changes[] = "Added index 'idx_status_category' to campaigns table";
    }

    if (!indexExists($pdo, 'campaigns', 'idx_end_date')) {
        $pdo->exec("ALTER TABLE `campaigns` ADD INDEX `idx_end_date` (`end_date`)");
        $changes[] = "Added index 'idx_end_date' to campaigns table";
    }

    // Donations table indexes
    if (!indexExists($pdo, 'donations', 'idx_donor_status')) {
        $pdo->exec("ALTER TABLE `donations` ADD INDEX `idx_donor_status` (`donor_id`, `status`)");
        $changes[] = "Added index 'idx_donor_status' to donations table";
    }

    if (!indexExists($pdo, 'donations', 'idx_influencer_status')) {
        $pdo->exec("ALTER TABLE `donations` ADD INDEX `idx_influencer_status` (`influencer_id`, `status`)");
        $changes[] = "Added index 'idx_influencer_status' to donations table";
    }

    if (!indexExists($pdo, 'donations', 'idx_campaign_status')) {
        $pdo->exec("ALTER TABLE `donations` ADD INDEX `idx_campaign_status` (`campaign_id`, `status`)");
        $changes[] = "Added index 'idx_campaign_status' to donations table";
    }

    // Notifications table indexes
    if (!indexExists($pdo, 'notifications', 'idx_user_read')) {
        $pdo->exec("ALTER TABLE `notifications` ADD INDEX `idx_user_read` (`user_id`, `is_read`, `created_at`)");
        $changes[] = "Added index 'idx_user_read' to notifications table";
    }

    // Transactions table indexes
    if (!indexExists($pdo, 'transactions', 'idx_user_created')) {
        $pdo->exec("ALTER TABLE `transactions` ADD INDEX `idx_user_created` (`user_id`, `created_at`)");
        $changes[] = "Added index 'idx_user_created' to transactions table";
    }

    if (!indexExists($pdo, 'transactions', 'idx_status_type')) {
        $pdo->exec("ALTER TABLE `transactions` ADD INDEX `idx_status_type` (`status`, `type`)");
        $changes[] = "Added index 'idx_status_type' to transactions table";
    }

    // Users table indexes
    if (!indexExists($pdo, 'users', 'idx_email_verified')) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_email_verified` (`email`, `is_verified`)");
        $changes[] = "Added index 'idx_email_verified' to users table";
    }

    if (!indexExists($pdo, 'users', 'idx_phone')) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_phone` (`phone`)");
        $changes[] = "Added index 'idx_phone' to users table";
    }

    // ============================================
    // 5. Update existing data
    // ============================================

    // Update verified column for existing influencers
    if (columnExists($pdo, 'influencers', 'verified')) {
        $pdo->exec("UPDATE `influencers` SET `verified` = FALSE WHERE `verified` IS NULL");
        $changes[] = "Updated NULL verified values to FALSE in influencers table";
    }

    // Update donation counts for existing donors
    if (columnExists($pdo, 'donors', 'donation_count')) {
        $pdo->exec("
            UPDATE `donors` d
            SET `donation_count` = (
                SELECT COUNT(*)
                FROM `donations`
                WHERE `donor_id` = d.`id` AND `status` = 'completed'
            )
            WHERE `donation_count` = 0
        ");
        $changes[] = "Updated donation counts for existing donors";
    }

    // Update total_raised for existing influencers
    if (columnExists($pdo, 'influencers', 'total_raised')) {
        $pdo->exec("
            UPDATE `influencers` i
            SET `total_raised` = COALESCE((
                SELECT SUM(amount)
                FROM `donations`
                WHERE `influencer_id` = i.`id` AND `status` = 'completed'
            ), 0.00)
            WHERE `total_raised` = 0.00
        ");
        $changes[] = "Updated total_raised for existing influencers";
    }

    // Update total_campaigns for existing influencers
    if (columnExists($pdo, 'influencers', 'total_campaigns')) {
        $pdo->exec("
            UPDATE `influencers` i
            SET `total_campaigns` = (
                SELECT COUNT(*)
                FROM `campaigns`
                WHERE `influencer_id` = i.`id`
            )
            WHERE `total_campaigns` = 0
        ");
        $changes[] = "Updated total_campaigns for existing influencers";
    }

    return [
        'success' => true,
        'changes' => $changes,
        'count' => count($changes)
    ];
};
