<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "barangay_system";

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Barangay Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 2rem;
            min-height: 100vh;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-header img {
            width: 100px;
            height: 100px;
            margin-bottom: 1rem;
        }
        .setup-log {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .success {
            color: var(--success);
        }
        .error {
            color: var(--danger);
        }
        .warning {
            color: var(--warning);
        }
        .info {
            color: var(--info);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <img src="assets/images/barangay-logo.png" alt="Barangay Logo">
            <h1>Barangay Management System</h1>
            <h2>Database Setup</h2>
        </div>
        <div class="setup-log">';

// Function to log messages with styling
function logMessage($message, $type = 'info') {
    echo "<div class=\"$type\"><i class=\"fas fa-";
    
    switch ($type) {
        case 'success':
            echo "check-circle";
            break;
        case 'error':
            echo "times-circle";
            break;
        case 'warning':
            echo "exclamation-triangle";
            break;
        default:
            echo "info-circle";
    }
    
    echo "\"></i> $message</div>";
    ob_flush();
    flush();
}

// Create database connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    logMessage("Connection failed: " . $conn->connect_error, 'error');
    exit;
}

logMessage("Database connection successful!", 'success');

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    logMessage("Database '$database' created successfully or already exists", 'success');
} else {
    logMessage("Error creating database: " . $conn->error, 'error');
}

// Select the database
$conn->select_db($database);

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
    logMessage("Users table created successfully or already exists", 'success');
} else {
    logMessage("Error creating users table: " . $conn->error, 'error');
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
    logMessage("Residents table created successfully or already exists", 'success');
} else {
    logMessage("Error creating residents table: " . $conn->error, 'error');
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    logMessage("Households table created successfully or already exists", 'success');
} else {
    logMessage("Error creating households table: " . $conn->error, 'error');
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    logMessage("Documents table created successfully or already exists", 'success');
} else {
    logMessage("Error creating documents table: " . $conn->error, 'error');
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    logMessage("Blotter table created successfully or already exists", 'success');
} else {
    logMessage("Error creating blotter table: " . $conn->error, 'error');
}

// Create activity_logs table
$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    action TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    logMessage("Activity logs table created successfully or already exists", 'success');
} else {
    logMessage("Error creating activity logs table: " . $conn->error, 'error');
}

// Create backup_logs table
$sql = "CREATE TABLE IF NOT EXISTS backup_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    backup_file VARCHAR(255) NOT NULL,
    backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    backup_by INT(11),
    status ENUM('Success', 'Failed') NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    logMessage("Backup logs table created successfully or already exists", 'success');
} else {
    logMessage("Error creating backup logs table: " . $conn->error, 'error');
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
    logMessage("Password reset tokens table created successfully or already exists", 'success');
} else {
    logMessage("Error creating password reset tokens table: " . $conn->error, 'error');
}

// Add foreign keys
logMessage("Adding foreign keys to tables...", 'info');

// Try to add foreign keys, but don't stop if they fail (they might already exist)
$foreign_keys = [
    "ALTER TABLE residents ADD FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE SET NULL",
    "ALTER TABLE households ADD FOREIGN KEY (head_of_family_id) REFERENCES residents(id) ON DELETE SET NULL",
    "ALTER TABLE documents ADD FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE",
    "ALTER TABLE documents ADD FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL",
    "ALTER TABLE blotter ADD FOREIGN KEY (complainant_id) REFERENCES residents(id) ON DELETE SET NULL",
    "ALTER TABLE blotter ADD FOREIGN KEY (respondent_id) REFERENCES residents(id) ON DELETE SET NULL",
    "ALTER TABLE blotter ADD FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE SET NULL",
    "ALTER TABLE activity_logs ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL",
    "ALTER TABLE backup_logs ADD FOREIGN KEY (backup_by) REFERENCES users(id) ON DELETE SET NULL"
];

foreach ($foreign_keys as $sql) {
    try {
        $conn->query($sql);
        logMessage("Foreign key added successfully", 'success');
    } catch (Exception $e) {
        logMessage("Note: " . $e->getMessage(), 'warning');
    }
}

// Insert default admin user if not exists
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($check_admin);

if ($result->num_rows == 0) {
    // Create default admin user
    $default_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, full_name, role) VALUES ('admin', '$default_password', 'System Administrator', 'admin')";
    
    if ($conn->query($sql) === TRUE) {
        logMessage("Default admin user created successfully", 'success');
        logMessage("Username: admin", 'info');
        logMessage("Password: admin123", 'info');
    } else {
        logMessage("Error creating default admin user: " . $conn->error, 'error');
    }
} else {
    logMessage("Default admin user already exists", 'info');
}

// Create backups directory
if (!file_exists('backups')) {
    if (mkdir('backups', 0755, true)) {
        logMessage("Backups directory created successfully", 'success');
    } else {
        logMessage("Failed to create backups directory", 'error');
    }
} else {
    logMessage("Backups directory already exists", 'info');
}

$conn->close();

echo '</div>
        <div style="text-align: center;">
            <h3 class="success"><i class="fas fa-check-circle"></i> Database setup completed!</h3>
            <p>You can now use the Barangay Management System.</p>
            <div style="margin-top: 1.5rem;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Go to Login Page
                </a>
                <a href="dashboard.php" class="btn btn-success">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>';
?>
