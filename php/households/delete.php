<?php
require_once '../config.php';
requireLogin();

if (isset($_GET['id'])) {
    $id = sanitize($_GET['id']);
    
    // Update any residents in this household to have no household
    $update_residents = "UPDATE residents SET household_id = NULL, is_head_of_family = 0 WHERE household_id = $id";
    $conn->query($update_residents);
    
    // Delete the household
    $sql = "DELETE FROM households WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        logActivity($_SESSION['user_id'], "Deleted household ID: $id");
        header("Location: ../../households.php?success=2");
        exit();
    } else {
        header("Location: ../../households.php?error=1");
        exit();
    }
} else {
    header("Location: ../../households.php");
    exit();
}
?>
