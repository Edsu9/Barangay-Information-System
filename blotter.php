<?php
require_once 'php/config.php';
requireLogin();

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "WHERE b.blotter_id LIKE '%$search%' OR b.incident_type LIKE '%$search%' OR b.incident_location LIKE '%$search%'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_records_query = "SELECT COUNT(*) as count FROM blotter b $search_condition";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get blotter reports
$blotters_query = "SELECT b.*, 
                  c.first_name as complainant_first_name, c.last_name as complainant_last_name,
                  r.first_name as respondent_first_name, r.last_name as respondent_last_name
                  FROM blotter b 
                  LEFT JOIN residents c ON b.complainant_id = c.id
                  LEFT JOIN residents r ON b.respondent_id = r.id
                  $search_condition 
                  ORDER BY b.created_at DESC 
                  LIMIT $offset, $records_per_page";
$blotters = $conn->query($blotters_query);

// Check for success or error messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 1:
            $success_msg = "Blotter report added successfully!";
            break;
        case 2:
            $success_msg = "Blotter report updated successfully!";
            break;
        case 3:
            $success_msg = "Blotter report deleted successfully!";
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
    <title>Blotter Reports - Barangay Management System</title>
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
                <a href="blotter.php" class="menu-item active">
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
                <h2>Blotter Reports Management</h2>
                <div class="user-info">
                    <img src="assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Search and Add Blotter -->
            <div class="form-container">
    <div class="form-title">
        <i class="fas fa-book"></i> Blotter Reports Management
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
            <input type="text" name="search" placeholder="Search by blotter ID, incident type, or location..." class="form-control" value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if (!empty($search)): ?>
            <a href="blotter.php" class="btn btn-warning">
                <i class="fas fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
        <a href="php/blotter/add.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Blotter
        </a>
    </div>
                
                <!-- Blotter Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Blotter ID</th>
                                <th>Complainant</th>
                                <th>Respondent</th>
                                <th>Incident Type</th>
                                <th>Incident Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($blotters && $blotters->num_rows > 0): ?>
                                <?php while($blotter = $blotters->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $blotter['blotter_id']; ?></td>
                                    <td>
                                        <?php 
                                        if ($blotter['complainant_id']) {
                                            echo $blotter['complainant_first_name'] . ' ' . $blotter['complainant_last_name'];
                                        } else {
                                            echo "Non-resident";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($blotter['respondent_id']) {
                                            echo $blotter['respondent_first_name'] . ' ' . $blotter['respondent_last_name'];
                                        } else {
                                            echo "Non-resident";
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $blotter['incident_type']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($blotter['incident_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($blotter['status']) == 'resolved' ? 'success' : (strtolower($blotter['status']) == 'pending' ? 'warning' : (strtolower($blotter['status']) == 'ongoing' ? 'info' : 'danger')); ?>">
                                            <?php echo $blotter['status']; ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="php/blotter/view.php?id=<?php echo $blotter['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="php/blotter/edit.php?id=<?php echo $blotter['id']; ?>" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="php/blotter/delete.php?id=<?php echo $blotter['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this blotter report?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No blotter reports found</td>
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
