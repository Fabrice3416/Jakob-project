<?php
// Test de connexion PostgreSQL
$host = '127.0.0.1';
$db   = 'jakob';
$user = 'phpuser';
$pass = 'simple123';
$port = "5433";

echo "Tentative de connexion à PostgreSQL...\n";
echo "Host: $host\n";
echo "Database: $db\n";
echo "User: $user\n";
echo "Port: $port\n\n";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✅ Connexion réussie!\n";

    // Test d'une requête simple
    $result = $pdo->query("SELECT version()");
    $version = $result->fetch();
    echo "Version PostgreSQL: " . $version['version'] . "\n";

} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\nSuggestions:\n";
    echo "1. Vérifiez que PostgreSQL est démarré\n";
    echo "2. Vérifiez le mot de passe dans db.php\n";
    echo "3. Vérifiez que la base de données 'jakob' existe\n";
    echo "4. Vérifiez le fichier pg_hba.conf de PostgreSQL\n";
}
?>
