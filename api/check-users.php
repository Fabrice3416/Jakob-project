<?php
/**
 * Check Users in Database
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();

    // Get all users with their type-specific data
    $stmt = $pdo->query('
        SELECT
            u.id,
            u.email,
            u.phone,
            u.user_type,
            u.is_verified,
            u.created_at,
            CASE
                WHEN u.user_type = "donor" THEN d.first_name
                WHEN u.user_type = "influencer" THEN i.display_name
            END as name,
            CASE
                WHEN u.user_type = "donor" THEN d.last_name
                WHEN u.user_type = "influencer" THEN i.username
            END as secondary_name
        FROM users u
        LEFT JOIN donors d ON u.id = d.user_id AND u.user_type = "donor"
        LEFT JOIN influencers i ON u.id = i.user_id AND u.user_type = "influencer"
        ORDER BY u.id DESC
    ');

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($users),
        'users' => $users
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
