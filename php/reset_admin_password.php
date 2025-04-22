<?php
// This script resets the admin password to 'admin123'
// Use this if you've forgotten your admin password

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

// Reset admin password to 'admin123'
$new_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = '$new_password' WHERE username = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "Admin password has been reset successfully!<br>";
    echo "Username: admin<br>";
    echo "New Password: admin123<br>";
    echo "<a href='../index.html'>Go to Login Page</a>";
} else {
    echo "Error resetting password: " . $conn->error;
}

$conn->close();
?>
