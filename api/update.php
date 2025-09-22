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
        // Check if the email is already used by another supplier
        $sqlCheck = "SELECT id FROM suppliers WHERE email = :email AND id != :id";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([':email' => $email, ':id' => $id]);
        if ($stmtCheck->fetch()) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'This email is already registered. Please use a different email.'
            ]);
            exit;
        }

        // Update supplier
        $sql = "UPDATE suppliers 
                SET name = :name, email = :email, phone = :phone, address = :address, status = :status, updated_at = NOW()
                WHERE id = :id";
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
        // Handle duplicate email error (fallback in case the check misses something)
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