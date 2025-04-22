<?php
// Include database configuration
require_once 'config.php';

// Create password_reset_tokens table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Password reset tokens table created successfully or already exists<br>";
} else {
    echo "Error creating password reset tokens table: " . $conn->error . "<br>";
}
?>
