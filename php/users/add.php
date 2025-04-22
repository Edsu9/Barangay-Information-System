<?php
require_once '../config.php';
requireLogin();

// Redirect non-admin users
if ($_SESSION['role'] != 'admin') {
    header("Location: ../../dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $role = sanitize($_POST['role']);
    $email = sanitize($_POST['email']);
    $contact_number = sanitize($_POST['contact_number']);
    
    // Validate username (check if already exists)
    $check_username = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($check_username);
    
    if ($result->num_rows > 0) {
        $error = "Username already exists. Please choose a different username.";
    } else {
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
            
            // Insert user data
            $sql = "INSERT INTO users (username, password, full_name, role, email, contact_number) 
                    VALUES ('$username', '$hashed_password', '$full_name', '$role', '$email', '$contact_number')";
            
            if ($conn->query($sql) === TRUE) {
                // Log activity
                logActivity($_SESSION['user_id'], "Added new user: $username ($role)");
                
                // Redirect to users page with success message
                header("Location: ../../users.php?success=1");
                exit();
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

// Include the header
$pageTitle = "Add User";
include '../includes/header.php';
?>

<!-- Add User Form -->
<div class="form-container">
    <div class="form-title">User Information</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <!-- User Information -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required data-password-strength="#passwordStrength">
                    <div id="passwordStrength"></div>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="admin">Administrator</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control">
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save User
            </button>
            <a href="../../users.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<script src="../../assets/js/password-validation.js"></script>
<?php include '../includes/footer.php'; ?>
