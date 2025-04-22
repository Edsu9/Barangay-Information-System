<?php
// Enable error reporting for debugging
// Comment these lines in production for security
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "barangay_system";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8");

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Function to sanitize input data
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Function to generate a unique ID
function generateUniqueID($prefix = '') {
    return $prefix . date('YmdHis') . rand(1000, 9999);
}

// Modified function to log activity - with error handling
function logActivity($user_id, $action) {
    global $conn;
    
    // Check if activity_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows == 0) {
        // Table doesn't exist, create it
        $create_table = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11),
            action TEXT NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        if (!$conn->query($create_table)) {
            // If we can't create the table, just return without logging
            error_log("Failed to create activity_logs table: " . $conn->error);
            return;
        }
    }
    
    // Now try to log the activity
    try {
        $timestamp = date('Y-m-d H:i:s');
        $sql = "INSERT INTO activity_logs (user_id, action, timestamp) VALUES ('$user_id', '$action', '$timestamp')";
        $conn->query($sql);
    } catch (Exception $e) {
        // If logging fails, just continue without crashing
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// Function to backup database using PHP's native functionality only
function backupDatabase() {
    global $host, $username, $password, $database, $conn;
    
    // Create backups directory if it doesn't exist
    $backup_dir = dirname(__DIR__) . '/backups';
    if (!file_exists($backup_dir)) {
        if (!mkdir($backup_dir, 0755, true)) {
            error_log("Failed to create backup directory: $backup_dir");
            return ['success' => false, 'error' => "Failed to create backup directory"];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($backup_dir)) {
        error_log("Backup directory is not writable: $backup_dir");
        // Try to make it writable
        chmod($backup_dir, 0755);
        if (!is_writable($backup_dir)) {
            return ['success' => false, 'error' => "Backup directory is not writable"];
        }
    }
    
    $timestamp = date("Y-m-d-H-i-s");
    $backup_file = $backup_dir . '/backup_' . $timestamp . '.sql';
    $backup_filename = 'backup_' . $timestamp . '.sql';
    
    // Use PHP's native functionality for backup
    try {
        $tables = [];
        $result = $conn->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $sql = "-- Barangay Management System Database Backup\n";
        $sql .= "-- Generated: " . date("Y-m-d H:i:s") . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        // Export structure and data for each table
        foreach ($tables as $table) {
            // Get table structure
            $result = $conn->query("SHOW CREATE TABLE $table");
            $row = $result->fetch_row();
            $sql .= "-- Table structure for table `$table`\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $row[1] . ";\n\n";
            
            // Get table data
            $result = $conn->query("SELECT * FROM $table");
            if ($result->num_rows > 0) {
                $sql .= "-- Data for table `$table`\n";
                $sql .= "INSERT INTO `$table` VALUES ";
                
                $first_row = true;
                while ($row = $result->fetch_assoc()) {
                    if (!$first_row) {
                        $sql .= ",\n";
                    } else {
                        $first_row = false;
                    }
                    
                    $sql .= "(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $conn->real_escape_string($value) . "'";
                        }
                    }
                    $sql .= implode(", ", $values);
                    $sql .= ")";
                }
                $sql .= ";\n\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Write SQL to file
        if (file_put_contents($backup_file, $sql) !== false) {
            return ['success' => true, 'filename' => $backup_filename, 'method' => 'php-native'];
        } else {
            error_log("Failed to write backup file using PHP native method");
            return ['success' => false, 'error' => "Failed to write backup file"];
        }
    } catch (Exception $e) {
        error_log("Exception during PHP native backup: " . $e->getMessage());
        return ['success' => false, 'error' => "PHP native backup failed: " . $e->getMessage()];
    }
}
?>
