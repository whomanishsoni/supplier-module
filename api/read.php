<?php
// api/read.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Define allowed columns for ordering
$allowedColumns = ['id', 'name', 'email', 'phone', 'address', 'status', 'created_at', 'updated_at'];

$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$searchValue = $_GET['search']['value'] ?? '';
$orderColumnIndex = $_GET['order'][0]['column'] ?? 1; // Default to id (index 1)
$orderDir = strtoupper($_GET['order'][0]['dir'] ?? 'ASC'); // Default to ASC, ensure uppercase
$statusFilter = $_GET['status_filter'] ?? '';
$nameFilter = $_GET['name_filter'] ?? '';
$emailFilter = $_GET['email_filter'] ?? '';

// Map DataTable column indexes to database columns
$columns = [
    0 => null, // Checkbox column (not orderable)
    1 => 'id', // ID column
    2 => 'name',
    3 => 'email',
    4 => 'phone',
    5 => 'status',
    6 => 'created_at',
    7 => 'updated_at',
    8 => null // Actions column (not orderable)
];

// Ensure $orderBy is a valid column
$orderBy = $columns[$orderColumnIndex] ?? 'id';
if (!in_array($orderBy, $allowedColumns)) {
    $orderBy = 'id'; // Fallback to id if invalid
}

// Validate $orderDir
$orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'ASC';

// Build WHERE conditions
$whereConditions = [];
$params = [];

if (!empty($searchValue)) {
    $whereConditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%$searchValue%";
}

if (!empty($statusFilter)) {
    $whereConditions[] = "status = :status_filter";
    $params[':status_filter'] = $statusFilter;
}

if (!empty($nameFilter)) {
    $whereConditions[] = "name LIKE :name_filter";
    $params[':name_filter'] = "%$nameFilter%";
}

if (!empty($emailFilter)) {
    $whereConditions[] = "email LIKE :email_filter";
    $params[':email_filter'] = "%$emailFilter%";
}

$where = '';
if (!empty($whereConditions)) {
    $where = 'WHERE ' . implode(' AND ', $whereConditions);
}

try {
    // Count total records (unfiltered)
    $totalRecordsSql = "SELECT COUNT(*) FROM suppliers";
    $totalRecords = $pdo->query($totalRecordsSql)->fetchColumn();

    // Count filtered records
    $filteredSql = "SELECT COUNT(*) FROM suppliers $where";
    $filteredStmt = $pdo->prepare($filteredSql);
    $filteredStmt->execute($params);
    $filteredRecords = $filteredStmt->fetchColumn();

    // Fetch data with explicit columns
    $sql = "SELECT id, name, email, phone, address, status, created_at, updated_at FROM suppliers $where ORDER BY $orderBy $orderDir LIMIT :start, :length";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the data being returned (conditional for development)
    if (defined('DEBUG') && DEBUG) {
        error_log(print_r($data, true)); // Log to server error log for verification
    }

    echo json_encode([
        'draw' => (int)$draw,
        'recordsTotal' => (int)$totalRecords,
        'recordsFiltered' => (int)$filteredRecords,
        'data' => $data
    ]);
} catch (PDOException $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'draw' => (int)$draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
?>