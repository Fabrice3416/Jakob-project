<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JaKòb - Run Migrations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #211111 0%, #2f1a1b 100%);
            color: #fff;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(47, 26, 27, 0.8);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #ea2a33;
            margin-bottom: 1.5rem;
            font-size: 2rem;
        }

        .step {
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #ea2a33;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }

        .success {
            color: #4ade80;
            border-left-color: #4ade80;
        }

        .error {
            color: #f87171;
            border-left-color: #f87171;
        }

        .warning {
            color: #fbbf24;
            border-left-color: #fbbf24;
        }

        .info {
            color: #60a5fa;
            border-left-color: #60a5fa;
        }

        pre {
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .btn {
            background: #ea2a33;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            margin-top: 1rem;
        }

        .btn:hover {
            background: #c91b24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ JaKòb Database Migrations</h1>

        <?php
        require_once __DIR__ . '/../config/database.php';

        try {
            echo '<div class="step info">';
            echo '<strong>📡 Connecting to database...</strong>';
            echo '</div>';

            $pdo = getDbConnection();

            echo '<div class="step success">';
            echo '<strong>✅ Connected successfully</strong>';
            echo '</div>';

            // Get list of migration files (both .sql and .php)
            $migrationsDir = __DIR__ . '/migrations';
            $sqlFiles = glob($migrationsDir . '/*.sql');
            $phpFiles = glob($migrationsDir . '/*.php');
            $migrationFiles = array_merge($sqlFiles ?: [], $phpFiles ?: []);

            if (empty($migrationFiles)) {
                echo '<div class="step warning">';
                echo '<strong>⚠️ No migration files found</strong>';
                echo '</div>';
                exit;
            }

            sort($migrationFiles);

            echo '<div class="step info">';
            echo '<strong>📋 Found ' . count($migrationFiles) . ' migration file(s)</strong>';
            echo '</div>';

            // Create migrations tracking table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `migration` VARCHAR(191) NOT NULL UNIQUE,
                    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Get already executed migrations
            $stmt = $pdo->query("SELECT migration FROM migrations");
            $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $newMigrationsCount = 0;

            foreach ($migrationFiles as $file) {
                $migrationName = basename($file);

                if (in_array($migrationName, $executedMigrations)) {
                    echo '<div class="step">';
                    echo '<strong>⏭️ Skipping (already executed): ' . htmlspecialchars($migrationName) . '</strong>';
                    echo '</div>';
                    continue;
                }

                echo '<div class="step info">';
                echo '<strong>⚙️ Executing: ' . htmlspecialchars($migrationName) . '</strong>';

                try {
                    // Check if this is a PHP or SQL migration
                    $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

                    if ($fileExtension === 'php') {
                        // Execute PHP migration (no global transaction - DDL auto-commits)
                        $migrationFunction = require $file;

                        if (!is_callable($migrationFunction)) {
                            throw new Exception('Migration file must return a callable function');
                        }

                        $result = $migrationFunction($pdo);

                        if (isset($result['changes']) && is_array($result['changes'])) {
                            echo '<br>✅ Applied ' . count($result['changes']) . ' change(s):';
                            echo '<ul style="margin-top: 0.5rem; padding-left: 1.5rem;">';
                            foreach ($result['changes'] as $change) {
                                echo '<li>' . htmlspecialchars($change) . '</li>';
                            }
                            echo '</ul>';
                        }

                        $executedCount = $result['count'] ?? 1;

                        // Mark migration as executed
                        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                        $stmt->execute([$migrationName]);

                    } else {
                        // SQL migrations - execute without transaction wrapper
                        // (CREATE TABLE and other DDL statements auto-commit)
                        $sql = file_get_contents($file);

                        // Split by semicolons to execute multiple statements
                        $statements = array_filter(
                            array_map('trim', explode(';', $sql)),
                            function($stmt) {
                                // Filter out comments and empty statements
                                return !empty($stmt) &&
                                       !preg_match('/^\s*--/', $stmt) &&
                                       !preg_match('/^\s*\/\*/', $stmt);
                            }
                        );

                        $executedCount = 0;
                        foreach ($statements as $statement) {
                            if (trim($statement)) {
                                $pdo->exec($statement);
                                $executedCount++;
                            }
                        }

                        echo '<br>✅ Executed ' . $executedCount . ' SQL statement(s)';

                        // Mark migration as executed (separate transaction for INSERT)
                        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
                        $stmt->execute([$migrationName]);
                    }

                    echo '</div>';

                    $newMigrationsCount++;

                } catch (Exception $e) {
                    // Only rollback if transaction is still active
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    echo '<br><span class="error">❌ Failed: ' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                    echo '</div>';

                    throw $e;
                }
            }

            if ($newMigrationsCount > 0) {
                echo '<div class="step success">';
                echo '<strong>🎉 Migration completed successfully!</strong>';
                echo '<br>Executed ' . $newMigrationsCount . ' new migration(s)';
                echo '</div>';
            } else {
                echo '<div class="step success">';
                echo '<strong>✅ Database is up to date</strong>';
                echo '<br>No new migrations to run';
                echo '</div>';
            }

            // Show current database status
            echo '<div class="step info">';
            echo '<strong>📊 Database Status</strong>';

            $tables = [
                'users', 'donors', 'influencers', 'campaigns', 'donations',
                'followers', 'payment_methods', 'transactions', 'notifications',
                'remember_tokens', 'password_resets'
            ];

            echo '<pre>';
            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo str_pad($table, 20) . ': ' . $count . ' record(s)' . PHP_EOL;
            }
            echo '</pre>';
            echo '</div>';

        } catch (Exception $e) {
            echo '<div class="step error">';
            echo '<strong>❌ Migration Failed</strong>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>

        <a href="install.php" class="btn">← Back to Database Installer</a>
    </div>
</body>
</html>
