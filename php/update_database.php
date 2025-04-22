<?php
require_once 'config.php';

// Add profile_image column to residents table if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM residents LIKE 'profile_image'");
if ($check_column->num_rows == 0) {
    $alter_table = "ALTER TABLE residents ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER last_name";
    if ($conn->query($alter_table) === TRUE) {
        echo "Profile image column added successfully.<br>";
    } else {
        echo "Error adding profile image column: " . $conn->error . "<br>";
    }
}

// Add additional profiling fields if they don't exist
$additional_fields = [
    "id_card_no" => "VARCHAR(50)",
    "blood_type" => "VARCHAR(5)",
    "height" => "DECIMAL(5,2)",
    "weight" => "DECIMAL(5,2)",
    "philhealth_no" => "VARCHAR(20)",
    "sss_no" => "VARCHAR(20)",
    "tin_no" => "VARCHAR(20)",
    "pagibig_no" => "VARCHAR(20)",
    "emergency_contact_name" => "VARCHAR(100)",
    "emergency_contact_number" => "VARCHAR(20)",
    "emergency_contact_relationship" => "VARCHAR(50)"
];

foreach ($additional_fields as $field => $type) {
    $check_column = $conn->query("SHOW COLUMNS FROM residents LIKE '$field'");
    if ($check_column->num_rows == 0) {
        $alter_table = "ALTER TABLE residents ADD COLUMN $field $type DEFAULT NULL";
        if ($conn->query($alter_table) === TRUE) {
            echo "$field column added successfully.<br>";
        } else {
            echo "Error adding $field column: " . $conn->error . "<br>";
        }
    }
}

echo "Database update completed!";
?>
