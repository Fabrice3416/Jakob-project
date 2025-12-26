<?php
/**
 * Debug script pour identifier les problèmes de l'API register.php
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>JaKòb - Diagnostic API</h1>";
echo "<hr>";

// Test 1: Vérifier .env
echo "<h2>1. Test .env File</h2>";
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo "✅ .env file exists<br>";
    $content = file_get_contents($envPath);
    echo "<pre>" . htmlspecialchars($content) . "</pre>";
} else {
    echo "❌ .env file NOT found at: $envPath<br>";
}
echo "<hr>";

// Test 2: Charger l'environnement
echo "<h2>2. Test Environment Variables</h2>";
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

if (loadEnv($envPath)) {
    echo "✅ Environment loaded<br>";
    echo "DB_HOST: " . getenv('DB_HOST') . "<br>";
    echo "DB_PORT: " . getenv('DB_PORT') . "<br>";
    echo "DB_NAME: " . getenv('DB_NAME') . "<br>";
    echo "DB_USER: " . getenv('DB_USER') . "<br>";
    echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? '***hidden***' : '(empty)') . "<br>";
} else {
    echo "❌ Failed to load environment<br>";
}
echo "<hr>";

// Test 3: Connexion à MySQL (sans base de données)
echo "<h2>3. Test MySQL Connection (no database)</h2>";
try {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT'),
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ MySQL connection successful<br>";

    // Lister les bases de données
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<strong>Available databases:</strong><br>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li>" . htmlspecialchars($db);
        if ($db === getenv('DB_NAME')) {
            echo " ✅ <strong>(configured in .env)</strong>";
        }
        echo "</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 4: Connexion à la base de données spécifique
echo "<h2>4. Test Database Connection</h2>";
try {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4",
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database '" . getenv('DB_NAME') . "' connection successful<br>";

    // Lister les tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<strong>Tables in database:</strong><br>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";

    // Compter les utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch();
    echo "<strong>Users count:</strong> " . $count['count'] . "<br>";

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 5: Tester l'API avec des données de test
echo "<h2>5. Test API with Sample Data</h2>";
$testData = [
    'user_type' => 'donor',
    'email' => 'test' . time() . '@example.com',
    'phone' => '+5091234' . rand(1000, 9999),
    'password' => 'password123'
];

echo "<strong>Test data:</strong><br>";
echo "<pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre>";

// Simuler l'appel API
try {
    require_once __DIR__ . '/../config/database.php';

    $pdo = getDbConnection();
    echo "✅ Database connection via config/database.php successful<br>";

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$testData['email']]);
    if ($stmt->fetch()) {
        echo "⚠️ Email already exists (this is expected if test was run before)<br>";
    } else {
        echo "✅ Email is available<br>";
    }

    // Tester le hachage du mot de passe
    $hashedPassword = password_hash($testData['password'], PASSWORD_BCRYPT);
    echo "✅ Password hashed successfully<br>";
    echo "Hash: " . substr($hashedPassword, 0, 30) . "...<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Test 6: Vérifier les headers HTTP
echo "<h2>6. Test HTTP Headers</h2>";
echo "<strong>Content-Type header:</strong><br>";
header('Content-Type: application/json; charset=utf-8');
$headers = headers_list();
echo "<ul>";
foreach ($headers as $header) {
    echo "<li>" . htmlspecialchars($header) . "</li>";
}
echo "</ul>";
echo "<hr>";

// Test 7: Tester la sortie JSON
echo "<h2>7. Test JSON Output</h2>";
$jsonTest = [
    'success' => true,
    'message' => 'This is a test JSON response',
    'data' => [
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION
    ]
];
echo "<strong>JSON test:</strong><br>";
echo "<pre>" . json_encode($jsonTest, JSON_PRETTY_PRINT) . "</pre>";
echo "<hr>";

echo "<h2>Diagnostic Complete</h2>";
echo "<p>Si tous les tests passent, le problème peut être:</p>";
echo "<ul>";
echo "<li>1. Un problème de BOM (Byte Order Mark) dans les fichiers PHP</li>";
echo "<li>2. Des espaces ou du texte avant &lt;?php</li>";
echo "<li>3. Un problème de buffering de sortie</li>";
echo "<li>4. Des erreurs PHP qui s'affichent avant le JSON</li>";
echo "<li>5. Un problème dans le code JavaScript de signup.html</li>";
echo "</ul>";
?>
