<?php
/**
 * Get Campaigns API
 * Returns campaigns with optional filtering
 */

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
    $pdo = getDbConnection();

    // Get query parameters
    $status = isset($_GET['status']) ? $_GET['status'] : 'active';
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $influencerId = isset($_GET['influencer_id']) ? intval($_GET['influencer_id']) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    // Build query
    $query = '
        SELECT
            c.*,
            i.display_name as influencer_name,
            i.username as influencer_username,
            i.avatar_url as influencer_avatar,
            i.verified as influencer_verified,
            ROUND((c.raised_amount / c.goal_amount) * 100, 2) as progress_percentage,
            DATEDIFF(c.end_date, CURDATE()) as days_remaining
        FROM campaigns c
        INNER JOIN influencers i ON c.influencer_id = i.id
        WHERE 1=1
    ';

    $params = [];

    if ($status) {
        $query .= ' AND c.status = ?';
        $params[] = $status;
    }

    if ($category) {
        $query .= ' AND c.category = ?';
        $params[] = $category;
    }

    if ($influencerId) {
        $query .= ' AND c.influencer_id = ?';
        $params[] = $influencerId;
    }

    $query .= ' ORDER BY c.created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countQuery = 'SELECT COUNT(*) as total FROM campaigns c WHERE 1=1';
    $countParams = [];

    if ($status) {
        $countQuery .= ' AND c.status = ?';
        $countParams[] = $status;
    }

    if ($category) {
        $countQuery .= ' AND c.category = ?';
        $countParams[] = $category;
    }

    if ($influencerId) {
        $countQuery .= ' AND c.influencer_id = ?';
        $countParams[] = $influencerId;
    }

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'total' => intval($total),
        'count' => count($campaigns),
        'campaigns' => $campaigns
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
