<?php
require_once 'php/config.php';
requireLogin();

// Check if user is admin
if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle backup request
$backup_message = '';
$backup_details = '';
if (isset($_POST['backup'])) {
    $backup_result = backupDatabase();
    
    if ($backup_result['success']) {
        $backup_file = $backup_result['filename'];
        $backup_method = $backup_result['method'];
        $backup_message = '<div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Database backup created successfully: ' . $backup_file . ' (Method: ' . $backup_method . ')
        </div>';
        
        // Log backup
        $sql = "INSERT INTO backup_logs (backup_file, backup_by, status) VALUES ('$backup_file', {$_SESSION['user_id']}, 'Success')";
        $conn->query($sql);
        
        // Log activity
        logActivity($_SESSION['user_id'], "Created database backup: $backup_file");
    } else {
        $error_message = $backup_result['error'];
        
        $backup_message = '<div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> Failed to create database backup: ' . $error_message . '
        </div>';
        
        // Log failed backup
        $backup_file = 'backup_' . date("Y-m-d-H-i-s") . '.sql';
        $sql = "INSERT INTO backup_logs (backup_file, backup_by, status) VALUES ('$backup_file', {$_SESSION['user_id']}, 'Failed')";
        $conn->query($sql);
    }
}

// Display success/error messages from delete operation
if (isset($_GET['success']) && $_GET['success'] == 'deleted') {
    $backup_message = '<div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Backup file deleted successfully.
    </div>';
}

// Add this new block for log clearing success messages
if (isset($_GET['success']) && $_GET['success'] == 'cleared') {
    $message = isset($_GET['message']) ? $_GET['message'] : 'Backup logs cleared successfully.';
    $backup_message = '<div class="alert alert-success">
        <i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '
    </div>';
}

if (isset($_GET['error'])) {
    $error_type = $_GET['error'];
    $error_text = "An error occurred.";
    
    if ($error_type == 'nofile') {
        $error_text = "No file specified for deletion.";
    } else if ($error_type == 'invalid') {
        $error_text = "Invalid file specified.";
    } else if ($error_type == 'delete_failed') {
        $error_text = "Failed to delete the backup file. Check file permissions.";
    } else if ($error_type == 'nomode') {
        $error_text = "No mode specified for clearing logs.";
    } else if ($error_type == 'invalidmode') {
        $error_text = "Invalid mode specified for clearing logs.";
    } else if ($error_type == 'clearfailed') {
        $error_text = isset($_GET['message']) ? $_GET['message'] : "Failed to clear backup logs.";
    }
    
    $backup_message = '<div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> ' . $error_text . '
    </div>';
}

// Get backup logs
$backup_logs_query = "SELECT bl.*, u.full_name FROM backup_logs bl 
                     LEFT JOIN users u ON bl.backup_by = u.id 
                     ORDER BY bl.backup_date DESC 
                     LIMIT 10";
$backup_logs = $conn->query($backup_logs_query);

