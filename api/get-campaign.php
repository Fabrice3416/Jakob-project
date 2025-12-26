<?php
/**
 * Get Single Campaign API
 * Returns a single campaign by ID
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
    // Get campaign ID from query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Campaign ID is required'
        ]);
        exit;
    }

    $campaignId = intval($_GET['id']);
    $pdo = getDbConnection();

    // Get campaign with influencer details
    $query = '
        SELECT
            c.*,
            i.display_name,
            i.username,
            i.avatar_url,
            i.verified,
            i.bio as influencer_bio,
            ROUND((c.raised_amount / c.goal_amount) * 100, 2) as progress_percentage,
            DATEDIFF(c.end_date, CURDATE()) as days_remaining
        FROM campaigns c
        INNER JOIN influencers i ON c.influencer_id = i.id
        WHERE c.id = ?
    ';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Campaign not found'
        ]);
        exit;
    }

    // Get recent supporters
    $supportersQuery = '
        SELECT
            d.amount,
            d.created_at,
            CASE
                WHEN d.is_anonymous = TRUE THEN "Anonymous Supporter"
                ELSE CONCAT(dn.first_name, " ", dn.last_name)
            END as supporter_name,
            d.is_anonymous
        FROM donations d
        LEFT JOIN donors dn ON d.donor_id = dn.id
        WHERE d.campaign_id = ? AND d.status = "completed"
        ORDER BY d.created_at DESC
        LIMIT 10
    ';

    $supportersStmt = $pdo->prepare($supportersQuery);
    $supportersStmt->execute([$campaignId]);
    $supporters = $supportersStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total supporters count
    $supportersCountQuery = '
        SELECT COUNT(DISTINCT donor_id) as total
        FROM donations
        WHERE campaign_id = ? AND status = "completed"
    ';

    $countStmt = $pdo->prepare($supportersCountQuery);
    $countStmt->execute([$campaignId]);
    $supportersCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => $campaign,
        'supporters' => $supporters,
        'supporters_count' => intval($supportersCount)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
