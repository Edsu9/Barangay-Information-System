<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../../residents.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get resident information for logging
$resident_query = "SELECT first_name, last_name, household_id, is_head_of_family FROM residents WHERE id = $id";
$resident_result = $conn->query($resident_query);

if ($resident_result && $resident_result->num_rows > 0) {
    $resident = $resident_result->fetch_assoc();
    $resident_name = $resident['first_name'] . ' ' . $resident['last_name'];
    
    // Check if resident is head of family
    if ($resident['is_head_of_family'] && $resident['household_id']) {
        // Update household to remove head of family reference
        $update_household = "UPDATE households SET head_of_family_id = NULL WHERE head_of_family_id = $id";
        $conn->query($update_household);
    }
    
    // Delete the resident
    $sql = "DELETE FROM residents WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        // Log activity
        logActivity($_SESSION['user_id'], "Deleted resident: $resident_name (ID: $id)");
        
        // Redirect to residents page with success message
        header("Location: ../../residents.php?success=3");
        exit();
    } else {
        // Redirect with error message
        header("Location: ../../residents.php?error=1");
        exit();
    }
} else {
    // Redirect with error message
    header("Location: ../../residents.php?error=2");
    exit();
}
?>
