<?php
/**
 * Get Wallet API
 * Returns wallet balance and recent transactions
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
    $userType = $_SESSION['user_type'];

    $pdo = getDbConnection();

    // Get payment methods and balance
    $stmt = $pdo->prepare('
        SELECT id, type, provider, account_number, account_name,
               is_default, is_verified, balance
        FROM payment_methods
        WHERE user_id = ?
        ORDER BY is_default DESC, id DESC
    ');
    $stmt->execute([$userId]);
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total balance
    $totalBalance = 0;
    foreach ($paymentMethods as $method) {
        $totalBalance += floatval($method['balance']);
    }

    // Get recent transactions
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $stmt = $pdo->prepare('
        SELECT id, type, amount, currency, status, description,
               reference_id, metadata, created_at
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ');
    $stmt->execute([$userId, $limit]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode metadata JSON
    foreach ($transactions as &$transaction) {
        if ($transaction['metadata']) {
            $transaction['metadata'] = json_decode($transaction['metadata'], true);
        }
    }

    // Get donation stats for donors
    $donationStats = null;
    if ($userType === 'donor') {
        $stmt = $pdo->prepare('
            SELECT
                COUNT(*) as total_donations,
                SUM(amount) as total_donated,
                AVG(amount) as avg_donation
            FROM donations
            WHERE donor_id = (SELECT id FROM donors WHERE user_id = ?)
              AND status = "completed"
        ');
        $stmt->execute([$userId]);
        $donationStats = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get earning stats for influencers
    $earningStats = null;
    if ($userType === 'influencer') {
        $stmt = $pdo->prepare('
            SELECT
                COUNT(DISTINCT d.id) as total_received,
                SUM(d.amount) as total_raised,
                COUNT(DISTINCT d.donor_id) as unique_donors
            FROM donations d
            WHERE d.influencer_id = (SELECT id FROM influencers WHERE user_id = ?)
              AND d.status = "completed"
        ');
        $stmt->execute([$userId]);
        $earningStats = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'wallet' => [
            'total_balance' => number_format($totalBalance, 2, '.', ''),
            'currency' => 'HTG',
            'payment_methods' => $paymentMethods
        ],
        'transactions' => $transactions,
        'stats' => $userType === 'donor' ? $donationStats : $earningStats
    ]);

} catch (Exception $e) {
    http_response_code($e->getMessage() === 'Not authenticated' ? 401 : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
