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
$debug_info = '';

// Check if the password_reset_tokens table exists
$check_table = $conn->query("SHOW TABLES LIKE 'password_reset_tokens'");
if ($check_table->num_rows == 0) {
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = 'error';
    } else {
        // Check if email exists in the database
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $debug_info .= "User found with ID: " . $user['id'] . "<br>";
            
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            
            // Set expiration to 24 hours instead of 1 hour for more flexibility
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $debug_info .= "Token will expire at: " . $expires . "<br>";
            $debug_info .= "Current server time: " . date('Y-m-d H:i:s') . "<br>";
            
            // Delete any existing tokens for this user
            $delete_sql = "DELETE FROM password_reset_tokens WHERE user_id = {$user['id']}";
            if ($conn->query($delete_sql) === TRUE) {
                $debug_info .= "Deleted existing tokens for user.<br>";
            } else {
                $debug_info .= "Error deleting existing tokens: " . $conn->error . "<br>";
            }
            
            // Store token in database
            $sql = "INSERT INTO password_reset_tokens (user_id, token, expires_at) 
                    VALUES ({$user['id']}, '$token', '$expires')";
            
            if ($conn->query($sql) === TRUE) {
                $debug_info .= "Token stored in database.<br>";
                $debug_info .= "Token: " . substr($token, 0, 10) . "...<br>";
                
                // Get the actual URL from the current request
                $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                
                // Extract the base URL (remove "forgot_password.php" from the end)
                $base_url = str_replace("forgot_password.php", "", $current_url);
                
                // Generate the reset link using the base URL
                $reset_link = $base_url . "reset_password.php?token=$token";
                $debug_link = $reset_link . "&debug=1"; // Add debug parameter
                
                $message = "<div style='text-align: left;'>
        <p>A password reset link has been generated. In a production environment, this would be emailed to you.</p>
        <p><strong>Reset Link:</strong> <a href='$reset_link' class='reset-link'>Click here to reset password</a></p>
        <p><strong>This link will expire in 24 hours.</strong></p>
     </div>";
                $message_type = 'success';
                
                // Log activity
                logActivity($user['id'], "Requested password reset");
            } else {
                $message = "Error generating reset token: " . $conn->error;
                $message_type = 'error';
                $debug_info .= "Error storing token: " . $conn->error . "<br>";
            }
        } else {
            // Don't reveal if email exists or not for security
            $message = "If your email is registered in our system, you will receive a password reset link.";
            $message_type = 'info';
            $debug_info .= "Email not found or database error.<br>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Barangay Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
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
        .reset-link {
            word-break: break-all; /* Add this line */
        }
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .input-group.error input {
            border-color: #dc3545;
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
                    <h1>Forgot Password</h1>
                    <p class="tagline">Enter your email to reset your password</p>
                </div>
                
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i> 
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST" novalidate>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email Address" required>
                        <div class="error-message"></div>
                    </div>
                    <button type="submit" class="login-btn">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
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
    
    <script>
        // Simple form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const emailInput = document.querySelector('input[type="email"]');
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validate email
                if (!emailInput.value.trim()) {
                    isValid = false;
                    showError(emailInput, 'Email is required');
                } else if (!isValidEmail(emailInput.value.trim())) {
                    isValid = false;
                    showError(emailInput, 'Please enter a valid email address');
                } else {
                    clearError(emailInput);
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Email input validation on blur
            emailInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError(this, 'Email is required');
                } else if (!isValidEmail(this.value.trim())) {
                    showError(this, 'Please enter a valid email address');
                } else {
                    clearError(this);
                }
            });
            
            // Clear error on input
            emailInput.addEventListener('input', function() {
                clearError(this);
            });
            
            // Helper functions
            function showError(input, message) {
                const inputGroup = input.parentElement;
                let errorElement = inputGroup.querySelector('.error-message');
                
                if (!errorElement) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'error-message';
                    inputGroup.appendChild(errorElement);
                }
                
                errorElement.textContent = message;
                inputGroup.classList.add('error');
                input.setAttribute('aria-invalid', 'true');
            }
            
            function clearError(input) {
                const inputGroup = input.parentElement;
                const errorElement = inputGroup.querySelector('.error-message');
                
                if (errorElement) {
                    errorElement.textContent = '';
                }
                
                inputGroup.classList.remove('error');
                input.setAttribute('aria-invalid', 'false');
            }
            
            function isValidEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
        });
    </script>
</body>
</html>
