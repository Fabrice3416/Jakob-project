<?php
/**
 * Get Influencers API
 * Returns list of influencers with optional filtering
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
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $verified = isset($_GET['verified']) ? filter_var($_GET['verified'], FILTER_VALIDATE_BOOLEAN) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $search = isset($_GET['search']) ? $_GET['search'] : null;

    // Build query
    $query = '
        SELECT
            i.id,
            i.user_id,
            i.display_name,
            i.username,
            i.category,
            i.bio,
            i.avatar_url,
            i.verified,
            i.total_followers,
            i.total_raised,
            i.total_campaigns,
            u.email,
            u.phone,
            u.location
        FROM influencers i
        INNER JOIN users u ON i.user_id = u.id
        WHERE 1=1
    ';

    $params = [];

    if ($category) {
        $query .= ' AND i.category = ?';
        $params[] = $category;
    }

    if ($verified !== null) {
        $query .= ' AND i.verified = ?';
        $params[] = $verified;
    }

    if ($search) {
        $query .= ' AND (i.display_name LIKE ? OR i.username LIKE ? OR i.bio LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $query .= ' ORDER BY i.verified DESC, i.total_raised DESC, i.total_followers DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $influencers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count
    $countQuery = 'SELECT COUNT(*) as total FROM influencers i WHERE 1=1';
    $countParams = [];

    if ($category) {
        $countQuery .= ' AND i.category = ?';
        $countParams[] = $category;
    }

    if ($verified !== null) {
        $countQuery .= ' AND i.verified = ?';
        $countParams[] = $verified;
    }

    if ($search) {
        $countQuery .= ' AND (i.display_name LIKE ? OR i.username LIKE ? OR i.bio LIKE ?)';
        $searchTerm = '%' . $search . '%';
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
        $countParams[] = $searchTerm;
    }

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'total' => intval($total),
        'count' => count($influencers),
        'influencers' => $influencers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
