<?php
require_once '../config.php';
requireLogin();

// Redirect non-admin users
if ($_SESSION['role'] != 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../../users.php");
    exit();
}

$user_id = sanitize($_GET['id']);

// Get user information
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);

if (!$user_result || $user_result->num_rows == 0) {
    header("Location: ../../users.php?error=4");
    exit();
}

$user = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
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
        $error = "Password does not meet requirements:<ul><li>" . implode("</li><li>", $password_errors) . "</li></ul>";
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if ($conn->query($sql) === TRUE) {
            // Log activity
            logActivity($_SESSION['user_id'], "Reset password for user: {$user['username']}");
            
            // Redirect to users page with success message
            header("Location: ../../users.php?success=3");
            exit();
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Include the header
$pageTitle = "Reset Password";
include '../includes/header.php';
?>

<!-- Reset Password Form -->
<div class="form-container">
    <div class="form-title">Reset Password for <?php echo $user['username']; ?></div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="password">New Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required data-password-strength="#passwordStrength">
                    <div id="passwordStrength"></div>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-key"></i> Reset Password
            </button>
            <a href="../../users.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script src="../../assets/js/password-validation.js"></script>
<?php include '../includes/footer.php'; ?>
