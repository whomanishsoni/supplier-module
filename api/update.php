<?php
// api/update.php
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = (int)($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $status = $data['status'] ?? 'active';

    if (empty($id) || empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID, name, and email are required']);
        exit;
    }

    try {
        $sql = "UPDATE suppliers SET name = :name, email = :email, phone = :phone, address = :address, status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':status' => $status
        ]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Supplier updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No changes made or supplier not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>