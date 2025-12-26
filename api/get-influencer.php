<?php
/**
 * Get Single Influencer API
 * Returns detailed information about a specific influencer
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
    // Get influencer ID from query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Influencer ID is required'
        ]);
        exit;
    }

    $influencerId = intval($_GET['id']);
    $pdo = getDbConnection();

    // Get influencer details
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
            i.location,
            i.created_at,
            u.email,
            u.phone
        FROM influencers i
        INNER JOIN users u ON i.user_id = u.id
        WHERE i.id = ?
    ';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$influencerId]);
    $influencer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$influencer) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Influencer not found'
        ]);
        exit;
    }

    // Get campaign statistics
    $statsQuery = '
        SELECT
            COUNT(*) as total_campaigns,
            SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_campaigns,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_campaigns,
            SUM(raised_amount) as total_raised_verified
        FROM campaigns
        WHERE influencer_id = ?
    ';

    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute([$influencerId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Get recent campaigns
    $recentCampaignsQuery = '
        SELECT
            id,
            title,
            description,
            category,
            goal_amount,
            raised_amount,
            image_url,
            status,
            created_at,
            ROUND((raised_amount / goal_amount) * 100, 2) as progress_percentage
        FROM campaigns
        WHERE influencer_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ';

    $recentStmt = $pdo->prepare($recentCampaignsQuery);
    $recentStmt->execute([$influencerId]);
    $recentCampaigns = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge stats into influencer data
    $influencer['stats'] = $stats;
    $influencer['recent_campaigns'] = $recentCampaigns;

    echo json_encode([
        'success' => true,
        'influencer' => $influencer
    ]);

} catch (PDOException $e) {
    error_log('Database error in get-influencer.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDOException'
    ]);
} catch (Exception $e) {
    error_log('Error in get-influencer.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'Exception'
    ]);
}
?>
