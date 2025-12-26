<?php
/**
 * JaKòb Login API
 * Handles user authentication for both donors and influencers
 */

// Start session
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
    if (!isset($data['phone']) || empty(trim($data['phone']))) {
        throw new Exception('Phone number is required');
    }

    if (!isset($data['password']) || empty($data['password'])) {
        throw new Exception('Password is required');
    }

    $phone = trim($data['phone']);
    $password = $data['password'];
    $rememberMe = isset($data['remember_me']) && $data['remember_me'] === true;

    // Get database connection
    $pdo = getDbConnection();

    // Find user by phone
    $stmt = $pdo->prepare('
        SELECT id, email, phone, password, user_type, is_verified, is_active
        FROM users
        WHERE phone = ?
    ');
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('Invalid phone number or password');
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Invalid phone number or password');
    }

    // Check if user is active
    if (!$user['is_active']) {
        throw new Exception('Account is deactivated. Please contact support.');
    }

    // Get user-specific data
    $userData = null;

    if ($user['user_type'] === 'donor') {
        $stmt = $pdo->prepare('
            SELECT id, first_name, last_name, avatar_url, bio, location,
                   total_donated, donation_count
            FROM donors
            WHERE user_id = ?
        ');
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare('
            SELECT id, display_name, username, avatar_url, cover_image_url,
                   bio, category, location, verified, total_raised,
                   total_campaigns, total_followers, social_links
            FROM influencers
            WHERE user_id = ?
        ');
        $stmt->execute([$user['id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decode JSON fields
        if ($userData && $userData['social_links']) {
            $userData['social_links'] = json_decode($userData['social_links'], true);
        }
    }

    // Update last login timestamp
    $stmt = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
    $stmt->execute([$user['id']]);

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['is_verified'] = $user['is_verified'];

    if ($user['user_type'] === 'donor') {
        $_SESSION['donor_id'] = $userData['id'];
        $_SESSION['full_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
    } else {
        $_SESSION['influencer_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['display_name'] = $userData['display_name'];
    }

    // Set remember me cookie if requested (30 days)
    if ($rememberMe) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);

        // Store token in database (you'd need to create a remember_tokens table)
        // For now, we'll skip this implementation
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'user_id' => $user['id'],
            'user_type' => $user['user_type'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'is_verified' => $user['is_verified'],
            'profile' => $userData
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
