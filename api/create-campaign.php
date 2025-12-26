<?php
/**
 * Create Campaign API
 * Allows influencers to create new campaigns
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

    // Only influencers can create campaigns
    requireUserType('influencer');

    // Only allow POST method
    requireMethod('POST');

    // Get JSON input
    $data = getJsonInput();

    // Validate required fields
    $errors = validateRequired($data, ['title', 'description', 'goal_amount', 'category', 'end_date']);
    if ($errors) {
        sendValidationError('Validation failed', $errors);
    }

    // Validate goal amount
    $goalAmount = floatval($data['goal_amount']);
    if ($goalAmount <= 0) {
        sendBadRequest('Goal amount must be greater than 0');
    }

    // Validate category
    $allowedCategories = ['art', 'music', 'education', 'youth', 'heritage'];
    if (!validateEnum($data['category'], $allowedCategories)) {
        sendBadRequest('Invalid category. Allowed: ' . implode(', ', $allowedCategories));
    }

    // Validate end date
    $endDate = $data['end_date'];
    if (strtotime($endDate) <= time()) {
        sendBadRequest('End date must be in the future');
    }

    // Validate status if provided
    $status = $data['status'] ?? 'draft';
    $allowedStatuses = ['draft', 'active'];
    if (!validateEnum($status, $allowedStatuses)) {
        sendBadRequest('Invalid status. Allowed: ' . implode(', ', $allowedStatuses));
    }

    // Connect to database
    $pdo = getDbConnection();

    // Get influencer ID
    $stmt = $pdo->prepare('SELECT id FROM influencers WHERE user_id = ?');
    $stmt->execute([$userId]);
    $influencer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$influencer) {
        sendNotFound('Influencer profile not found');
    }

    $influencerId = $influencer['id'];

    // Start transaction
    $pdo->beginTransaction();

    // Create campaign
    $stmt = $pdo->prepare('
        INSERT INTO campaigns (
            influencer_id, title, description, story, goal_amount,
            raised_amount, currency, category, image_url, video_url,
            start_date, end_date, status
        ) VALUES (?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $influencerId,
        sanitizeInput($data['title']),
        sanitizeInput($data['description']),
        isset($data['story']) ? sanitizeInput($data['story']) : null,
        $goalAmount,
        $data['currency'] ?? 'HTG',
        $data['category'],
        $data['image_url'] ?? null,
        $data['video_url'] ?? null,
        $data['start_date'] ?? date('Y-m-d H:i:s'),
        $endDate,
        $status
    ]);

    $campaignId = $pdo->lastInsertId();

    // Update influencer campaign count
    $stmt = $pdo->prepare('UPDATE influencers SET total_campaigns = total_campaigns + 1 WHERE id = ?');
    $stmt->execute([$influencerId]);

    // Commit transaction
    $pdo->commit();

    // Fetch created campaign with influencer details
    $stmt = $pdo->prepare('
        SELECT c.*, i.display_name, i.username, i.avatar_url, i.verified,
               0 as progress_percentage,
               DATEDIFF(c.end_date, CURDATE()) as days_remaining
        FROM campaigns c
        INNER JOIN influencers i ON c.influencer_id = i.id
        WHERE c.id = ?
    ');
    $stmt->execute([$campaignId]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    sendSuccess(
        $campaign,
        'Campaign created successfully',
        null,
        201 // Created
    );

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    sendServerError('Database error', $e->getMessage());

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    sendServerError('Failed to create campaign', $e->getMessage());
}
?>
