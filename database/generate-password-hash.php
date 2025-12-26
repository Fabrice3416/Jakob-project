<?php
/**
 * Password Hash Generator
 * Run this file to generate bcrypt hashes for test passwords
 */

// Password to hash
$password = 'password123';

// Generate bcrypt hash
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "===========================================\n";
echo "PASSWORD HASH GENERATOR\n";
echo "===========================================\n\n";

echo "Original password: {$password}\n\n";
echo "Bcrypt hash:\n{$hash}\n\n";

echo "===========================================\n";
echo "Verification test:\n";
echo "===========================================\n";

if (password_verify($password, $hash)) {
    echo "✓ Hash verification: SUCCESS\n";
} else {
    echo "✗ Hash verification: FAILED\n";
}

echo "\n";
echo "Copy this hash to use in schema.sql:\n";
echo "--------------------------------------------\n";
echo $hash;
echo "\n--------------------------------------------\n";
?>
