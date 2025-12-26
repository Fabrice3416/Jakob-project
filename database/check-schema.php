<?php
/**
 * Schema Checker - Diagnose current database state
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = getDbConnection();

    echo "=== JaKòb Database Schema Check ===\n\n";

    // Check migrations table
    echo "📋 MIGRATIONS TABLE\n";
    echo str_repeat("-", 50) . "\n";
    try {
        $stmt = $pdo->query("SELECT * FROM migrations ORDER BY executed_at");
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($migrations)) {
            echo "No migrations recorded yet\n";
        } else {
            foreach ($migrations as $migration) {
                echo "✓ {$migration['migration']} - {$migration['executed_at']}\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Migrations table doesn't exist\n";
    }

    echo "\n";

    // Check columns in campaigns table
    echo "📊 CAMPAIGNS TABLE COLUMNS\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE campaigns");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }

    $hasImageUrl = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'image_url') {
            $hasImageUrl = true;
            break;
        }
    }
    echo $hasImageUrl ? "✓ image_url exists\n" : "❌ image_url missing\n";

    echo "\n";

    // Check columns in influencers table
    echo "📊 INFLUENCERS TABLE COLUMNS\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE influencers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }

    $checks = ['verified', 'total_raised', 'total_campaigns'];
    foreach ($checks as $check) {
        $exists = false;
        foreach ($columns as $col) {
            if ($col['Field'] === $check) {
                $exists = true;
                break;
            }
        }
        echo ($exists ? "✓" : "❌") . " $check " . ($exists ? "exists" : "missing") . "\n";
    }

    echo "\n";

    // Check columns in donors table
    echo "📊 DONORS TABLE COLUMNS\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("DESCRIBE donors");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "  {$col['Field']} - {$col['Type']}\n";
    }

    $hasDonationCount = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'donation_count') {
            $hasDonationCount = true;
            break;
        }
    }
    echo $hasDonationCount ? "✓ donation_count exists\n" : "❌ donation_count missing\n";

    echo "\n";

    // Check if new tables exist
    echo "📋 NEW TABLES\n";
    echo str_repeat("-", 50) . "\n";

    $newTables = ['remember_tokens', 'password_resets', 'user_sessions'];
    foreach ($newTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "✓ $table exists ($count records)\n";
        } catch (Exception $e) {
            echo "❌ $table missing\n";
        }
    }

    echo "\n";

    // Check indexes
    echo "📇 INDEXES\n";
    echo str_repeat("-", 50) . "\n";

    $indexesToCheck = [
        'campaigns' => ['idx_influencer_status', 'idx_status_category', 'idx_end_date'],
        'donations' => ['idx_donor_status', 'idx_influencer_status', 'idx_campaign_status'],
        'notifications' => ['idx_user_read'],
        'transactions' => ['idx_user_created', 'idx_status_type'],
        'users' => ['idx_email_verified', 'idx_phone']
    ];

    foreach ($indexesToCheck as $table => $indexes) {
        echo "\n$table:\n";
        $stmt = $pdo->query("SHOW INDEX FROM $table");
        $existingIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $indexNames = array_unique(array_column($existingIndexes, 'Key_name'));

        foreach ($indexes as $index) {
            $exists = in_array($index, $indexNames);
            echo "  " . ($exists ? "✓" : "❌") . " $index\n";
        }
    }

    echo "\n";
    echo "=== Check Complete ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
