<?php
/**
 * Create Donation API
 * Allows donors to make donations to campaigns
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

    // Only donors can make donations
    requireUserType('donor');

    // Only allow POST method
    requireMethod('POST');

    // Get JSON input
    $data = getJsonInput();

    // Validate required fields
    $errors = validateRequired($data, ['campaign_id', 'amount', 'payment_method']);
    if ($errors) {
        sendValidationError('Validation failed', $errors);
    }

    // Validate amount
    $amount = floatval($data['amount']);
    if ($amount <= 0) {
        sendBadRequest('Amount must be greater than 0');
    }

    // Validate payment method
    $allowedMethods = ['moncash', 'natcash', 'bank_transfer', 'credit_card'];
    if (!validateEnum($data['payment_method'], $allowedMethods)) {
        sendBadRequest('Invalid payment method. Allowed: ' . implode(', ', $allowedMethods));
    }

    // Connect to database
    $pdo = getDbConnection();

    // Start transaction
    $pdo->beginTransaction();

    // Get donor ID
    $stmt = $pdo->prepare('SELECT id FROM donors WHERE user_id = ?');
    $stmt->execute([$userId]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        $pdo->rollBack();
        sendNotFound('Donor profile not found');
    }

    $donorId = $donor['id'];

    // Verify campaign exists and is active
    $stmt = $pdo->prepare('
        SELECT c.*, i.id as influencer_id
        FROM campaigns c
        INNER JOIN influencers i ON c.influencer_id = i.id
        WHERE c.id = ? AND c.status = "active"
    ');
    $stmt->execute([$data['campaign_id']]);
    $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$campaign) {
        $pdo->rollBack();
        sendNotFound('Campaign not found or not active');
    }

    // Check if campaign end date has passed
    if (strtotime($campaign['end_date']) < time()) {
        $pdo->rollBack();
        sendBadRequest('Campaign has ended');
    }

    // Generate unique transaction reference
    $transactionRef = 'DON_' . strtoupper(uniqid()) . '_' . time();

    // Create donation record
    $stmt = $pdo->prepare('
        INSERT INTO donations (
            donor_id, influencer_id, campaign_id, amount, currency,
            payment_method, transaction_ref, status, is_anonymous, message
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $donorId,
        $campaign['influencer_id'],
        $data['campaign_id'],
        $amount,
        $data['currency'] ?? 'HTG',
        $data['payment_method'],
        $transactionRef,
        'pending', // Will be 'completed' after payment verification
        isset($data['is_anonymous']) ? ($data['is_anonymous'] ? 1 : 0) : 0,
        $data['message'] ?? null
    ]);

    $donationId = $pdo->lastInsertId();

    // Create transaction record for wallet tracking
    $stmt = $pdo->prepare('
        INSERT INTO transactions (
            user_id, type, amount, currency, status, description, reference_id, metadata
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $metadata = json_encode([
        'donation_id' => $donationId,
        'campaign_id' => $data['campaign_id'],
        'campaign_title' => $campaign['title'],
        'payment_method' => $data['payment_method']
    ]);

    $stmt->execute([
        $userId,
        'donation',
        $amount,
        $data['currency'] ?? 'HTG',
        'pending',
        'Donation to: ' . $campaign['title'],
        $transactionRef,
        $metadata
    ]);

    $transactionId = $pdo->lastInsertId();

    // In a real system, here you would:
    // 1. Integrate with payment gateway (MonCash, NatCash, etc.)
    // 2. Process the payment
    // 3. Update donation and transaction status to 'completed' after payment confirmation
    // 4. Update campaign raised_amount
    // 5. Update donor total_donated
    // 6. Send notification to influencer

    // For now, we'll simulate instant success for testing
    // TODO: Replace with actual payment gateway integration
    if (getenv('APP_ENV') === 'development') {
        // Auto-complete donation in development
        $stmt = $pdo->prepare('UPDATE donations SET status = "completed", completed_at = NOW() WHERE id = ?');
        $stmt->execute([$donationId]);

        $stmt = $pdo->prepare('UPDATE transactions SET status = "completed" WHERE id = ?');
        $stmt->execute([$transactionId]);

        // Update campaign raised amount
        $stmt = $pdo->prepare('UPDATE campaigns SET raised_amount = raised_amount + ? WHERE id = ?');
        $stmt->execute([$amount, $data['campaign_id']]);

        // Update donor total donated
        $stmt = $pdo->prepare('UPDATE donors SET total_donated = total_donated + ?, donation_count = donation_count + 1 WHERE id = ?');
        $stmt->execute([$amount, $donorId]);

        // Update influencer total raised
        $stmt = $pdo->prepare('UPDATE influencers SET total_raised = total_raised + ? WHERE id = ?');
        $stmt->execute([$amount, $campaign['influencer_id']]);

        // Create notification for influencer
        $stmt = $pdo->prepare('
            INSERT INTO notifications (user_id, type, title, message, icon, link)
            SELECT user_id, "donation", "New Donation!", ?, "volunteer_activism", ?
            FROM influencers WHERE id = ?
        ');

        $message = sprintf(
            'You received %s HTG for your campaign "%s"',
            number_format($amount, 2),
            $campaign['title']
        );

        $link = '/pages/main/campaign-details.html?id=' . $data['campaign_id'];

        $stmt->execute([$message, $link, $campaign['influencer_id']]);
    }

    // Commit transaction
    $pdo->commit();

    // Fetch created donation with details
    $stmt = $pdo->prepare('
        SELECT d.*, c.title as campaign_title,
               CONCAT(donor.first_name, " ", donor.last_name) as donor_name
        FROM donations d
        INNER JOIN campaigns c ON d.campaign_id = c.id
        INNER JOIN donors donor ON d.donor_id = donor.id
        WHERE d.id = ?
    ');
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);

    sendSuccess(
        [
            'donation' => $donation,
            'transaction_ref' => $transactionRef,
            'payment_url' => null // In production, this would be the payment gateway URL
        ],
        'Donation created successfully',
        null,
        201 // Created
    );

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Database error in create-donation.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    // Return detailed error in development mode
    if (getenv('APP_ENV') === 'development') {
        sendServerError('Database error: ' . $e->getMessage(), $e->getTraceAsString());
    } else {
        sendServerError('Database error', 'An error occurred while processing your donation');
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error in create-donation.php: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    // Return detailed error in development mode
    if (getenv('APP_ENV') === 'development') {
        sendServerError('Error: ' . $e->getMessage(), $e->getTraceAsString());
    } else {
        sendServerError('Failed to create donation', 'An error occurred while processing your donation');
    }
}
?>
