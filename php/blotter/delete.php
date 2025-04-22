<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../../blotter.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get blotter information for logging
$blotter_query = "SELECT blotter_id FROM blotter WHERE id = $id";
$blotter_result = $conn->query($blotter_query);

if ($blotter_result && $blotter_result->num_rows > 0) {
    $blotter = $blotter_result->fetch_assoc();
    $blotter_id = $blotter['blotter_id'];
    
    // Delete the blotter
    $sql = "DELETE FROM blotter WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        // Log activity
        logActivity($_SESSION['user_id'], "Deleted blotter report: $blotter_id");
        
        // Redirect to blotter page with success message
        header("Location: ../../blotter.php?success=3");
        exit();
    } else {
        // Redirect with error
        header("Location: ../../blotter.php?error=1");
        exit();
    }
} else {
    // Redirect with error
    header("Location: ../../blotter.php?error=1");
    exit();
}
?>
