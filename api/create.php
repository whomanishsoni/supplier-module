<?php
// api/create.php
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $status = $data['status'] ?? 'active';

    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Name and email are required']);
        exit;
    }

    try {
        $sql = "INSERT INTO suppliers (name, email, phone, address, status, created_at, updated_at) 
                VALUES (:name, :email, :phone, :address, :status, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':address' => $address,
            ':status' => $status
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Supplier created successfully']);
    } catch (PDOException $e) {
        // Handle duplicate email error
        if ($e->getCode() === '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'This email is already registered. Please use a different email.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
?>