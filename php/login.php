<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize error variables
    $error_type = "";
    $error_message = "";
    
    // Get form data
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate form data
    if (empty($username)) {
        $error_type = "username_empty";
        $error_message = "Username is required";
    } elseif (empty($password)) {
        $error_type = "password_empty";
        $error_message = "Password is required";
    } else {
        // Check if username exists
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                try {
                    // Log activity
                    logActivity($user['id'], 'Logged in');
                } catch (Exception $e) {
                    error_log("Failed to log activity: " . $e->getMessage());
                }
                
                // Redirect to dashboard
                header("Location: ../dashboard.php");
                exit();
            } else {
                // Username exists but password is incorrect
                $error_type = "password_incorrect";
                $error_message = "The password you entered is incorrect";
            }
        } else {
            // Username doesn't exist
            $error_type = "username_not_found";
            $error_message = "Username not found";
        }
    }
    
    // If there are errors, redirect back to login page with error information
    if (!empty($error_type)) {
        header("Location: ../index.php?error_type=$error_type&error_message=" . urlencode($error_message) . "&username=" . urlencode($username));
        exit();
    }
} else {
    // Not a POST request
    header("Location: ../index.php");
    exit();
}
?>
