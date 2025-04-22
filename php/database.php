<?php
// Create database and tables if they don't exist
require_once 'config.php';

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS barangay_system";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("barangay_system");

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
    echo "Users table created successfully or already exists<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create residents table
$sql = "CREATE TABLE IF NOT EXISTS residents (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    resident_id VARCHAR(20) NOT NULL UNIQUE,
    household_id INT(11),
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    civil_status ENUM('Single', 'Married', 'Widowed', 'Divorced', 'Separated') NOT NULL,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    occupation VARCHAR(100),
    educational_attainment VARCHAR(100),
    address TEXT NOT NULL,
    is_voter BOOLEAN DEFAULT FALSE,
    is_head_of_family BOOLEAN DEFAULT FALSE,
    nationality VARCHAR(50) DEFAULT 'Filipino',
    religion VARCHAR(100),
    sector ENUM('Senior Citizen', 'PWD', 'Solo Parent', 'Indigenous People', 'Youth', 'None') DEFAULT 'None',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Residents table created successfully or already exists<br>";
} else {
    echo "Error creating residents table: " . $conn->error . "<br>";
}

// Create households table
$sql = "CREATE TABLE IF NOT EXISTS households (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    household_id VARCHAR(20) NOT NULL UNIQUE,
    address TEXT NOT NULL,
    family_name VARCHAR(100) NOT NULL,
    head_of_family_id INT(11),
    monthly_income DECIMAL(10,2),
    number_of_members INT(3) DEFAULT 1,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (head_of_family_id) REFERENCES residents(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Households table created successfully or already exists<br>";
} else {
    echo "Error creating households table: " . $conn->error . "<br>";
}

// Add foreign key to residents table
$sql = "ALTER TABLE residents ADD FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE SET NULL";
if ($conn->query($sql) === TRUE) {
    echo "Foreign key added to residents table successfully or already exists<br>";
} else {
    echo "Error adding foreign key to residents table: " . $conn->error . "<br>";
}

// Create documents table
$sql = "CREATE TABLE IF NOT EXISTS documents (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    document_id VARCHAR(20) NOT NULL UNIQUE,
    resident_id INT(11) NOT NULL,
    document_type ENUM('Barangay Clearance', 'Certificate of Indigency', 'Certificate of Residency', 'Business Permit', 'Other') NOT NULL,
    purpose TEXT NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE,
    status ENUM('Pending', 'Issued', 'Cancelled', 'Expired') DEFAULT 'Pending',
    issued_by INT(11),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Documents table created successfully or already exists<br>";
} else {
    echo "Error creating documents table: " . $conn->error . "<br>";
}

// Create blotter table
$sql = "CREATE TABLE IF NOT EXISTS blotter (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    blotter_id VARCHAR(20) NOT NULL UNIQUE,
    complainant_id INT(11),
    respondent_id INT(11),
    incident_type VARCHAR(100) NOT NULL,
    incident_date DATE NOT NULL,
    incident_location TEXT NOT NULL,
    incident_details TEXT NOT NULL,
    status ENUM('Pending', 'Ongoing', 'Resolved', 'Cancelled') DEFAULT 'Pending',
    resolution TEXT,
    handled_by INT(11),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complainant_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (respondent_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Blotter table created successfully or already exists<br>";
} else {
    echo "Error creating blotter table: " . $conn->error . "<br>";
}

// Create activity_logs table
$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    action TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Activity logs table created successfully or already exists<br>";
} else {
    echo "Error creating activity logs table: " . $conn->error . "<br>";
}

// Create backup_logs table
$sql = "CREATE TABLE IF NOT EXISTS backup_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    backup_file VARCHAR(255) NOT NULL,
    backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    backup_by INT(11),
    status ENUM('Success', 'Failed') NOT NULL,
    FOREIGN KEY (backup_by) REFERENCES users(id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Backup logs table created successfully or already exists<br>";
} else {
    echo "Error creating backup logs table: " . $conn->error . "<br>";
}

// Create password_reset_tokens table
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

// Insert default admin user if not exists
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Create default admin user
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('admin', '$default_password', 'System Administrator', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Default admin user created successfully<br>";
    } else {
        echo "Error creating default admin user: " . $conn->error . "<br>";
    }
} else {
    echo "Default admin user already exists<br>";
}

echo "Database setup completed!";
?>
