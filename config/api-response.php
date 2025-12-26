<?php
/**
 * API Response Helper
 * Standardizes API responses across all endpoints
 */

/**
 * Send a standardized JSON success response
 *
 * @param mixed $data The data to return
 * @param string $message Optional success message
 * @param array $meta Optional metadata (pagination, etc.)
 * @param int $statusCode HTTP status code (default 200)
 */
function sendSuccess($data = null, $message = null, $meta = null, $statusCode = 200) {
    http_response_code($statusCode);

    $response = ['success' => true];

    if ($message !== null) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    if ($meta !== null) {
        $response['meta'] = $meta;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send a standardized JSON error response
 *
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @param array $errors Optional validation errors
 * @param mixed $debug Optional debug information (only in development)
 */
function sendError($message, $statusCode = 500, $errors = null, $debug = null) {
    http_response_code($statusCode);

    $response = [
        'success' => false,
        'message' => $message
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    // Only include debug info in development
    if ($debug !== null && getenv('APP_ENV') === 'development') {
        $response['debug'] = $debug;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send 400 Bad Request
 */
function sendBadRequest($message = 'Bad Request', $errors = null) {
    sendError($message, 400, $errors);
}

/**
 * Send 401 Unauthorized
 */
function sendUnauthorized($message = 'Unauthorized') {
    sendError($message, 401);
}

/**
 * Send 403 Forbidden
 */
function sendForbidden($message = 'Forbidden') {
    sendError($message, 403);
}

/**
 * Send 404 Not Found
 */
function sendNotFound($message = 'Resource not found') {
    sendError($message, 404);
}

/**
 * Send 422 Unprocessable Entity (validation errors)
 */
function sendValidationError($message = 'Validation failed', $errors = []) {
    sendError($message, 422, $errors);
}

/**
 * Send 500 Internal Server Error
 */
function sendServerError($message = 'Internal server error', $debug = null) {
    sendError($message, 500, null, $debug);
}

/**
 * Validate required fields in request data
 *
 * @param array $data The data to validate
 * @param array $requiredFields List of required field names
 * @return array|null Returns array of errors or null if valid
 */
function validateRequired($data, $requiredFields) {
    $errors = [];

    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[$field] = "The $field field is required";
        }
    }

    return empty($errors) ? null : $errors;
}

/**
 * Validate email format
 *
 * @param string $email Email to validate
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Haitian format)
 *
 * @param string $phone Phone number to validate
 * @return bool
 */
function validatePhone($phone) {
    // Haitian phone format: +509XXXXXXXX or 509XXXXXXXX or XXXXXXXX (8 digits)
    $pattern = '/^(\+?509)?[0-9]{8}$/';
    return preg_match($pattern, str_replace([' ', '-', '(', ')'], '', $phone)) === 1;
}

/**
 * Validate positive integer
 *
 * @param mixed $value Value to validate
 * @return bool
 */
function validatePositiveInt($value) {
    return is_numeric($value) && intval($value) > 0 && intval($value) == $value;
}

/**
 * Validate enum value
 *
 * @param mixed $value Value to validate
 * @param array $allowedValues List of allowed values
 * @return bool
 */
function validateEnum($value, $allowedValues) {
    return in_array($value, $allowedValues, true);
}

/**
 * Sanitize input string
 *
 * @param string $input Input to sanitize
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get JSON input from request body
 *
 * @return array|null
 */
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendBadRequest('Invalid JSON in request body');
    }

    return $data;
}

/**
 * Set standard CORS headers
 *
 * @param array $allowedOrigins Array of allowed origins (default: localhost only)
 */
function setCorsHeaders($allowedOrigins = ['http://localhost', 'http://127.0.0.1']) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    // Check if origin is allowed
    if (in_array($origin, $allowedOrigins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        // For development, allow all origins (remove in production)
        header('Access-Control-Allow-Origin: *');
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
}

/**
 * Handle OPTIONS preflight request
 */
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Require specific HTTP method
 *
 * @param string|array $allowedMethods Single method or array of allowed methods
 */
function requireMethod($allowedMethods) {
    $allowedMethods = (array) $allowedMethods;

    if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
        sendError(
            'Method not allowed. Allowed methods: ' . implode(', ', $allowedMethods),
            405
        );
    }
}

/**
 * Require authentication (checks session)
 *
 * @return array User session data
 */
function requireAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
        sendUnauthorized('Authentication required');
    }

    return [
        'user_id' => $_SESSION['user_id'],
        'user_type' => $_SESSION['user_type'],
        'email' => $_SESSION['email'] ?? null,
        'phone' => $_SESSION['phone'] ?? null
    ];
}

/**
 * Require specific user type
 *
 * @param string|array $allowedTypes Single type or array of allowed types
 */
function requireUserType($allowedTypes) {
    $allowedTypes = (array) $allowedTypes;

    if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], $allowedTypes)) {
        sendForbidden('This action requires ' . implode(' or ', $allowedTypes) . ' privileges');
    }
}
?>
