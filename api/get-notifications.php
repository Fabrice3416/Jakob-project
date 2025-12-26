<?php
/**
 * Get Notifications API
 * Returns user notifications
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
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    $userId = $_SESSION['user_id'];
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

    $pdo = getDbConnection();

    // Build query
    $query = '
        SELECT id, type, title, message, icon, link, is_read, created_at
        FROM notifications
        WHERE user_id = ?
    ';

    $params = [$userId];

    if ($unreadOnly) {
        $query .= ' AND is_read = FALSE';
    }

    $query .= ' ORDER BY created_at DESC LIMIT ?';
    $params[] = $limit;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $countStmt = $pdo->prepare('
        SELECT COUNT(*) as unread_count
        FROM notifications
        WHERE user_id = ? AND is_read = FALSE
    ');
    $countStmt->execute([$userId]);
    $unreadCount = $countStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

    echo json_encode([
        'success' => true,
        'unread_count' => intval($unreadCount),
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Not authenticated' ? 401 : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
