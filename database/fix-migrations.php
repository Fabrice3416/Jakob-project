<?php
/**
 * Fix Migrations - Clean up partial migration and prepare for fresh run
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Migrations - JaKòb</title>
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
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #c91b24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Migration State</h1>

        <?php
        try {
            $pdo = getDbConnection();

            echo '<div class="step info">';
            echo '<strong>📋 Current migration state:</strong><br>';
            $stmt = $pdo->query("SELECT * FROM migrations ORDER BY executed_at");
            $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($migrations as $migration) {
                echo "  - {$migration['migration']} (executed at {$migration['executed_at']})<br>";
            }
            echo '</div>';

            // Remove the partial migration record
            echo '<div class="step info">';
            echo '<strong>🗑️ Removing partial migration record...</strong><br>';
            $pdo->exec("DELETE FROM migrations WHERE migration = '001_add_missing_fields_and_indexes.sql'");
            echo '✅ Removed 001_add_missing_fields_and_indexes.sql from migrations table';
            echo '</div>';

            echo '<div class="step success">';
            echo '<strong>✅ Migration state cleaned!</strong><br>';
            echo 'You can now run the migrations again. The system will:';
            echo '<ol style="margin-top: 0.5rem; margin-left: 1.5rem;">';
            echo '<li>Create the remember_tokens table (missing)</li>';
            echo '<li>Add all missing performance indexes</li>';
            echo '<li>Skip columns that already exist</li>';
            echo '</ol>';
            echo '</div>';

            echo '<a href="run-migrations.php" class="btn">▶️ Run Migrations Now</a> ';
            echo '<a href="check-schema.php" class="btn" style="background: #4b5563;">📊 Check Schema</a>';

        } catch (Exception $e) {
            echo '<div class="step error">';
            echo '<strong>❌ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
