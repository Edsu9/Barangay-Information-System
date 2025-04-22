<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id']) || !isset($_GET['household_id'])) {
    header("Location: ../../households.php");
    exit();
}

$resident_id = sanitize($_GET['id']);
$household_id = sanitize($_GET['household_id']);

// Check if resident exists and belongs to the specified household
$check_query = "SELECT * FROM residents WHERE id = $resident_id AND household_id = $household_id";
$check_result = $conn->query($check_query);

if (!$check_result || $check_result->num_rows == 0) {
    header("Location: view.php?id=$household_id&error=1");
    exit();
}

$resident = $check_result->fetch_assoc();

// Update resident to remove from household
$sql = "UPDATE residents SET household_id = NULL, is_head_of_family = 0 WHERE id = $resident_id";

if ($conn->query($sql) === TRUE) {
    // If resident was head of family, update household
    if ($resident['is_head_of_family']) {
        $update_household = "UPDATE households SET head_of_family_id = NULL WHERE head_of_family_id = $resident_id";
        $conn->query($update_household);
    }
    
    // Update household's number of members
    $update_members = "UPDATE households SET number_of_members = (SELECT COUNT(*) FROM residents WHERE household_id = $household_id) WHERE id = $household_id";
    $conn->query($update_members);
    
    // Log activity
    logActivity($_SESSION['user_id'], "Removed resident ID: $resident_id from household ID: $household_id");
    
    // Redirect back to household view
    header("Location: view.php?id=$household_id&success=2");
    exit();
} else {
    // Redirect with error
    header("Location: view.php?id=$household_id&error=2");
    exit();
}
?>
