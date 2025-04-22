<?php
require_once 'php/config.php';
requireLogin();

// Redirect non-admin users
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_records_query = "SELECT COUNT(*) as count FROM users $search_condition";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get users
$users_query = "SELECT * FROM users $search_condition ORDER BY created_at DESC LIMIT $offset, $records_per_page";
$users = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Barangay Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/modules.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="assets/images/barangay-logo.png" alt="Barangay Logo">
                <h2>Barangay System</h2>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="residents.php" class="menu-item">
                    <i class="fas fa-users"></i> Residents
                </a>
                <a href="households.php" class="menu-item">
                    <i class="fas fa-home"></i> Households
                </a>
                <a href="documents.php" class="menu-item">
                    <i class="fas fa-file-alt"></i> Documents
                </a>
                <a href="blotter.php" class="menu-item">
                    <i class="fas fa-book"></i> Blotter
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="users.php" class="menu-item active">
                    <i class="fas fa-user-shield"></i> Users
                </a>
                <a href="backup.php" class="menu-item">
                    <i class="fas fa-database"></i> Backup
                </a>
                <?php endif; ?>
                <a href="php/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <h2>Users Management</h2>
                <div class="user-info">
                    <img src="assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Search and Add User -->
            <div class="form-container">
    <div class="form-title">
        <i class="fas fa-user-shield"></i> Users Management
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> 
            <?php 
                switch($_GET['success']) {
                    case 1:
                        echo "User added successfully!";
                        break;
                    case 2:
                        echo "User updated successfully!";
                        break;
                    case 3:
                        echo "Password reset successfully!";
                        break;
                    default:
                        echo "Operation completed successfully!";
                }
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> 
            <?php 
                switch($_GET['error']) {
                    case 1:
                        echo "An error occurred while processing your request.";
                        break;
                    case 2:
                        echo "Cannot delete your own account.";
                        break;
                    case 4:
                        echo "User not found.";
                        break;
                    default:
                        echo "An error occurred.";
                }
            ?>
        </div>
    <?php endif; ?>
    
    <div class="module-actions" style="margin-bottom: 1.5rem; justify-content: space-between;">
        <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
            <input type="text" name="search" placeholder="Search by username, full name, or email..." class="form-control" value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search)): ?>
            <a href="users.php" class="btn btn-warning">
                <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
        <a href="php/users/add.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
                
                <!-- Users Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Contact Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($users && $users->num_rows > 0): ?>
                                <?php while($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['full_name']; ?></td>
                                    <td>
                                        <?php if($user['role'] == 'admin'): ?>
                                            <span class="badge badge-danger">Administrator</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Staff</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['contact_number']; ?></td>
                                    <td class="action-buttons">
                                        <a href="php/users/edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="php/delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="reset_password.php?id=<?php echo $user['id']; ?>" class="btn btn-info" onclick="return confirm('Are you sure you want to reset the password for this user?')">
                                            <i class="fas fa-key"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div style="display: flex; justify-content: center; margin-top: 1rem;">
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" class="btn btn-primary">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-warning'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" class="btn btn-primary">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
