<?php
// api/read.php
header('Content-Type: application/json');
require_once '../config/db.php';

$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$searchValue = $_GET['search']['value'] ?? '';
$orderColumnIndex = $_GET['order'][0]['column'] ?? 7; // Default to created_at (index 7)
$orderDir = $_GET['order'][0]['dir'] ?? 'desc'; // Default to DESC
$statusFilter = $_GET['status_filter'] ?? '';
$nameFilter = $_GET['name_filter'] ?? '';
$emailFilter = $_GET['email_filter'] ?? '';

// Map DataTable column indexes to database columns
$columns = [
    0 => null, // Checkbox column (not orderable)
    1 => null, // Counter column (not orderable)
    2 => 'name',
    3 => 'email',
    4 => 'phone',
    5 => 'address',
    6 => 'status',
    7 => 'created_at',
    8 => null // Actions column (not orderable)
];
$orderBy = $columns[$orderColumnIndex] ?? 'created_at';

// Build WHERE conditions
$whereConditions = [];
$params = [];

if (!empty($searchValue)) {
    $whereConditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search OR address LIKE :search)";
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

// Count total records (unfiltered)
$totalRecordsSql = "SELECT COUNT(*) FROM suppliers";
$totalRecords = $pdo->query($totalRecordsSql)->fetchColumn();

// Count filtered records
$filteredSql = "SELECT COUNT(*) FROM suppliers $where";
$filteredStmt = $pdo->prepare($filteredSql);
$filteredStmt->execute($params);
$filteredRecords = $filteredStmt->fetchColumn();

// Fetch data
$sql = "SELECT * FROM suppliers $where ORDER BY $orderBy $orderDir LIMIT :start, :length";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'draw' => (int)$draw,
    'recordsTotal' => (int)$totalRecords,
    'recordsFiltered' => (int)$filteredRecords,
    'data' => $data
]);
?>