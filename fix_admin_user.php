<?php
// This script will fix the admin user by recreating it with the correct password hash
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Admin User Fix Tool</h1>";

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "barangay_system";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("<p style='color: red;'>Connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color: green;'>Database connection successful!</p>";

// First, check if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>Users table does not exist! Creating it now...</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'staff') NOT NULL,
        email VARCHAR(100),
        contact_number VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>Users table created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating users table: " . $conn->error . "</p>";
        exit;
    }
}

// Now, check if admin user exists
$admin_check = $conn->query("SELECT * FROM users WHERE username = 'admin'");

// Generate a proper password hash for 'admin123'
$correct_hash = password_hash('admin123', PASSWORD_DEFAULT);

if ($admin_check->num_rows > 0) {
    // Admin exists, update the password
    echo "<p>Admin user exists. Updating password...</p>";
    
    $update_sql = "UPDATE users SET password = '$correct_hash' WHERE username = 'admin'";
    
    if ($conn->query($update_sql) === TRUE) {
        echo "<p style='color: green;'>Admin password updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating admin password: " . $conn->error . "</p>";
    }
} else {
    // Admin doesn't exist, create it
    echo "<p>Admin user does not exist. Creating admin user...</p>";
    
    $insert_sql = "INSERT INTO users (username, password, full_name, role) 
                  VALUES ('admin', '$correct_hash', 'System Administrator', 'admin')";
    
    if ($conn->query($insert_sql) === TRUE) {
        echo "<p style='color: green;'>Admin user created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error creating admin user: " . $conn->error . "</p>";
    }
}

// Verify the admin user
$verify_sql = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($verify_sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'>Admin user verified in database.</p>";
    echo "<p>Username: admin</p>";
    echo "<p>Password: admin123</p>";
    
    // Test password verification
    if (password_verify('admin123', $user['password'])) {
        echo "<p style='color: green;'>Password verification successful!</p>";
    } else {
        echo "<p style='color: red;'>Password verification failed! Hash in database may be incorrect.</p>";
        echo "<p>Current hash in database: " . $user['password'] . "</p>";
        echo "<p>Expected hash format: " . $correct_hash . "</p>";
    }
} else {
    echo "<p style='color: red;'>Failed to verify admin user!</p>";
}

$conn->close();

echo "<p><a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #1e88e5; color: white; text-decoration: none; border-radius: 5px;'>Try Login Again</a></p>";
?>
