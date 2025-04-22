<?php
// Start session
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
   header("Location: dashboard.php");
   exit();
}

// Include database configuration
require_once 'php/config.php';

$message = '';
$message_type = '';
$valid_token = false;
$token = '';
$debug_info = '';

// Check if token is provided
if (isset($_GET['token'])) {
   $token = sanitize($_GET['token']);
   $debug_info .= "Token received: " . substr($token, 0, 10) . "...<br>";
   
   // Make sure the password_reset_tokens table exists
   $table_check = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
   if ($table_check->num_rows == 0) {
       // Create the table if it doesn't exist
       $create_table = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
           id INT(11) AUTO_INCREMENT PRIMARY KEY,
           user_id INT(11) NOT NULL,
           token VARCHAR(255) NOT NULL,
           expires_at DATETIME NOT NULL,
           used TINYINT(1) DEFAULT 0,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
       )";
       
       if ($conn->query($create_table) === TRUE) {
           $debug_info .= "Created password_reset_tokens table.<br>";
       } else {
           $debug_info .= "Error creating table: " . $conn->error . "<br>";
       }
   } else {
       $debug_info .= "Password reset tokens table exists.<br>";
   }
   
   // Check if token exists and is not expired
   $sql = "SELECT * FROM password_reset_tokens WHERE token = '$token'";
   $result = $conn->query($sql);
   
   if ($result) {
       if ($result->num_rows > 0) {
           $token_data = $result->fetch_assoc();
           $debug_info .= "Token found in database. Expires at: " . $token_data['expires_at'] . "<br>";
           $debug_info .= "Current server time: " . date('Y-m-d H:i:s') . "<br>";
           
           // Check if token is used
           if ($token_data['used'] == 1) {
               $message = "This reset link has already been used. Please request a new password reset link.";
               $message_type = 'error';
               $debug_info .= "Token has been used.<br>";
           } 
           // Check if token is expired
           else if (strtotime($token_data['expires_at']) < time()) {
               $message = "This reset link has expired. Please request a new password reset link.";
               $message_type = 'error';
               $debug_info .= "Token expired. Expiry: " . strtotime($token_data['expires_at']) . ", Current: " . time() . "<br>";
           } 
           // Token is valid
           else {
               $valid_token = true;
               $debug_info .= "Token is valid.<br>";
           }
       } else {
           $message = "Invalid reset link. Please request a new password reset link.";
           $message_type = 'error';
           $debug_info .= "Token not found in database.<br>";
       }
   } else {
       $message = "Error checking token: " . $conn->error;
       $message_type = 'error';
       $debug_info .= "Database error: " . $conn->error . "<br>";
   }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['token'])) {
   $password = $_POST['password'];
   $confirm_password = $_POST['confirm_password'];
   $token = sanitize($_POST['token']);
   $debug_info .= "Form submitted with token: " . substr($token, 0, 10) . "...<br>";
   
   // Verify token again
   $sql = "SELECT * FROM password_reset_tokens WHERE token = '$token'";
   $result = $conn->query($sql);
   
   if ($result && $result->num_rows > 0) {
       $token_data = $result->fetch_assoc();
       $debug_info .= "Token found for form submission. Expires at: " . $token_data['expires_at'] . "<br>";
       
       // Check if token is used
       if ($token_data['used'] == 1) {
           $message = "This reset link has already been used. Please request a new password reset link.";
           $message_type = 'error';
           $debug_info .= "Token has been used.<br>";
       } 
       // Check if token is expired
       else if (strtotime($token_data['expires_at']) < time()) {
           $message = "This reset link has expired. Please request a new password reset link.";
           $message_type = 'error';
           $debug_info .= "Token expired. Expiry: " . strtotime($token_data['expires_at']) . ", Current: " . time() . "<br>";
       } 
       // Token is valid
       else {
           $user_id = $token_data['user_id'];
           $debug_info .= "Token is valid for user ID: " . $user_id . "<br>";
           
           // Validate password strength
           $password_errors = [];
           if (strlen($password) < 8) {
               $password_errors[] = "Password must be at least 8 characters long";
           }
           if (!preg_match('/[a-z]/', $password)) {
               $password_errors[] = "Password must contain at least one lowercase letter";
           }
           if (!preg_match('/[A-Z]/', $password)) {
               $password_errors[] = "Password must contain at least one uppercase letter";
           }
           if (!preg_match('/[0-9]/', $password)) {
               $password_errors[] = "Password must contain at least one number";
           }
           if (!preg_match('/[^A-Za-z0-9]/', $password)) {
               $password_errors[] = "Password must contain at least one special character";
           }
           
           if (!empty($password_errors)) {
               $message = "Password does not meet requirements:<ul><li>" . implode("</li><li>", $password_errors) . "</li></ul>";
               $message_type = 'error';
               $valid_token = true; // Keep form visible
               $debug_info .= "Password doesn't meet requirements.<br>";
           } else if ($password !== $confirm_password) {
               $message = "Passwords do not match.";
               $message_type = 'error';
               $valid_token = true; // Keep form visible
               $debug_info .= "Passwords don't match.<br>";
           } else {
               // Hash the password
               $hashed_password = password_hash($password, PASSWORD_DEFAULT);
               
               // Update user password
               $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
               
               if ($conn->query($sql) === TRUE) {
                   // Mark token as used
                   $sql = "UPDATE password_reset_tokens SET used = 1 WHERE token = '$token'";
                   $conn->query($sql);
                   
                   // Log activity
                   logActivity($user_id, "Reset password");
                   
                   $message = "Password has been reset successfully. You can now <a href='index.php'>login</a> with your new password.";
                   $message_type = 'success';
                   $valid_token = false; // Hide form after successful reset
                   $debug_info .= "Password reset successful.<br>";
               } else {
                   $message = "Error resetting password: " . $conn->error;
                   $message_type = 'error';
                   $valid_token = true; // Keep form visible
                   $debug_info .= "Error updating password: " . $conn->error . "<br>";
               }
           }
       }
   } else {
       $message = "Invalid or expired token. Please request a new password reset link.";
       $message_type = 'error';
       $debug_info .= "Token not found for form submission.<br>";
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reset Password - Barangay Management System</title>
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel="stylesheet" href="assets/css/login.css">
   <link rel="stylesheet" href="assets/css/password-validation.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
       .debug-info {
           background-color: #f8f9fa;
           border: 1px solid #dee2e6;
           border-radius: 5px;
           padding: 10px;
           margin-top: 20px;
           font-family: monospace;
           font-size: 12px;
           color: #6c757d;
       }
   </style>
</head>
<body>
   <div class="login-container">
       <div class="login-overlay"></div>
       <div class="login-form-container">
           <div class="login-form">
               <div class="logo">
                   <img src="assets/images/barangay-logo.png" alt="Barangay Logo">
                   <h1>Reset Password</h1>
                   <p class="tagline">Enter your new password</p>
               </div>
               
               <?php if (!empty($message)): ?>
               <div class="alert alert-<?php echo $message_type; ?>">
                   <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i> 
                   <div><?php echo $message; ?></div>
               </div>
               <?php endif; ?>
               
               <?php if ($valid_token): ?>
               <form action="reset_password.php" method="POST" novalidate>
                   <input type="hidden" name="token" value="<?php echo $token; ?>">
                   <div class="input-group">
                       <i class="fas fa-lock"></i>
                       <input type="password" name="password" id="password" placeholder="New Password" required data-validate="password">
                   </div>
                   <div class="input-group">
                       <i class="fas fa-lock"></i>
                       <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required data-match="password">
                   </div>
                   <button type="submit" class="login-btn">
                       <i class="fas fa-key"></i> Reset Password
                   </button>
               </form>
               <?php elseif ($message_type !== 'success'): ?>
               <div style="text-align: center; margin: 20px 0;">
                   <a href="forgot_password.php" class="btn btn-primary">
                       <i class="fas fa-envelope"></i> Request New Reset Link
                   </a>
               </div>
               <?php endif; ?>
               
               <div class="login-footer">
                   <a href="index.php" class="back-to-login"><i class="fas fa-arrow-left"></i> Back to Login</a>
               </div>
               
               <?php if (!empty($debug_info) && isset($_GET['debug'])): ?>
               <div class="debug-info">
                   <strong>Debug Information:</strong><br>
                   <?php echo $debug_info; ?>
               </div>
               <?php endif; ?>
           </div>
       </div>
   </div>
   
   <script src="assets/js/password-validation.js"></script>
</body>
</html>
