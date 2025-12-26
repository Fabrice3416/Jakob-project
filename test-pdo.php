<?php
/**
 * Test PDO MySQL Extension
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PDO MySQL Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { background: #10b981; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ef4444; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .info { background: #3b82f6; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Test PDO MySQL Extension</h1>

    <?php
    echo "<h2>1. PHP Version</h2>";
    echo "<div class='info'>PHP Version: " . PHP_VERSION . "</div>";

    echo "<h2>2. PDO Support</h2>";
    if (extension_loaded('PDO')) {
        echo "<div class='success'>✅ PDO extension is loaded</div>";
    } else {
        echo "<div class='error'>❌ PDO extension is NOT loaded</div>";
    }

    echo "<h2>3. PDO MySQL Driver</h2>";
    if (extension_loaded('pdo_mysql')) {
        echo "<div class='success'>✅ PDO MySQL driver is loaded</div>";
    } else {
        echo "<div class='error'>❌ PDO MySQL driver is NOT loaded</div>";
        echo "<div class='error'>Please enable extension=pdo_mysql in php.ini</div>";
    }

    echo "<h2>4. MySQLi Support</h2>";
    if (extension_loaded('mysqli')) {
        echo "<div class='success'>✅ MySQLi extension is loaded</div>";
    } else {
        echo "<div class='error'>❌ MySQLi extension is NOT loaded</div>";
    }

    echo "<h2>5. Available PDO Drivers</h2>";
    $drivers = PDO::getAvailableDrivers();
    echo "<div class='info'>Available drivers: " . implode(', ', $drivers) . "</div>";

    echo "<h2>6. Test Database Connection</h2>";
    try {
        require_once __DIR__ . '/config/database.php';
        $pdo = getDbConnection();
        echo "<div class='success'>✅ Database connection successful!</div>";

        // Test query
        $stmt = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
        $result = $stmt->fetch();
        echo "<div class='info'>Connected to database: <strong>" . $result['db'] . "</strong></div>";
        echo "<div class='info'>MySQL version: <strong>" . $result['version'] . "</strong></div>";

        // Count tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<div class='info'>Number of tables: <strong>" . count($tables) . "</strong></div>";
        echo "<pre>Tables:\n" . implode("\n", $tables) . "</pre>";

    } catch (Exception $e) {
        echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
    }

    echo "<h2>7. Loaded Extensions</h2>";
    $extensions = get_loaded_extensions();
    sort($extensions);
    echo "<pre>" . implode("\n", $extensions) . "</pre>";
    ?>

    <h2>✅ Next Steps</h2>
    <div class="info">
        <p>If all tests pass:</p>
        <ol>
            <li>The PDO MySQL extension is properly loaded</li>
            <li>Database connection is working</li>
            <li>You can now test user registration at: <a href="/pages/auth/signup.html" style="color: #60a5fa;">/pages/auth/signup.html</a></li>
        </ol>
    </div>

    <div class="error">
        <p><strong>⚠️ Important:</strong> If changes were made to php.ini, you MUST restart the PHP server:</p>
        <ol>
            <li>Press Ctrl+C in the terminal running PHP server</li>
            <li>Restart with: <code>php -S localhost:8000</code></li>
        </ol>
    </div>
</body>
</html>
