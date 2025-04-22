<?php
require_once 'config.php';
requireLogin();

// Ensure only admins can delete backups
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Check if file parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("Location: ../backup.php?error=nofile");
    exit();
}

$filename = basename($_GET['file']); // Use basename to prevent directory traversal
$backup_dir = '../backups';
$filepath = $backup_dir . '/' . $filename;

// Validate file exists and has .sql extension
if (!file_exists($filepath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    header("Location: ../backup.php?error=invalid");
    exit();
}

// Delete the file
if (unlink($filepath)) {
    // Log the action
    logActivity($_SESSION['user_id'], "Deleted backup file: $filename");
    
    // Redirect with success message
    header("Location: ../backup.php?success=deleted");
} else {
    // Redirect with error message
    header("Location: ../backup.php?error=delete_failed");
}
exit();
?>
