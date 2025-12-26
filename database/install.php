<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JaKòb - Database Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #211111;
            color: #fff;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #2f1a1b;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        h1 { color: #ea2a33; margin-bottom: 20px; }
        .success { background: #10b981; padding: 15px; border-radius: 10px; margin: 10px 0; }
        .error { background: #ef4444; padding: 15px; border-radius: 10px; margin: 10px 0; }
        .info { background: #3b82f6; padding: 15px; border-radius: 10px; margin: 10px 0; }
        pre { background: #000; padding: 15px; border-radius: 10px; overflow-x: auto; margin: 10px 0; }
        .btn {
            background: #ea2a33;
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn:hover { background: #c91b24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🇭🇹 JaKòb - Database Installation</h1>

        <?php
        // Load .env file
        function loadEnv($path) {
            if (!file_exists($path)) {
                return false;
            }
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    putenv(trim($key) . "=" . trim($value));
                }
            }
            return true;
        }

        $envPath = __DIR__ . '/../.env';
        if (!loadEnv($envPath)) {
            echo '<div class="error">❌ Error: .env file not found!</div>';
            echo '<div class="info">Please create a .env file from .env.example</div>';
            exit;
        }

        // Database configuration
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'jakob';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        echo '<div class="info">📋 Configuration:</div>';
        echo '<pre>';
        echo "Host: $host:$port\n";
        echo "Database: $dbname\n";
        echo "User: $username\n";
        echo '</pre>';

        if (isset($_POST['install'])) {
            try {
                // Connect to MySQL (without database)
                $pdo = new PDO(
                    "mysql:host=$host;port=$port;charset=utf8mb4",
                    $username,
                    $password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                echo '<div class="success">✅ Connected to MySQL</div>';

                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo '<div class="success">✅ Database created: ' . $dbname . '</div>';

                // Use the database
                $pdo->exec("USE `$dbname`");

                // Read and execute schema.sql
                $schemaPath = __DIR__ . '/schema.sql';
                if (!file_exists($schemaPath)) {
                    throw new Exception('schema.sql file not found!');
                }

                $schema = file_get_contents($schemaPath);

                // Split SQL into individual statements
                $statements = array_filter(
                    array_map('trim', explode(';', $schema)),
                    function($stmt) {
                        return !empty($stmt) &&
                               strpos($stmt, '--') !== 0 &&
                               $stmt !== '';
                    }
                );

                echo '<div class="info">📝 Executing ' . count($statements) . ' SQL statements...</div>';

                foreach ($statements as $statement) {
                    if (!empty(trim($statement))) {
                        $pdo->exec($statement);
                    }
                }

                echo '<div class="success">✅ Database schema installed successfully!</div>';
                echo '<div class="success">✅ Sample data inserted</div>';
                echo '<div class="info">';
                echo '<strong>Test Accounts:</strong><br>';
                echo '📧 Donor: marie@example.com | Password: password123<br>';
                echo '📧 Influencer: basquiat@example.com | Password: password123';
                echo '</div>';
                echo '<br><a href="../index.html" class="btn">🚀 Start Using JaKòb</a>';

            } catch (PDOException $e) {
                echo '<div class="error">❌ Database Error: ' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
            }
        } else {
            ?>
            <form method="POST">
                <div class="info">
                    <strong>⚠️ Warning:</strong> This will:
                    <ul style="margin-left: 20px; margin-top: 10px;">
                        <li>Create the database if it doesn't exist</li>
                        <li>Drop existing tables if they exist</li>
                        <li>Create all tables with fresh schema</li>
                        <li>Insert sample test data</li>
                    </ul>
                </div>
                <button type="submit" name="install" class="btn">
                    🔧 Install Database
                </button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>
