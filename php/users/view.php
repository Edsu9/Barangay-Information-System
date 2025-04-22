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

// Get user activity logs
$logs_query = "SELECT * FROM activity_logs WHERE user_id = $id ORDER BY timestamp DESC LIMIT 20";
$logs = $conn->query($logs_query);

// Include the header
$pageTitle = "View User";
include '../includes/header.php';
?>

<!-- User Details -->
<div class="form-container">
    <div class="form-title">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>User Information</span>
            <div>
                <a href="../users/edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="../users/reset_password.php?id=<?php echo $id; ?>" class="btn btn-info">
                    <i class="fas fa-key"></i> Reset Password
                </a>
                <a href="../../users.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
    
    <div class="data-view">
        <div class="data-row">
            <div class="data-label">Username:</div>
            <div class="data-value"><?php echo $user['username']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Full Name:</div>
            <div class="data-value"><?php echo $user['full_name']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Role:</div>
            <div class="data-value">
                <?php if ($user['role'] == 'admin'): ?>
                    <span class="badge badge-danger">Administrator</span>
                <?php else: ?>
                    <span class="badge badge-info">Staff</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Email:</div>
            <div class="data-value"><?php echo $user['email'] ?: 'Not provided'; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Contact Number:</div>
            <div class="data-value"><?php echo $user['contact_number'] ?: 'Not provided'; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Date Added:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($user['created_at'])); ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Last Updated:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($user['updated_at'])); ?></div>
        </div>
    </div>
</div>

<!-- User Activity Logs -->
<div class="form-container">
    <div class="form-title">Recent Activity</div>
    
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($logs && $logs->num_rows > 0): ?>
                    <?php while($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i:s A', strtotime($log['timestamp'])); ?></td>
                        <td><?php echo $log['action']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">No activity logs found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
