<?php
require_once 'config.php';
requireLogin();

// Check if user is admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Check if mode is provided
if (!isset($_GET['mode']) || empty($_GET['mode'])) {
    header("Location: ../backup.php?error=nomode");
    exit();
}

$mode = $_GET['mode'];
$success = false;
$message = '';

// Process based on mode
switch ($mode) {
    case 'all':
        // Delete all logs
        $sql = "DELETE FROM backup_logs";
        if ($conn->query($sql) === TRUE) {
            $success = true;
            $message = "All backup logs cleared successfully.";
            logActivity($_SESSION['user_id'], "Cleared all backup logs");
        } else {
            $message = "Error clearing all backup logs: " . $conn->error;
        }
        break;
        
    case 'failed':
        // Delete only failed logs
        $sql = "DELETE FROM backup_logs WHERE status = 'Failed'";
        if ($conn->query($sql) === TRUE) {
            $success = true;
            $message = "Failed backup logs cleared successfully.";
            logActivity($_SESSION['user_id'], "Cleared failed backup logs");
        } else {
            $message = "Error clearing failed backup logs: " . $conn->error;
        }
        break;
        
    case 'older':
        // Delete logs older than 30 days
        $sql = "DELETE FROM backup_logs WHERE backup_date < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        if ($conn->query($sql) === TRUE) {
            $success = true;
            $message = "Backup logs older than 30 days cleared successfully.";
            logActivity($_SESSION['user_id'], "Cleared backup logs older than 30 days");
        } else {
            $message = "Error clearing older backup logs: " . $conn->error;
        }
        break;
        
    default:
        header("Location: ../backup.php?error=invalidmode");
        exit();
}

// Redirect with appropriate message
if ($success) {
    header("Location: ../backup.php?success=cleared&message=" . urlencode($message));
} else {
    header("Location: ../backup.php?error=clearfailed&message=" . urlencode($message));
}
exit();
?>
