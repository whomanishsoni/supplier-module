<?php
// migrations/20250918_create_suppliers_table.php
require_once '../config/db.php';

try {
    $sql = "
        CREATE TABLE IF NOT EXISTS suppliers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    
    // Add indexes for performance
    $pdo->exec("ALTER TABLE suppliers ADD INDEX idx_name (name)");
    $pdo->exec("ALTER TABLE suppliers ADD INDEX idx_status (status)");
    $pdo->exec("ALTER TABLE suppliers ADD FULLTEXT idx_fulltext (name, address)");
    
    echo "Suppliers table created successfully with indexes.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>