// Check for existing backups
$backup_dir = 'backups';
$existing_backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $existing_backups[] = [
                'name' => $file,
                'size' => filesize("$backup_dir/$file"),
                'date' => filemtime("$backup_dir/$file")
            ];
        }
    }
    // Sort by date (newest first)
    usort($existing_backups, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Database Backup - Barangay Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mobile-friendly styles */
        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
            }
            
            .data-table {
                overflow-x: auto;
            }
            
            .data-table table {
                min-width: 600px; /* Ensure table is scrollable on mobile */
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 5px;
            }
            
            /* Make buttons more touch-friendly */
            .btn {
                padding: 10px;
                min-height: 44px; /* Minimum touch target size */
            }
            
            /* Adjust log clearing buttons for mobile */
            .log-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .log-actions .btn {
                margin-bottom: 8px;
            }
        }
    </style>
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
                <a href="users.php" class="menu-item">
                    <i class="fas fa-user-shield"></i> Users
                </a>
                <a href="backup.php" class="menu-item active">
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
                <h2>Database Backup</h2>
                <div class="user-info">
                    <img src="assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Backup Form -->
            <div class="form-container">
                <div class="form-title">Create Database Backup</div>
                
                <?php echo $backup_message; ?>
                
                <p style="margin-bottom: 1rem;">
                    Creating a backup will save all the data in the system to a SQL file that can be used to restore the system in case of data loss.
                </p>
                
                <form action="" method="POST">
                    <button type="submit" name="backup" class="btn btn-success">
                        <i class="fas fa-download"></i> Create Backup Now
                    </button>
                </form>
            </div>
            
            <!-- Backup Directory Status -->
            <div class="form-container">
                <div class="form-title">System Information</div>
                
                <?php
                $backup_dir = 'backups';
                $backup_dir_exists = is_dir($backup_dir);
                $backup_dir_writable = $backup_dir_exists && is_writable($backup_dir);
                ?>
                
                <div style="margin-bottom: 1rem;">
                    <p>
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                    </p>
                    
                    <p>
                        <strong>Backup Directory:</strong> 
                        <?php if($backup_dir_exists): ?>
                            <span style="color: #4caf50;"><i class="fas fa-check-circle"></i> Exists</span>
                        <?php else: ?>
                            <span style="color: #f44336;"><i class="fas fa-times-circle"></i> Does not exist</span>
                        <?php endif; ?>
                    </p>
                    
                    <p>
                        <strong>Directory Permissions:</strong> 
                        <?php if($backup_dir_writable): ?>
                            <span style="color: #4caf50;"><i class="fas fa-check-circle"></i> Writable</span>
                        <?php else: ?>
                            <span style="color: #f44336;"><i class="fas fa-times-circle"></i> Not writable</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if(!$backup_dir_exists || !$backup_dir_writable): ?>
                <form action="" method="POST">
                    <button type="submit" name="create_backup_dir" class="btn btn-primary">
                        <i class="fas fa-folder-plus"></i> Create Backup Directory
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Existing Backups -->
            <?php if (!empty($existing_backups)): ?>
            <div class="form-container">
                <div class="form-title">Existing Backup Files</div>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($existing_backups as $backup): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($backup['name']); ?></td>
                                <td><?php echo formatFileSize($backup['size']); ?></td>
                                <td><?php echo date('M d, Y h:i A', $backup['date']); ?></td>
                                <td class="action-buttons">
                                    <a href="backups/<?php echo urlencode($backup['name']); ?>" class="btn btn-primary" download>
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                    <a href="php/delete_backup.php?file=<?php echo urlencode($backup['name']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this backup file? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Backup Logs -->
            <div class="form-container">
                <div class="form-title">Recent Backup Logs</div>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Date & Time</th>
                                <th>Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($backup_logs && $backup_logs->num_rows > 0): ?>
                                <?php while($log = $backup_logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $log['backup_file']; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($log['backup_date'])); ?></td>
                                    <td><?php echo $log['full_name']; ?></td>
                                    <td>
                                        <?php if($log['status'] == 'Success'): ?>
                                        <span style="color: #4caf50; font-weight: bold;">
                                            <i class="fas fa-check-circle"></i> Success
                                        </span>
                                        <?php else: ?>
                                        <span style="color: #f44336; font-weight: bold;">
                                            <i class="fas fa-times-circle"></i> Failed
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">No backup logs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
    
                <!-- Add Clear Logs Buttons with improved styling -->
                <div class="log-actions" style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="php/clear_backup_logs.php?mode=failed" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to clear all failed backup logs?')">
                        <i class="fas fa-trash-alt"></i> Clear Failed Logs
                    </a>
                    <a href="php/clear_backup_logs.php?mode=older" class="btn btn-sm btn-info" onclick="return confirm('Are you sure you want to clear backup logs older than 30 days?')">
                        <i class="fas fa-calendar-times"></i> Clear Old Logs
                    </a>
                    <a href="php/clear_backup_logs.php?mode=all" class="btn btn-sm btn-danger" onclick="return confirm('WARNING: This will delete ALL backup logs. This action cannot be undone. Continue?')">
                        <i class="fas fa-trash"></i> Clear All Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toggle Sidebar Button for Mobile -->
    <div class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </div>
    
    <script>
        // Enhanced mobile-friendly sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const dashboardContainer = document.querySelector('.dashboard-container');
            const menuItems = document.querySelectorAll('.menu-item');
            
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
            
            // Close sidebar when clicking a menu item on mobile
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        dashboardContainer.classList.add('sidebar-collapsed');
                    }
                });
            });
            
            // Add touch-friendly enhancements
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                button.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>

<?php
// Handle backup directory creation
if (isset($_POST['create_backup_dir'])) {
    $backup_dir = 'backups';
    if (!is_dir($backup_dir)) {
        if (mkdir($backup_dir, 0755, true)) {
            echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Backup directory created successfully!
            </div>';
            echo '<meta http-equiv="refresh" content="2;url=backup.php">';
        } else {
            echo '<div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Failed to create backup directory. Please check server permissions.
            </div>';
        }
    } else if (!is_writable($backup_dir)) {
        if (chmod($backup_dir, 0755)) {
            echo '<div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Backup directory permissions updated successfully!
            </div>';
            echo '<meta http-equiv="refresh" content="2;url=backup.php">';
        } else {
            echo '<div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Failed to update backup directory permissions. Please set them manually to 755 or 777.
            </div>';
        }
    }
}

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
