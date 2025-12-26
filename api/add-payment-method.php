<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api-response.php';

setCorsHeaders();
handlePreflight();
header('Content-Type: application/json; charset=utf-8');

try {
    $user = requireAuth();
    requireMethod('POST');

    $data = getJsonInput();

    // Validate required fields
    $errors = validateRequired($data, ['type', 'account_number']);
    if ($errors) {
        sendValidationError('Validation failed', $errors);
    }

    // Validate payment method type
    $allowedTypes = ['moncash', 'natcash', 'bank_transfer', 'credit_card'];
    if (!validateEnum($data['type'], $allowedTypes)) {
        sendBadRequest('Invalid payment method type');
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // If this is the first payment method, make it default
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM payment_methods WHERE user_id = ?');
    $stmt->execute([$user['user_id']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $isFirst = $count == 0;

    // If user wants to make this default, update others
    $isDefault = isset($data['is_default']) && $data['is_default'] === true;
    if ($isDefault || $isFirst) {
        $stmt = $pdo->prepare('UPDATE payment_methods SET is_default = FALSE WHERE user_id = ?');
        $stmt->execute([$user['user_id']]);
    }

    // Insert payment method
    $stmt = $pdo->prepare('
        INSERT INTO payment_methods (
            user_id, type, provider, account_number, account_name,
            is_default, is_verified, balance
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $stmt->execute([
        $user['user_id'],
        $data['type'],
        $data['provider'] ?? null,
        $data['account_number'],
        $data['account_name'] ?? null,
        $isDefault || $isFirst,
        false, // Needs verification
        0.00
    ]);

    $methodId = $pdo->lastInsertId();
    $pdo->commit();

    // Fetch created payment method
    $stmt = $pdo->prepare('SELECT * FROM payment_methods WHERE id = ?');
    $stmt->execute([$methodId]);
    $method = $stmt->fetch(PDO::FETCH_ASSOC);

    sendSuccess($method, 'Payment method added successfully', null, 201);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendServerError('Database error', $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendServerError('Failed to add payment method', $e->getMessage());
}
?>
