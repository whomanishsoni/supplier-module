<?php
// api/single_read.php
header('Content-Type: application/json');
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $sql = "SELECT * FROM suppliers WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($supplier) {
            echo json_encode($supplier);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Supplier not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID is required']);
}
?>