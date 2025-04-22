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

$id = sanitize($_GET['id']);

// Get user information
$user_query = "SELECT * FROM users WHERE id = $id";
$user_result = $conn->query($user_query);

if (!$user_result || $user_result->num_rows == 0) {
    header("Location: ../../users.php?error=1");
    exit();
}

$user = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $username = sanitize($_POST['username']);
    $full_name = sanitize($_POST['full_name']);
    $role = sanitize($_POST['role']);
    $email = sanitize($_POST['email']);
    $contact_number = sanitize($_POST['contact_number']);
    
    // Check if username exists (if changed)
    if ($username != $user['username']) {
        $check_username = "SELECT * FROM users WHERE username = '$username' AND id != $id";
        $result = $conn->query($check_username);
        
        if ($result->num_rows > 0) {
            $error = "Username already exists. Please choose a different username.";
        }
    }
    
    if (!isset($error)) {
        // Update user data
        $sql = "UPDATE users SET 
                username = '$username', 
                full_name = '$full_name', 
                role = '$role', 
                email = '$email', 
                contact_number = '$contact_number' 
                WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            // Log activity
            logActivity($_SESSION['user_id'], "Updated user: $username");
            
            // Redirect to users page with success message
            header("Location: ../../users.php?success=2");
            exit();
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Include the header
$pageTitle = "Edit User";
include '../includes/header.php';
?>

<!-- Edit User Form -->
<div class="form-container">
    <div class="form-title">Edit User Information</div>
    
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
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="staff" <?php echo ($user['role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" value="<?php echo $user['contact_number']; ?>">
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update User
            </button>
            <a href="../../users.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
