<?php
/**
 * Update Profile API
 * Allows users to update their profile information
 */

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api-response.php';

// Set CORS headers
setCorsHeaders();
handlePreflight();

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

try {
    // Require authentication
    $user = requireAuth();
    $userId = $user['user_id'];
    $userType = $user['user_type'];

    // Only allow POST or PUT methods
    requireMethod(['POST', 'PUT']);

    // Get JSON input
    $data = getJsonInput();

    // Connect to database
    $pdo = getDbConnection();

    // Get current user data
    $stmt = $pdo->prepare('SELECT user_type FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        sendNotFound('User not found');
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update base user table (common fields)
    $updatableUserFields = [];
    $userParams = [];

    if (isset($data['email']) && validateEmail($data['email'])) {
        $updatableUserFields[] = 'email = ?';
        $userParams[] = sanitizeInput($data['email']);

        // Reset email verification if email changed
        $updatableUserFields[] = 'email_verified_at = NULL';
        $updatableUserFields[] = 'is_verified = FALSE';
    }

    if (isset($data['phone']) && validatePhone($data['phone'])) {
        $updatableUserFields[] = 'phone = ?';
        $userParams[] = sanitizeInput($data['phone']);

        // Reset phone verification if phone changed
        $updatableUserFields[] = 'phone_verified_at = NULL';
        $updatableUserFields[] = 'is_verified = FALSE';
    }

    if (isset($data['avatar_url'])) {
        $updatableUserFields[] = 'avatar_url = ?';
        $userParams[] = sanitizeInput($data['avatar_url']);
    }

    // Update users table if there are fields to update
    if (!empty($updatableUserFields)) {
        $userParams[] = $userId;
        $sql = 'UPDATE users SET ' . implode(', ', $updatableUserFields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($userParams);
    }

    // Update type-specific table (donors or influencers)
    if ($currentUser['user_type'] === 'donor') {
        $updatableFields = [];
        $params = [];

        if (isset($data['first_name'])) {
            $updatableFields[] = 'first_name = ?';
            $params[] = sanitizeInput($data['first_name']);
        }

        if (isset($data['last_name'])) {
            $updatableFields[] = 'last_name = ?';
            $params[] = sanitizeInput($data['last_name']);
        }

        if (isset($data['bio'])) {
            $updatableFields[] = 'bio = ?';
            $params[] = sanitizeInput($data['bio']);
        }

        if (isset($data['location'])) {
            $updatableFields[] = 'location = ?';
            $params[] = sanitizeInput($data['location']);
        }

        if (isset($data['favorite_categories']) && is_array($data['favorite_categories'])) {
            $updatableFields[] = 'favorite_categories = ?';
            $params[] = json_encode($data['favorite_categories']);
        }

        if (!empty($updatableFields)) {
            $params[] = $userId;
            $sql = 'UPDATE donors SET ' . implode(', ', $updatableFields) . ' WHERE user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

    } else if ($currentUser['user_type'] === 'influencer') {
        $updatableFields = [];
        $params = [];

        if (isset($data['display_name'])) {
            $updatableFields[] = 'display_name = ?';
            $params[] = sanitizeInput($data['display_name']);
        }

        if (isset($data['username'])) {
            // Check if username is already taken
            $stmt = $pdo->prepare('SELECT id FROM influencers WHERE username = ? AND user_id != ?');
            $stmt->execute([sanitizeInput($data['username']), $userId]);
            if ($stmt->fetch()) {
                $pdo->rollBack();
                sendBadRequest('Username already taken');
            }

            $updatableFields[] = 'username = ?';
            $params[] = sanitizeInput($data['username']);
        }

        if (isset($data['bio'])) {
            $updatableFields[] = 'bio = ?';
            $params[] = sanitizeInput($data['bio']);
        }

        if (isset($data['location'])) {
            $updatableFields[] = 'location = ?';
            $params[] = sanitizeInput($data['location']);
        }

        if (isset($data['cover_image_url'])) {
            $updatableFields[] = 'cover_image_url = ?';
            $params[] = sanitizeInput($data['cover_image_url']);
        }

        if (isset($data['social_links']) && is_array($data['social_links'])) {
            $updatableFields[] = 'social_links = ?';
            $params[] = json_encode($data['social_links']);
        }

        if (!empty($updatableFields)) {
            $params[] = $userId;
            $sql = 'UPDATE influencers SET ' . implode(', ', $updatableFields) . ' WHERE user_id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
    }

    // Commit transaction
    $pdo->commit();

    // Fetch updated profile
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($currentUser['user_type'] === 'donor') {
        $stmt = $pdo->prepare('SELECT * FROM donors WHERE user_id = ?');
    } else {
        $stmt = $pdo->prepare('SELECT * FROM influencers WHERE user_id = ?');
    }
    $stmt->execute([$userId]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Update session variables if email/phone changed
    if (isset($data['email'])) {
        $_SESSION['email'] = $updatedUser['email'];
    }
    if (isset($data['phone'])) {
        $_SESSION['phone'] = $updatedUser['phone'];
    }

    sendSuccess(
        array_merge($updatedUser, $profile ?: []),
        'Profile updated successfully'
    );

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Check for duplicate key errors
    if ($e->getCode() === '23000') {
        sendBadRequest('Email, phone, or username already exists');
    }

    sendServerError('Database error', $e->getMessage());

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    sendServerError('Failed to update profile', $e->getMessage());
}
?>
