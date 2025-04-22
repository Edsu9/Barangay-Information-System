<?php
require_once 'php/config.php';
requireLogin();

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
   $search_condition = "WHERE first_name LIKE '%$search%' OR middle_name LIKE '%$search%' OR last_name LIKE '%$search%' OR resident_id LIKE '%$search%'";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Get total records
$total_records_query = "SELECT COUNT(*) as count FROM residents $search_condition";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get residents
$residents_query = "SELECT * FROM residents $search_condition ORDER BY created_at DESC LIMIT $offset, $records_per_page";
$residents = $conn->query($residents_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Residents - Barangay Management System</title>
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
               <a href="residents.php" class="menu-item active">
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
               <h2>Residents Management</h2>
               <div class="user-info">
                   <img src="assets/images/user-avatar.png" alt="User Avatar">
                   <div>
                       <p><?php echo $_SESSION['full_name']; ?></p>
                       <small><?php echo ucfirst($_SESSION['role']); ?></small>
                   </div>
               </div>
           </div>
           
           <!-- Success and Error Messages -->
           <?php if (isset($_GET['success'])): ?>
               <div class="alert alert-success">
                   <i class="fas fa-check-circle"></i> 
                   <?php 
                       switch($_GET['success']) {
                           case 1:
                               echo "Resident added successfully!";
                               break;
                           case 2:
                               echo "Resident updated successfully!";
                               break;
                           case 3:
                               echo "Resident deleted successfully!";
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
                               echo "Resident not found.";
                               break;
                           default:
                               echo "An error occurred.";
                       }
                   ?>
               </div>
           <?php endif; ?>
           
           <!-- Search and Add Resident -->
           <div class="form-container">
               <div class="form-title">
                   <i class="fas fa-users"></i> Residents Management
               </div>
               
               <div class="module-actions" style="margin-bottom: 1.5rem; justify-content: space-between;">
                   <form action="" method="GET" style="display: flex; gap: 0.5rem; flex: 1;">
                       <input type="text" name="search" placeholder="Search by name or ID..." class="form-control" value="<?php echo $search; ?>">
                       <button type="submit" class="btn btn-primary">
                           <i class="fas fa-search"></i> Search
                       </button>
                       <?php if (!empty($search)): ?>
                       <a href="residents.php" class="btn btn-warning">
                           <i class="fas fa-times"></i> Clear
                       </a>
                       <?php endif; ?>
                   </form>
                   <a href="php/residents/add.php" class="btn btn-success">
                       <i class="fas fa-plus"></i> Add New Resident
                   </a>
               </div>
               
               <!-- Residents Table -->
               <div class="data-table">
                   <table>
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>Name</th>
                               <th>Gender</th>
                               <th>Birth Date</th>
                               <th>Contact</th>
                               <th>Address</th>
                               <th>Actions</th>
                           </tr>
                       </thead>
                       <tbody>
                           <?php if($residents && $residents->num_rows > 0): ?>
                               <?php while($resident = $residents->fetch_assoc()): ?>
                               <tr>
                                   <td><?php echo $resident['resident_id']; ?></td>
                                   <td><?php echo $resident['first_name'] . ' ' . $resident['middle_name'] . ' ' . $resident['last_name']; ?></td>
                                   <td><?php echo $resident['gender']; ?></td>
                                   <td><?php echo date('M d, Y', strtotime($resident['birth_date'])); ?></td>
                                   <td><?php echo $resident['contact_number']; ?></td>
                                   <td><?php echo $resident['address']; ?></td>
                                   <td class="action-buttons">
                                       <a href="php/residents/view.php?id=<?php echo $resident['id']; ?>" class="btn btn-primary">
                                           <i class="fas fa-eye"></i>
                                       </a>
                                       <a href="php/residents/edit.php?id=<?php echo $resident['id']; ?>" class="btn btn-warning">
                                           <i class="fas fa-edit"></i>
                                       </a>
                                       <a href="php/residents/delete.php?id=<?php echo $resident['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this resident?')">
                                           <i class="fas fa-trash"></i>
                                       </a>
                                   </td>
                               </tr>
                               <?php endwhile; ?>
                           <?php else: ?>
                               <tr>
                                   <td colspan="7" style="text-align: center;">No residents found</td>
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
