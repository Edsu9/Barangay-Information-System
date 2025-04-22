<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Barangay Management System</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/images/barangay-logo.png" alt="Barangay Logo">
                <h2>Barangay System</h2>
            </div>
            <div class="sidebar-menu">
                <a href="../../dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="../../residents.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'residents.php' || strpos($_SERVER['PHP_SELF'], 'residents') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Residents
                </a>
                <a href="../../households.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'households.php' || strpos($_SERVER['PHP_SELF'], 'households') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Households
                </a>
                <a href="../../documents.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'documents.php' || strpos($_SERVER['PHP_SELF'], 'documents') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-file-alt"></i> Documents
                </a>
                <a href="../../blotter.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'blotter.php' || strpos($_SERVER['PHP_SELF'], 'blotter') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i> Blotter
                </a>
                <a href="../../reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="../../users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' || strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-user-shield"></i> Users
                </a>
                <a href="../../backup.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : ''; ?>">
                    <i class="fas fa-database"></i> Backup
                </a>
                <?php endif; ?>
                <a href="../../php/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <h2><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h2>
                <div class="user-info">
                    <img src="../../assets/images/user-avatar.png" alt="User Avatar">
                    <div>
                        <p><?php echo $_SESSION['full_name']; ?></p>
                        <small><?php echo ucfirst($_SESSION['role']); ?></small>
                    </div>
                </div>
            </div>
