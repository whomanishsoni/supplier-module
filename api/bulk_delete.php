<?php
// api/bulk_delete.php
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ids = array_map('intval', $data['ids'] ?? []);

    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No IDs provided']);
        exit;
    }

    // Process in chunks for large deletions to avoid locks
    $chunkSize = 1000;
    $deletedCount = 0;
    try {
        foreach (array_chunk($ids, $chunkSize) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $sql = "DELETE FROM suppliers WHERE id IN ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($chunk);
            $deletedCount += $stmt->rowCount();
        }
        echo json_encode(['status' => 'success', 'message' => "$deletedCount suppliers deleted successfully"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>