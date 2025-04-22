<?php
// This script creates the default admin user if it doesn't exist
// Run this if you're having trouble logging in

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

// Check if admin user exists
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Create default admin user with password 'admin123'
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('admin', '$default_password', 'System Administrator', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Default admin user created successfully!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<a href='../index.html'>Go to Login Page</a>";
    } else {
        echo "Error creating default admin user: " . $conn->error;
    }
} else {
    echo "Admin user already exists!<br>";
    echo "Username: admin<br>";
    echo "If you forgot your password, you can reset it by running:<br>";
    echo "<pre>UPDATE users SET password = '" . password_hash("admin123", PASSWORD_DEFAULT) . "' WHERE username = 'admin';</pre>";
    echo "<a href='../index.html'>Go to Login Page</a>";
}

$conn->close();
?>
