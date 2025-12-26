<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api-response.php';

setCorsHeaders();
handlePreflight();
header('Content-Type: application/json; charset=utf-8');

try {
    $user = requireAuth();
    requireUserType('influencer');
    requireMethod(['POST', 'PUT']);

    $data = getJsonInput();

    if (!isset($data['campaign_id'])) {
        sendBadRequest('campaign_id is required');
    }

    $pdo = getDbConnection();

    // Get influencer ID
    $stmt = $pdo->prepare('SELECT id FROM influencers WHERE user_id = ?');
    $stmt->execute([$user['user_id']]);
    $influencer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$influencer) {
        sendNotFound('Influencer profile not found');
    }

    // Verify campaign belongs to this influencer
    $stmt = $pdo->prepare('SELECT * FROM campaigns WHERE id = ? AND influencer_id = ?');
    $stmt->execute([$data['campaign_id'], $influencer['id']]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        sendForbidden('You can only edit your own campaigns');
    }

    // Build update query
    $updates = [];
    $params = [];

    $updatableFields = ['title', 'description', 'story', 'goal_amount', 'category', 'image_url', 'video_url', 'start_date', 'end_date', 'status'];

    foreach ($updatableFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($updates)) {
        sendBadRequest('No fields to update');
    }

    $params[] = $data['campaign_id'];
    $sql = 'UPDATE campaigns SET ' . implode(', ', $updates) . ' WHERE id = ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch updated campaign
    $stmt = $pdo->prepare('
        SELECT c.*, i.display_name, i.username, i.avatar_url, i.verified,
               ROUND((c.raised_amount / c.goal_amount) * 100, 2) as progress_percentage
        FROM campaigns c
        INNER JOIN influencers i ON c.influencer_id = i.id
        WHERE c.id = ?
    ');
    $stmt->execute([$data['campaign_id']]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);

    sendSuccess($updated, 'Campaign updated successfully');

} catch (Exception $e) {
    sendServerError('Failed to update campaign', $e->getMessage());
}
?>
