<?php
// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
   header("Location: dashboard.php");
   exit();
}

// Get error information if any
$error_type = isset($_GET['error_type']) ? $_GET['error_type'] : '';
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';
$username = isset($_GET['username']) ? $_GET['username'] : '';

// Check for password reset success message
$success = '';
if (isset($_GET['password_reset']) && $_GET['password_reset'] == 'success') {
   $success = 'Your password has been reset successfully. You can now login with your new password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
   <title>Barangay Management System</title>
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel="stylesheet" href="assets/css/login.css">
   <link rel="stylesheet" href="assets/css/login-validation.css">
   <link rel="stylesheet" href="assets/css/mobile.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   <style>
       /* Additional mobile-friendly login styles */
       @media (max-width: 768px) {
           .login-form {
               width: 90%;
               max-width: 350px;
               padding: 1.5rem;
               border-radius: 15px;
           }
           
           .login-form .logo img {
               width: 70px;
               height: 70px;
           }
           
           .login-form .logo h1 {
               font-size: 1.4rem;
           }
           
           .login-form .tagline {
               font-size: 0.85rem;
           }
           
           .input-group input {
               font-size: 16px; /* Prevent iOS zoom */
               padding: 12px 12px 12px 45px;
           }
           
           .login-btn {
               min-height: 44px;
           }
           
           /* Improve touch targets */
           .forgot-password a {
               display: inline-block;
               padding: 10px;
               min-height: 44px;
               line-height: 24px;
           }
       }
   </style>
</head>
<body>
   <div class="login-container">
       <div class="login-overlay"></div>
       <div class="login-form-container">
           <div class="login-form <?php echo !empty($error_type) ? 'shake' : ''; ?>">
               <div class="logo">
                   <img src="assets/images/barangay-logo.png" alt="Barangay Logo">
                   <h1>Barangay Management System</h1>
                   <p class="tagline">Efficient Community Management</p>
               </div>
               
               <?php if (!empty($success)): ?>
               <div class="alert alert-success">
                   <i class="fas fa-check-circle"></i> <?php echo $success; ?>
               </div>
               <?php endif; ?>
               
               <?php if (!empty($error_message)): ?>
               <div class="alert alert-danger">
                   <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
               </div>
               <?php endif; ?>
               
               <form action="php/login.php" method="POST" id="loginForm">
                   <div class="input-group">
                       <i class="fas fa-user"></i>
                       <input type="text" name="username" id="username" placeholder="Username" 
                              value="<?php echo htmlspecialchars($username); ?>" 
                              class="<?php echo ($error_type == 'username_empty' || $error_type == 'username_not_found') ? 'input-error' : ''; ?>" 
                              required
                              autocomplete="username"
                              aria-label="Username"
                              aria-describedby="username-error">
                       <?php if ($error_type == 'username_empty' || $error_type == 'username_not_found'): ?>
                           <span class="error-text" id="username-error"><?php echo $error_message; ?></span>
                       <?php endif; ?>
                   </div>
                   <div class="input-group">
                       <i class="fas fa-lock"></i>
                       <input type="password" name="password" id="password" placeholder="Password" 
                              class="<?php echo ($error_type == 'password_empty' || $error_type == 'password_incorrect') ? 'input-error' : ''; ?>" 
                              required
                              autocomplete="current-password"
                              aria-label="Password"
                              aria-describedby="password-error">
                       <?php if ($error_type == 'password_empty' || $error_type == 'password_incorrect'): ?>
                           <span class="error-text" id="password-error"><?php echo $error_message; ?></span>
                       <?php endif; ?>
                   </div>
                   <button type="submit" class="login-btn" id="loginBtn">
                       <i class="fas fa-sign-in-alt"></i> Login
                   </button>
                   <div class="forgot-password">
                       <a href="forgot_password.php">Forgot Password?</a>
                   </div>
               </form>
               <div class="login-footer">
                   <p>&copy; <?php echo date('Y'); ?> Barangay Management System</p>
               </div>
           </div>
       </div>
   </div>
   
   <script>
   document.addEventListener('DOMContentLoaded', function() {
       // Client-side validation
       const loginForm = document.getElementById('loginForm');
       const usernameInput = document.getElementById('username');
       const passwordInput = document.getElementById('password');
       const loginBtn = document.getElementById('loginBtn');
       
       // Focus on the first field with error, or the username field by default
       if (document.querySelector('.input-error')) {
           document.querySelector('.input-error').focus();
       } else {
           usernameInput.focus();
       }
       
       loginForm.addEventListener('submit', function(e) {
           let isValid = true;
           
           // Reset previous error states
           usernameInput.classList.remove('input-error');
           passwordInput.classList.remove('input-error');
           
           // Remove existing error messages
           const errorTexts = document.querySelectorAll('.error-text');
           errorTexts.forEach(text => text.remove());
           
           // Validate username
           if (!usernameInput.value.trim()) {
               isValid = false;
               usernameInput.classList.add('input-error');
               const errorSpan = document.createElement('span');
               errorSpan.className = 'error-text';
               errorSpan.id = 'username-error';
               errorSpan.textContent = 'Username is required';
               usernameInput.setAttribute('aria-describedby', 'username-error');
               usernameInput.parentNode.appendChild(errorSpan);
           }
           
           // Validate password
           if (!passwordInput.value.trim()) {
               isValid = false;
               passwordInput.classList.add('input-error');
               const errorSpan = document.createElement('span');
               errorSpan.className = 'error-text';
               errorSpan.id = 'password-error';
               errorSpan.textContent = 'Password is required';
               passwordInput.setAttribute('aria-describedby', 'password-error');
               passwordInput.parentNode.appendChild(errorSpan);
           }
           
           if (!isValid) {
               e.preventDefault();
               document.querySelector('.login-form').classList.add('shake');
               
               // Remove shake class after animation completes
               setTimeout(() => {
                   document.querySelector('.login-form').classList.remove('shake');
               }, 500);
               
               // Focus on the first field with error
               document.querySelector('.input-error').focus();
           } else {
               // Show loading state
               loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
               loginBtn.disabled = true;
           }
       });
       
       // Remove error styling on input
       usernameInput.addEventListener('input', function() {
           this.classList.remove('input-error');
           const errorText = this.parentNode.querySelector('.error-text');
           if (errorText) {
               errorText.remove();
           }
       });
       
       passwordInput.addEventListener('input', function() {
           this.classList.remove('input-error');
           const errorText = this.parentNode.querySelector('.error-text');
           if (errorText) {
               errorText.remove();
           }
       });
       
       // Add touch event handling for mobile
       if ('ontouchstart' in window) {
           loginBtn.addEventListener('touchstart', function() {
               this.style.transform = 'scale(0.98)';
           });
           
           loginBtn.addEventListener('touchend', function() {
               this.style.transform = 'scale(1)';
           });
       }
       
       // Focus on username field on page load if empty
       if (!usernameInput.value) {
           usernameInput.focus();
       }
   });
   </script>
</body>
</html>
