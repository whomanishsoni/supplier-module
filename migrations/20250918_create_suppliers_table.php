<?php
// migrate_and_seed.php
require_once '../config/db.php';

// Increase PHP timeout to prevent script termination
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration and Seeding</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        .success { background-color: #e6ffed; color: #2e7d32; }
        .error { background-color: #ffebee; color: #d32f2f; }
        .progress-container { width: 100%; background-color: #f3f3f3; border-radius: 5px; margin-top: 10px; }
        .progress-bar { height: 20px; background-color: #4caf50; border-radius: 5px; text-align: center; color: white; line-height: 20px; }
        .seeding-section { margin-top: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .seeding-progress { font-weight: bold; margin-bottom: 5px; }
        #seeding-status { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <h2>Database Migration and Seeding</h2>
    <div id="status"></div>
    <div class="progress-container">
        <div id="progress-bar" class="progress-bar">0%</div>
    </div>
    <div id="seeding-section" class="seeding-section" style="display: none;">
        <h3>Seeding Progress</h3>
        <div id="seeding-progress" class="seeding-progress">Records: 0 / 100,000 (0%)</div>
        <div class="progress-container">
            <div id="seeding-bar" class="progress-bar">0%</div>
        </div>
        <div id="seeding-status">Starting...</div>
    </div>

    <script>
        // Prevent page refresh or close during seeding
        let isSeeding = false;
        window.onbeforeunload = function() {
            if (isSeeding) return "Data seeding is in progress. Closing or refreshing may cause incomplete data. Are you sure?";
        };

        // Function to update main status
        function updateStatus(message, isError = false) {
            const statusDiv = document.getElementById('status');
            const statusMessage = document.createElement('div');
            statusMessage.className = `status ${isError ? 'error' : 'success'}`;
            statusMessage.textContent = message;
            statusDiv.appendChild(statusMessage);
            statusDiv.scrollTop = statusDiv.scrollHeight;
        }

        // Function to update main progress bar
        function updateProgress(percentage) {
            const progressBar = document.getElementById('progress-bar');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
        }

        // Function to update seeding progress
        function updateSeedingProgress(data) {
            const section = document.getElementById('seeding-section');
            const progressText = document.getElementById('seeding-progress');
            const seedingBar = document.getElementById('seeding-bar');
            const status = document.getElementById('seeding-status');

            if (data.count >= 0) {
                section.style.display = 'block';
                const percentage = Math.round((data.count / 100000) * 100);
                const estTimeLeft = data.estTimeLeft || 'Calculating...';
                progressText.textContent = `Records: ${data.count.toLocaleString()} / 100,000 (${percentage}%)`;
                seedingBar.style.width = percentage + '%';
                seedingBar.textContent = percentage + '%';
                status.textContent = `Est. time left: ${estTimeLeft}`;
            }
        }

        // Poll for seeding progress every 2 seconds
        let pollInterval;
        function startPolling() {
            isSeeding = true;
            pollInterval = setInterval(async () => {
                try {
                    const response = await fetch('get_progress.php');
                    const data = await response.json();
                    if (data.error) {
                        updateStatus('Progress polling error: ' + data.error, true);
                        clearInterval(pollInterval);
                        isSeeding = false;
                        return;
                    }
                    updateSeedingProgress(data);
                    if (data.count >= 100000) {
                        clearInterval(pollInterval);
                        isSeeding = false;
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }, 2000);
        }

        function stopPolling() {
            if (pollInterval) clearInterval(pollInterval);
            isSeeding = false;
            window.onbeforeunload = null;
        }
    </script>

    <?php
    try {
        // Increase MySQL timeout
        $pdo->exec("SET SESSION wait_timeout = 300");
        $pdo->exec("SET SESSION innodb_lock_wait_timeout = 300");

        // Step 1: Check if table exists
        echo "<script>updateStatus('Step 1: Checking if suppliers table exists...');</script>";
        echo "<script>updateProgress(10);</script>";
        ob_flush(); flush();

        $tableCheck = $pdo->query("SHOW TABLES LIKE 'suppliers'")->fetch();
        if ($tableCheck) {
            echo "<script>updateStatus('Suppliers table already exists, skipping creation.');</script>";
        } else {
            echo "<script>updateStatus('Creating suppliers table...');</script>";
            $sql = "
                CREATE TABLE suppliers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    phone VARCHAR(20),
                    address TEXT,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            $pdo->exec($sql);
            echo "<script>updateStatus('Suppliers table created successfully.');</script>";
        }

        // Check and add updated_at column if missing
        $columnCheck = $pdo->query("SHOW COLUMNS FROM suppliers LIKE 'updated_at'")->fetch();
        if (!$columnCheck) {
            echo "<script>updateStatus('Adding updated_at column...');</script>";
            $pdo->exec("ALTER TABLE suppliers ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "<script>updateStatus('updated_at column added successfully.');</script>";
        } else {
            echo "<script>updateStatus('updated_at column already exists, skipping.');</script>";
        }

        // Step 2: Check and create indexes
        echo "<script>updateStatus('Step 2: Checking and creating indexes...');</script>";
        echo "<script>updateProgress(20);</script>";
        ob_flush(); flush();

        foreach (['idx_name' => 'name', 'idx_status' => 'status'] as $index => $column) {
            $indexCheck = $pdo->query("SHOW INDEXES FROM suppliers WHERE Key_name = '$index'")->fetch();
            if (!$indexCheck) {
                $pdo->exec("ALTER TABLE suppliers ADD INDEX $index ($column)");
                echo "<script>updateStatus('Index $index created successfully.');</script>";
            } else {
                echo "<script>updateStatus('Index $index already exists, skipping creation.');</script>";
            }
        }
        $indexCheck = $pdo->query("SHOW INDEXES FROM suppliers WHERE Key_name = 'idx_fulltext'")->fetch();
        if (!$indexCheck) {
            $pdo->exec("ALTER TABLE suppliers ADD FULLTEXT idx_fulltext (name, address)");
            echo "<script>updateStatus('Fulltext index idx_fulltext created successfully.');</script>";
        } else {
            echo "<script>updateStatus('Fulltext index idx_fulltext already exists, skipping creation.');</script>";
        }

        // Step 3: Check existing records
        echo "<script>updateStatus('Step 3: Checking existing records...');</script>";
        echo "<script>updateProgress(30);</script>";
        ob_flush(); flush();

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordCount = $result['count'];
        if ($recordCount > 0) {
            echo "<script>updateStatus('Found $recordCount records. Truncating table to seed new data...');</script>";
            $pdo->exec("TRUNCATE TABLE suppliers");
            echo "<script>updateStatus('Table truncated successfully.');</script>";
        } else {
            echo "<script>updateStatus('Table is empty, ready for seeding.');</script>";
        }

        // Step 4: Create progress tracking table
        echo "<script>updateStatus('Step 4: Setting up progress tracking...');</script>";
        echo "<script>updateProgress(40);</script>";
        ob_flush(); flush();

        $pdo->exec("CREATE TABLE IF NOT EXISTS seeding_progress (
            id INT AUTO_INCREMENT PRIMARY KEY,
            inserted_count INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB");
        $pdo->exec("TRUNCATE TABLE seeding_progress");
        $pdo->exec("INSERT INTO seeding_progress (inserted_count) VALUES (0)");

        // Step 5: Create stored procedure
        echo "<script>updateStatus('Step 5: Creating seeding procedure...');</script>";
        echo "<script>updateProgress(50);</script>";
        ob_flush(); flush();

        $timestamp = time();
        $seed_sql = "
        CREATE PROCEDURE InsertIndianSuppliers(IN num_records INT)
        BEGIN
            DECLARE i INT DEFAULT 1;
            DECLARE batch_size INT DEFAULT 1000;
            DECLARE progress_step INT DEFAULT 5000;

            DECLARE first_names VARCHAR(255) DEFAULT 'Aarav,Aditya,Akash,Ananya,Arjun,Aryan,Ayesha,Deepak,Dhruv,Gaurav,Isha,Kavya,Manish,Meera,Nikhil,Pooja,Priya,Rahul,Riya,Sanjay,Shalini,Shivam,Sneha,Tanvi,Vikram';
            DECLARE last_names VARCHAR(255) DEFAULT 'Agarwal,Bhatia,Chopra,Das,Gupta,Jain,Kapoor,Kumar,Mehta,Patel,Sharma,Singh,Verma';
            DECLARE cities VARCHAR(255) DEFAULT 'Mumbai,Delhi,Bengaluru,Chennai,Kolkata,Hyderabad,Ahmedabad,Pune,Jaipur,Lucknow';
            DECLARE states VARCHAR(255) DEFAULT 'Maharashtra,Delhi,Karnataka,Tamil Nadu,West Bengal,Telangana,Gujarat,Maharashtra,Rajasthan,Uttar Pradesh';

            WHILE i <= num_records DO
                SET @sql = 'INSERT INTO suppliers (name, email, phone, address, status) VALUES ';
                SET @values = '';
                SET @batch_count = 0;

                WHILE @batch_count < batch_size AND i <= num_records DO
                    SET @first_name = SUBSTRING_INDEX(SUBSTRING_INDEX(first_names, ',', FLOOR(1 + RAND() * 25)), ',', -1);
                    SET @last_name = SUBSTRING_INDEX(SUBSTRING_INDEX(last_names, ',', FLOOR(1 + RAND() * 13)), ',', -1);
                    SET @name = CONCAT(@first_name, ' ', @last_name);
                    SET @email = CONCAT(LOWER(@first_name), i, '_', $timestamp, '@', ELT(FLOOR(1 + RAND() * 3), 'company.in', 'gmail.com', 'outlook.in'));
                    SET @phone = CONCAT('+91-', FLOOR(6000000000 + RAND() * 4000000000));
                    SET @city = SUBSTRING_INDEX(SUBSTRING_INDEX(cities, ',', FLOOR(1 + RAND() * 10)), ',', -1);
                    SET @state = SUBSTRING_INDEX(SUBSTRING_INDEX(states, ',', FLOOR(1 + RAND() * 10)), ',', -1);
                    SET @pincode = FLOOR(100000 + RAND() * 900000);
                    SET @address = CONCAT(FLOOR(1 + RAND() * 999), ', ', ELT(FLOOR(1 + RAND() * 3), 'MG Road', 'Station Road', 'Bazaar Street'), ', ', @city, ', ', @state, ' ', @pincode);
                    SET @status = IF(RAND() > 0.5, 'active', 'inactive');

                    IF @batch_count = 0 THEN
                        SET @values = CONCAT('(\"', @name, '\", \"', @email, '\", \"', @phone, '\", \"', @address, '\", \"', @status, '\")');
                    ELSE
                        SET @values = CONCAT(@values, ', (\"', @name, '\", \"', @email, '\", \"', @phone, '\", \"', @address, '\", \"', @status, '\")');
                    END IF;

                    SET @batch_count = @batch_count + 1;
                    SET i = i + 1;
                END WHILE;

                SET @sql = CONCAT(@sql, @values);
                PREPARE stmt FROM @sql;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;

                -- Update progress every 5000 records
                IF i % progress_step = 0 OR i > num_records THEN
                    UPDATE seeding_progress SET inserted_count = i - 1;
                END IF;
                COMMIT;
            END WHILE;
        END;
        ";

        $procCheck = $pdo->query("SELECT 1 FROM information_schema.routines WHERE routine_name = 'InsertIndianSuppliers' AND routine_schema = DATABASE()")->fetch();
        if ($procCheck) {
            $pdo->exec("DROP PROCEDURE IF EXISTS InsertIndianSuppliers");
            echo "<script>updateStatus('Dropped existing stored procedure.');</script>";
        }
        $pdo->exec($seed_sql);
        echo "<script>updateStatus('Stored procedure created successfully.');</script>";
        ob_flush(); flush();

        // Step 6: Start seeding
        echo "<script>updateStatus('Step 6: Seeding 100,000 records...');</script>";
        echo "<script>updateProgress(60);</script>";
        echo "<script>startPolling();</script>";
        ob_flush(); flush();

        $pdo->exec("CALL InsertIndianSuppliers(100000)");
        echo "<script>updateStatus('Seeding completed.');</script>";
        echo "<script>updateProgress(80);</script>";
        echo "<script>stopPolling();</script>";
        ob_flush(); flush();

        // Clean up progress table
        $pdo->exec("DROP TABLE IF EXISTS seeding_progress");
        echo "<script>updateStatus('Step 7: Cleaned up progress tracking.');</script>";
        echo "<script>updateProgress(90);</script>";
        ob_flush(); flush();

        // Step 8: Verify records
        echo "<script>updateStatus('Step 8: Verifying record count...');</script>";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM suppliers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $finalCount = $result['count'];
        if ($finalCount == 100000) {
            echo "<script>updateStatus('Success: Exactly 100,000 records inserted.');</script>";
        } else {
            echo "<script>updateStatus('Warning: Only $finalCount records inserted, expected 100,000. Check for interruptions or errors.', true);</script>";
        }
        echo "<script>updateProgress(100);</script>";
        ob_flush(); flush();

        // Step 9: Cleanup procedure
        echo "<script>updateStatus('Step 9: Cleaning up stored procedure...');</script>";
        $pdo->exec("DROP PROCEDURE IF EXISTS InsertIndianSuppliers");
        echo "<script>updateStatus('Stored procedure dropped successfully.');</script>";
        ob_flush(); flush();

        echo "<script>window.onbeforeunload = null;</script>";

    } catch (PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
        echo "<script>updateStatus('" . addslashes($errorMessage) . "', true);</script>";
        echo "<script>stopPolling();</script>";
        echo "<script>window.onbeforeunload = null;</script>";
        ob_flush(); flush();
    }
    ?>
</body>
</html>