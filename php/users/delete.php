<?php
require_once '../config.php';
requireLogin();

// Ensure only admins can delete users
if ($_SESSION['role'] != 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = sanitize($_GET['id']);
    
    // Don't allow deleting own account
    if ($id == $_SESSION['user_id']) {
        header("Location: ../../users.php?error=2");
        exit();
    }
    
    // Delete the user
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        logActivity($_SESSION['user_id'], "Deleted user ID: $id");
        header("Location: ../../users.php?success=2");
        exit();
    } else {
        header("Location: ../../users.php?error=1");
        exit();
    }
} else {
    header("Location: ../../users.php");
    exit();
}
?>
