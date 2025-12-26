<?php
/**
 * JaKòb Session Management Helper
 * Handles session initialization, user authentication checks, and session data
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is a donor
 * @return bool
 */
function isDonor() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'donor';
}

/**
 * Check if user is an influencer
 * @return bool
 */
function isInfluencer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'influencer';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user type
 * @return string|null ('donor' or 'influencer')
 */
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Get current user email
 * @return string|null
 */
function getUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Get current user phone
 * @return string|null
 */
function getUserPhone() {
    return $_SESSION['phone'] ?? null;
}

/**
 * Get donor-specific ID
 * @return int|null
 */
function getDonorId() {
    return $_SESSION['donor_id'] ?? null;
}

/**
 * Get influencer-specific ID
 * @return int|null
 */
function getInfluencerId() {
    return $_SESSION['influencer_id'] ?? null;
}

/**
 * Get user display name (varies by type)
 * @return string|null
 */
function getUserDisplayName() {
    if (isDonor()) {
        return $_SESSION['full_name'] ?? 'User';
    } else if (isInfluencer()) {
        return $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'Creator';
    }
    return null;
}

/**
 * Require authentication - redirect to login if not logged in
 * @param string $redirectUrl URL to redirect to if not authenticated
 */
function requireAuth($redirectUrl = '/pages/auth/login.html') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Require donor role - redirect if not a donor
 * @param string $redirectUrl URL to redirect to if not a donor
 */
function requireDonor($redirectUrl = '/pages/main/home.html') {
    requireAuth();
    if (!isDonor()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Require influencer role - redirect if not an influencer
 * @param string $redirectUrl URL to redirect to if not an influencer
 */
function requireInfluencer($redirectUrl = '/pages/creator/my-campaigns.html') {
    requireAuth();
    if (!isInfluencer()) {
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Logout user - destroy session and redirect
 * @param string $redirectUrl URL to redirect after logout
 */
function logout($redirectUrl = '/pages/auth/splash.html') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }

    // Destroy remember_token cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 42000, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to specified URL
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Get user session data as array
 * @return array
 */
function getUserSessionData() {
    if (!isLoggedIn()) {
        return [];
    }

    return [
        'user_id' => getCurrentUserId(),
        'user_type' => getUserType(),
        'email' => getUserEmail(),
        'phone' => getUserPhone(),
        'is_verified' => $_SESSION['is_verified'] ?? false,
        'display_name' => getUserDisplayName(),
        'donor_id' => getDonorId(),
        'influencer_id' => getInfluencerId(),
        'username' => $_SESSION['username'] ?? null,
    ];
}

/**
 * Check if user is verified
 * @return bool
 */
function isVerified() {
    return isset($_SESSION['is_verified']) && $_SESSION['is_verified'] === true;
}

/**
 * Set session flash message
 * @param string $type Type of message (success, error, warning, info)
 * @param string $message The message text
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
