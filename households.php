<?php
require_once 'php/config.php';
requireLogin();

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE family_name LIKE '%$search%' OR household_id LIKE '%$search%' OR address LIKE '%$search%'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_records_query = "SELECT COUNT(*) as count FROM households $search_condition";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get households
$households_query = "SELECT h.*, r.first_name, r.last_name 
                    FROM households h 
                    LEFT JOIN residents r ON h.head_of_family_id = r.id 
                    $search_condition 
                    ORDER BY h.created_at DESC 
                    LIMIT $offset, $records_per_page";
$households = $conn->query($households_query);

// Check for success or error messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            $success_msg = "Household added successfully!";
            break;
        case 2:
            $success_msg = "Household updated successfully!";
            break;
        case 3:
            $success_msg = "Household deleted successfully!";
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 1:
            $error_msg = "An error occurred. Please try again.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Households - Barangay Management System</title>
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
                <a href="households.php" class="menu-item active">
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
                <a href="users.php" class="menu-item">
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
                <h2>Households Management</h2>
                <div class="user-info">
                    <img src="assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Search and Add Household -->
            <div class="form-container">
                <div class="form-title">
                    <i class="fas fa-home"></i> Households Management
                </div>
                
                <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
                <?php endif; ?>
                
                <div class="module-actions" style="margin-bottom: 1.5rem; justify-content: space-between;">
                    <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
                        <input type="text" name="search" placeholder="Search by family name, household ID, or address..." class="form-control" value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if (!empty($search)): ?>
                        <a href="households.php" class="btn btn-warning">
                            <i class="fas fa-times"></i> Clear
                        </a>
                        <?php endif; ?>
                    </form>
                    <a href="php/households/add.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New Household
                    </a>
                </div>
                
                <!-- Households Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Family Name</th>
                                <th>Head of Family</th>
                                <th>Address</th>
                                <th>Members</th>
                                <th>Monthly Income</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($households && $households->num_rows > 0): ?>
                                <?php while($household = $households->fetch_assoc()): ?>
                                <?php
                                    // Get number of members
                                    $members_query = "SELECT COUNT(*) as count FROM residents WHERE household_id = {$household['id']}";
                                    $members_result = $conn->query($members_query);
                                    $members_count = $members_result->fetch_assoc()['count'];
                                ?>
                                <tr>
                                    <td><?php echo $household['household_id']; ?></td>
                                    <td><?php echo $household['family_name']; ?></td>
                                    <td>
                                        <?php if ($household['head_of_family_id']): ?>
                                            <?php echo $household['first_name'] . ' ' . $household['last_name']; ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Not Set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $household['address']; ?></td>
                                    <td><?php echo $members_count; ?></td>
                                    <td><?php echo '₱ ' . number_format($household['monthly_income'], 2); ?></td>
                                    <td class="action-buttons">
                                        <a href="php/households/view.php?id=<?php echo $household['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="php/households/edit.php?id=<?php echo $household['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="php/households/delete.php?id=<?php echo $household['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this household?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No households found</td>
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
