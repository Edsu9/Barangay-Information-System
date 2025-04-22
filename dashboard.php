<?php
require_once 'php/config.php';
requireLogin();

// Get counts for dashboard - with error handling
function safeQuery($conn, $query, $default = 0) {
    try {
        $result = $conn->query($query);
        if ($result) {
            return $result->fetch_assoc()['count'];
        }
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
    }
    return $default;
}

// Check if tables exist before querying
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

// Get counts with safety checks
$resident_count = tableExists($conn, 'residents') ? safeQuery($conn, "SELECT COUNT(*) as count FROM residents") : 0;
$household_count = tableExists($conn, 'households') ? safeQuery($conn, "SELECT COUNT(*) as count FROM households") : 0;
$document_count = tableExists($conn, 'documents') ? safeQuery($conn, "SELECT COUNT(*) as count FROM documents") : 0;
$blotter_count = tableExists($conn, 'blotter') ? safeQuery($conn, "SELECT COUNT(*) as count FROM blotter") : 0;

// Get recent residents
$recent_residents = tableExists($conn, 'residents') ? $conn->query("SELECT * FROM residents ORDER BY created_at DESC LIMIT 5") : null;

// Get recent documents
$recent_documents = null;
if (tableExists($conn, 'documents') && tableExists($conn, 'residents')) {
    $recent_documents = $conn->query("SELECT d.*, r.first_name, r.last_name FROM documents d 
                                     JOIN residents r ON d.resident_id = r.id 
                                     ORDER BY d.created_at DESC LIMIT 5");
}

// Get recent blotter reports
$recent_blotters = tableExists($conn, 'blotter') ? $conn->query("SELECT * FROM blotter ORDER BY created_at DESC LIMIT 5") : null;

// Check if any tables are missing
$missing_tables = [];
if (!tableExists($conn, 'residents')) $missing_tables[] = 'residents';
if (!tableExists($conn, 'households')) $missing_tables[] = 'households';
if (!tableExists($conn, 'documents')) $missing_tables[] = 'documents';
if (!tableExists($conn, 'blotter')) $missing_tables[] = 'blotter';
if (!tableExists($conn, 'activity_logs')) $missing_tables[] = 'activity_logs';
if (!tableExists($conn, 'backup_logs')) $missing_tables[] = 'backup_logs';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barangay Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/modules.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="residents.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Residents</span>
                </a>
                <a href="households.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Households</span>
                </a>
                <a href="documents.php" class="menu-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Documents</span>
                </a>
                <a href="blotter.php" class="menu-item">
                    <i class="fas fa-book"></i>
                    <span>Blotter</span>
                </a>
                <a href="reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="users.php" class="menu-item">
                    <i class="fas fa-user-shield"></i>
                    <span>Users</span>
                </a>
                <a href="backup.php" class="menu-item">
                    <i class="fas fa-database"></i>
                    <span>Backup</span>
                </a>
                <?php endif; ?>
                <a href="php/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
                <div class="user-info">
                    <img src="assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($missing_tables)): ?>
            <div class="alert alert-danger">
                <h3><i class="fas fa-exclamation-triangle"></i> Database Setup Required</h3>
                <p>Some required tables are missing from your database: <strong><?php echo implode(', ', $missing_tables); ?></strong></p>
                <p>Please run the database setup script to create all necessary tables:</p>
                <a href="setup_database.php" class="btn btn-danger">
                    <i class="fas fa-database"></i> Run Database Setup
                </a>
            </div>
            <?php endif; ?>
            
            <!-- Modules Layout -->
            <div class="modules-container">
                <!-- Residents Module -->
                <div class="module-card module-residents">
                    <div class="module-header">
                        <h3>Residents</h3>
                        <div class="module-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="count"><?php echo $resident_count; ?></div>
                            <div class="label">Registered residents</div>
                        </div>
                        <div class="module-actions">
                            <a href="residents.php" class="module-action">
                                <i class="fas fa-list"></i> View All
                            </a>
                            <a href="php/residents/add.php" class="module-action">
                                <i class="fas fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Households Module -->
                <div class="module-card module-households">
                    <div class="module-header">
                        <h3>Households</h3>
                        <div class="module-icon">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="count"><?php echo $household_count; ?></div>
                            <div class="label">Registered households</div>
                        </div>
                        <div class="module-actions">
                            <a href="households.php" class="module-action">
                                <i class="fas fa-list"></i> View All
                            </a>
                            <a href="php/households/add.php" class="module-action">
                                <i class="fas fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Documents Module -->
                <div class="module-card module-documents">
                    <div class="module-header">
                        <h3>Documents</h3>
                        <div class="module-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="count"><?php echo $document_count; ?></div>
                            <div class="label">Issued documents</div>
                        </div>
                        <div class="module-actions">
                            <a href="documents.php" class="module-action">
                                <i class="fas fa-list"></i> View All
                            </a>
                            <a href="php/documents/add.php" class="module-action">
                                <i class="fas fa-plus"></i> Create New
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Blotter Module -->
                <div class="module-card module-blotter">
                    <div class="module-header">
                        <h3>Blotter</h3>
                        <div class="module-icon">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="count"><?php echo $blotter_count; ?></div>
                            <div class="label">Recorded incidents</div>
                        </div>
                        <div class="module-actions">
                            <a href="blotter.php" class="module-action">
                                <i class="fas fa-list"></i> View All
                            </a>
                            <a href="php/blotter/add.php" class="module-action">
                                <i class="fas fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Reports Module -->
                <div class="module-card module-reports">
                    <div class="module-header">
                        <h3>Reports</h3>
                        <div class="module-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="label">Generate various reports and statistics</div>
                        </div>
                        <div class="module-actions">
                            <a href="reports.php" class="module-action">
                                <i class="fas fa-chart-line"></i> View Reports
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <!-- Users Module -->
                <div class="module-card module-users">
                    <div class="module-header">
                        <h3>Users</h3>
                        <div class="module-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="label">Manage system users and permissions</div>
                        </div>
                        <div class="module-actions">
                            <a href="users.php" class="module-action">
                                <i class="fas fa-users-cog"></i> Manage Users
                            </a>
                            <a href="php/users/add.php" class="module-action">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Backup Module -->
                <div class="module-card module-backup">
                    <div class="module-header">
                        <h3>Backup</h3>
                        <div class="module-icon">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                    <div class="module-body">
                        <div class="module-stats">
                            <div class="label">Backup and restore system data</div>
                        </div>
                        <div class="module-actions">
                            <a href="backup.php" class="module-action">
                                <i class="fas fa-download"></i> Backup Data
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Residents -->
            
<div class="form-container recent-section">
    <div class="form-title"><i class="fas fa-users"></i> Recent Residents List</div>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <!-- Removed Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_residents && $recent_residents->num_rows > 0): ?>
                    <?php while($resident = $recent_residents->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $resident['resident_id']; ?></td>
                        <td><?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?></td>
                        <td><?php echo $resident['gender']; ?></td>
                        <td><?php echo $resident['contact_number']; ?></td>
                        <td><?php echo $resident['address']; ?></td>
                        <!-- Removed action buttons -->
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No residents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-all">
        <a href="residents.php">View All Residents <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

            
            <!-- Recent Documents -->
            
<div class="form-container recent-section">
    <div class="form-title"><i class="fas fa-file-alt"></i> Recent Documents List</div>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resident</th>
                    <th>Document Type</th>
                    <th>Issue Date</th>
                    <th>Status</th>
                    <!-- Removed Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_documents && $recent_documents->num_rows > 0): ?>
                    <?php while($document = $recent_documents->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $document['document_id']; ?></td>
                        <td><?php echo $document['first_name'] . ' ' . $document['last_name']; ?></td>
                        <td><?php echo $document['document_type']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($document['issue_date'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($document['status']) == 'issued' ? 'success' : (strtolower($document['status']) == 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo $document['status']; ?>
                            </span>
                        </td>
                        <!-- Removed action buttons -->
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No documents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-all">
        <a href="documents.php">View All Documents <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

            
            <!-- Recent Blotter Reports -->
            
<div class="form-container recent-section">
    <div class="form-title"><i class="fas fa-book"></i> Recent Blotter Reports List</div>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Incident Type</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Status</th>
                    <!-- Removed Actions column -->
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_blotters && $recent_blotters->num_rows > 0): ?>
                    <?php while($blotter = $recent_blotters->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $blotter['blotter_id']; ?></td>
                        <td><?php echo $blotter['incident_type']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($blotter['incident_date'])); ?></td>
                        <td><?php echo $blotter['incident_location']; ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($blotter['status']) == 'resolved' ? 'success' : (strtolower($blotter['status']) == 'pending' ? 'warning' : 'info'); ?>">
                                <?php echo $blotter['status']; ?>
                            </span>
                        </td>
                        <!-- Removed action buttons -->
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No blotter reports found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="view-all">
        <a href="blotter.php">View All Blotter Reports <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

        </div>
    </div>
    
    <!-- Toggle Sidebar Button for Mobile -->
    <div class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </div>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item');
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const dashboardContainer = document.querySelector('.dashboard-container');
            
            // Active menu item
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Toggle sidebar on mobile
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    dashboardContainer.classList.toggle('sidebar-collapsed');
                });
            }
            
            // Auto-collapse sidebar on small screens
            function checkScreenSize() {
                if (window.innerWidth <= 768) {
                    dashboardContainer.classList.add('sidebar-collapsed');
                }
            }
            
            // Check on load
            checkScreenSize();
            
            // Check on resize
            window.addEventListener('resize', checkScreenSize);
        });
    </script>
</body>
</html>
