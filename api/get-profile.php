<?php
/**
 * Get User Profile API
 * Returns complete profile data for the authenticated user
 */

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        throw new Exception('Not authenticated');
    }

    $userId = $_SESSION['user_id'];
    $userType = $_SESSION['user_type'];

    $pdo = getDbConnection();

    // Get base user data
    $stmt = $pdo->prepare('
        SELECT id, email, phone, user_type, is_verified, is_active,
               email_verified_at, phone_verified_at, created_at
        FROM users
        WHERE id = ?
    ');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Get type-specific data
    $profileData = null;

    if ($userType === 'donor') {
        $stmt = $pdo->prepare('
            SELECT id, first_name, last_name, avatar_url, bio, location,
                   total_donated, donation_count, favorite_categories
            FROM donors
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decode JSON fields
        if ($profileData && $profileData['favorite_categories']) {
            $profileData['favorite_categories'] = json_decode($profileData['favorite_categories'], true);
        }

    } else {
        $stmt = $pdo->prepare('
            SELECT id, display_name, username, avatar_url, cover_image_url,
                   bio, category, location, verified, total_raised,
                   total_campaigns, total_followers, social_links,
                   bank_account, moncash_number, natcash_number
            FROM influencers
            WHERE user_id = ?
        ');
        $stmt->execute([$userId]);
        $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decode JSON fields
        if ($profileData && $profileData['social_links']) {
            $profileData['social_links'] = json_decode($profileData['social_links'], true);
        }
    }

    // Combine user and profile data
    $response = [
        'success' => true,
        'user' => array_merge($user, $profileData ?: [])
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Not authenticated' ? 401 : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
