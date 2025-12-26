<?php
/**
 * JaKòb Registration API
 * Handles user registration for both donors and influencers
 */

// Start session for auto-login after registration
session_start();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

// Load database configuration
require_once __DIR__ . '/../config/database.php';

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $requiredFields = ['user_type', 'email', 'phone', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    // Validate user type
    if (!in_array($data['user_type'], ['donor', 'influencer'])) {
        throw new Exception('Invalid user type. Must be "donor" or "influencer"');
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Validate phone format (basic validation)
    $phone = trim($data['phone']);
    if (strlen($phone) < 8) {
        throw new Exception('Invalid phone number');
    }

    // Validate password strength
    $password = $data['password'];
    if (strlen($password) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }

    // Get database connection
    $pdo = getDbConnection();

    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$data['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Email already registered');
    }

    // Check if phone already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        throw new Exception('Phone number already registered');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Insert into users table
        $stmt = $pdo->prepare('
            INSERT INTO users (email, phone, password, user_type, is_verified, is_active)
            VALUES (?, ?, ?, ?, FALSE, TRUE)
        ');
        $stmt->execute([
            $data['email'],
            $phone,
            $hashedPassword,
            $data['user_type']
        ]);

        $userId = $pdo->lastInsertId();

        // Insert into type-specific table
        if ($data['user_type'] === 'donor') {
            // For donors, we'll need first_name and last_name
            // For now, we'll use placeholders since signup doesn't collect them yet
            $stmt = $pdo->prepare('
                INSERT INTO donors (user_id, first_name, last_name)
                VALUES (?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                'New', // Placeholder - should be updated in profile completion
                'Donor' // Placeholder - should be updated in profile completion
            ]);
        } else {
            // For influencers, generate a default username from email
            $username = strtolower(explode('@', $data['email'])[0]);

            // Check if username exists, add number if needed
            $baseUsername = $username;
            $counter = 1;
            $stmt = $pdo->prepare('SELECT id FROM influencers WHERE username = ?');

            while (true) {
                $stmt->execute([$username]);
                if (!$stmt->fetch()) {
                    break;
                }
                $username = $baseUsername . $counter;
                $counter++;
            }

            $stmt = $pdo->prepare('
                INSERT INTO influencers (user_id, display_name, username, category)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([
                $userId,
                'New Creator', // Placeholder - should be updated in profile completion
                $username,
                'art' // Default category - should be updated in profile completion
            ]);
        }

        // Commit transaction
        $pdo->commit();

        // Create session for auto-login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $data['user_type'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['phone'] = $phone;
        $_SESSION['logged_in'] = true;

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'user_id' => $userId,
                'user_type' => $data['user_type'],
                'email' => $data['email'],
                'auto_logged_in' => true
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
