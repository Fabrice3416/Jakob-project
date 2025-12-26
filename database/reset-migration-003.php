<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();
$pdo->exec("DELETE FROM migrations WHERE migration = '003_add_remember_tokens.sql'");
echo "✅ Deleted migration 003 record. Now run migrations again.";
