<?php
require_once 'php/config.php';
requireLogin();

// Get statistics for reports
$total_residents = $conn->query("SELECT COUNT(*) as count FROM residents")->fetch_assoc()['count'];
$total_households = $conn->query("SELECT COUNT(*) as count FROM households")->fetch_assoc()['count'];
$total_documents = $conn->query("SELECT COUNT(*) as count FROM documents")->fetch_assoc()['count'];
$total_blotters = $conn->query("SELECT COUNT(*) as count FROM blotter")->fetch_assoc()['count'];

// Get gender distribution
$gender_distribution = $conn->query("SELECT gender, COUNT(*) as count FROM residents GROUP BY gender");

// Get age distribution
$age_distribution_query = "
   SELECT 
       CASE 
           WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 17 THEN 'Under 18'
           WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 30 THEN '18-30'
           WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 31 AND 45 THEN '31-45'
           WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 46 AND 60 THEN '46-60'
           ELSE 'Over 60'
       END as age_group,
       COUNT(*) as count
   FROM residents
   GROUP BY age_group
   ORDER BY 
       CASE age_group
           WHEN 'Under 18' THEN 1
           WHEN '18-30' THEN 2
           WHEN '31-45' THEN 3
           WHEN '46-60' THEN 4
           WHEN 'Over 60' THEN 5
       END
";
$age_distribution = $conn->query($age_distribution_query);

// Get document types
$document_types = $conn->query("SELECT document_type, COUNT(*) as count FROM documents GROUP BY document_type");

// Get blotter status
$blotter_status = $conn->query("SELECT status, COUNT(*) as count FROM blotter GROUP BY status");

// Get monthly document issuance for the current year
$monthly_documents_query = "
   SELECT 
       MONTH(issue_date) as month,
       COUNT(*) as count
   FROM documents
   WHERE YEAR(issue_date) = YEAR(CURDATE())
   GROUP BY MONTH(issue_date)
   ORDER BY MONTH(issue_date)
";
$monthly_documents = $conn->query($monthly_documents_query);

// Prepare data for charts
$gender_data = [];
$gender_colors = ['Male' => '#4361ee', 'Female' => '#ff6b6b', 'Other' => '#4caf50'];
while($row = $gender_distribution->fetch_assoc()) {
   $gender_data[$row['gender']] = $row['count'];
}

$age_data = [];
$age_colors = ['Under 18' => '#4cc9f0', '18-30' => '#4361ee', '31-45' => '#3a0ca3', '46-60' => '#7209b7', 'Over 60' => '#f72585'];
while($row = $age_distribution->fetch_assoc()) {
   $age_data[$row['age_group']] = $row['count'];
}

$document_type_data = [];
$document_colors = ['Barangay Clearance' => '#0077b6', 'Certificate of Indigency' => '#00b4d8', 'Certificate of Residency' => '#90e0ef', 'Business Permit' => '#0096c7', 'Other' => '#48cae4'];
while($row = $document_types->fetch_assoc()) {
   $document_type_data[$row['document_type']] = $row['count'];
}

$blotter_status_data = [];
$blotter_colors = ['Pending' => '#fca311', 'Ongoing' => '#3a86ff', 'Resolved' => '#2a9d8f', 'Cancelled' => '#e63946'];
while($row = $blotter_status->fetch_assoc()) {
   $blotter_status_data[$row['status']] = $row['count'];
}

$monthly_document_data = array_fill(1, 12, 0);
while($row = $monthly_documents->fetch_assoc()) {
   $monthly_document_data[$row['month']] = $row['count'];
}

// Get month names for chart labels
$month_names = [];
for ($i = 1; $i <= 12; $i++) {
    $month_names[] = date('F', mktime(0, 0, 0, $i, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reports - Barangay Management System</title>
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel="stylesheet" href="assets/css/reports.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
               <a href="reports.php" class="menu-item active">
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
               <h2><i class="fas fa-chart-bar"></i> Reports and Statistics</h2>
               <div class="user-info">
                   <img src="assets/images/user-avatar.png" alt="User Avatar">
                   <div>
                       <p><?php echo $_SESSION['full_name']; ?></p>
                       <small><?php echo ucfirst($_SESSION['role']); ?></small>
                   </div>
               </div>
           </div>
           
           <!-- Summary Cards -->
           <div class="dashboard-cards">
               <div class="stat-card residents">
                   <div class="card-header">
                       <h3>Total Residents</h3>
                       <div class="card-icon">
                           <i class="fas fa-users"></i>
                       </div>
                   </div>
                   <div class="card-value"><?php echo $total_residents; ?></div>
                   <div class="card-label">Registered residents</div>
               </div>
               
               <div class="stat-card households">
                   <div class="card-header">
                       <h3>Total Households</h3>
                       <div class="card-icon">
                           <i class="fas fa-home"></i>
                       </div>
                   </div>
                   <div class="card-value"><?php echo $total_households; ?></div>
                   <div class="card-label">Registered households</div>
               </div>
               
               <div class="stat-card documents">
                   <div class="card-header">
                       <h3>Documents</h3>
                       <div class="card-icon">
                           <i class="fas fa-file-alt"></i>
                       </div>
                   </div>
                   <div class="card-value"><?php echo $total_documents; ?></div>
                   <div class="card-label">Issued documents</div>
               </div>
               
               <div class="stat-card blotter">
                   <div class="card-header">
                       <h3>Blotter Reports</h3>
                       <div class="card-icon">
                           <i class="fas fa-book"></i>
                       </div>
                   </div>
                   <div class="card-value"><?php echo $total_blotters; ?></div>
                   <div class="card-label">Recorded incidents</div>
               </div>
           </div>
           
           <!-- Charts -->
           <div class="charts-grid">
               <!-- Gender Distribution -->
               <div class="chart-container">
                   <h3><i class="fas fa-venus-mars"></i> Gender Distribution</h3>
                   <div class="chart-wrapper">
                       <canvas id="genderChart"></canvas>
                   </div>
                   <div class="custom-legend" id="genderLegend"></div>
               </div>
               
               <!-- Age Distribution -->
               <div class="chart-container">
                   <h3><i class="fas fa-users"></i> Age Distribution</h3>
                   <div class="chart-wrapper">
                       <canvas id="ageChart"></canvas>
                   </div>
               </div>
               
               <!-- Document Types -->
               <div class="chart-container">
                   <h3><i class="fas fa-file-alt"></i> Document Types</h3>
                   <div class="chart-wrapper">
                       <canvas id="documentChart"></canvas>
                   </div>
                   <div class="custom-legend" id="documentLegend"></div>
               </div>
               
               <!-- Blotter Status -->
               <div class="chart-container">
                   <h3><i class="fas fa-book"></i> Blotter Status</h3>
                   <div class="chart-wrapper">
                       <canvas id="blotterChart"></canvas>
                   </div>
                   <div class="custom-legend" id="blotterLegend"></div>
               </div>
               
               <!-- Monthly Document Issuance -->
               <div class="chart-container full-width">
                   <h3><i class="fas fa-calendar-alt"></i> Monthly Document Issuance (<?php echo date('Y'); ?>)</h3>
                   <div class="chart-wrapper">
                       <canvas id="monthlyDocumentChart"></canvas>
                   </div>
               </div>
           </div>
       </div>
   </div>
   
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           // Common chart options
           Chart.defaults.font.family = "'Poppins', 'Arial', sans-serif";
           Chart.defaults.color = '#555';
           Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.7)';
           Chart.defaults.plugins.tooltip.padding = 10;
           Chart.defaults.plugins.tooltip.cornerRadius = 4;
           Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };
           
           // Create custom legends
           function createLegend(chartId, data, colors) {
               const legendContainer = document.getElementById(chartId + 'Legend');
               if (!legendContainer) return;
               
               legendContainer.innerHTML = '';
               Object.keys(data).forEach(key => {
                   const item = document.createElement('div');
                   item.className = 'legend-item';
                   
                   const colorBox = document.createElement('span');
                   colorBox.className = 'legend-color';
                   colorBox.style.backgroundColor = colors[key];
                   
                   const label = document.createElement('span');
                   label.textContent = `${key}: ${data[key]}`;
                   
                   item.appendChild(colorBox);
                   item.appendChild(label);
                   legendContainer.appendChild(item);
               });
           }
           
           // Gender Distribution Chart
           const genderCtx = document.getElementById('genderChart').getContext('2d');
           const genderData = <?php echo json_encode(array_values($gender_data)); ?>;
           const genderLabels = <?php echo json_encode(array_keys($gender_data)); ?>;
           const genderColors = <?php echo json_encode(array_values($gender_colors)); ?>;
           
           new Chart(genderCtx, {
               type: 'doughnut',
               data: {
                   labels: genderLabels,
                   datasets: [{
                       data: genderData,
                       backgroundColor: genderColors,
                       borderWidth: 2,
                       borderColor: '#ffffff',
                       hoverOffset: 15
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   cutout: '60%',
                   plugins: {
                       legend: {
                           display: false
                       },
                       tooltip: {
                           callbacks: {
                               label: function(context) {
                                   const label = context.label || '';
                                   const value = context.raw || 0;
                                   const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                   const percentage = Math.round((value / total) * 100);
                                   return `${label}: ${value} (${percentage}%)`;
                               }
                           }
                       }
                   },
                   animation: {
                       animateScale: true,
                       animateRotate: true
                   }
               }
           });
           
           createLegend('gender', <?php echo json_encode($gender_data); ?>, <?php echo json_encode($gender_colors); ?>);
           
           // Age Distribution Chart
           const ageCtx = document.getElementById('ageChart').getContext('2d');
           const ageData = <?php echo json_encode(array_values($age_data)); ?>;
           const ageLabels = <?php echo json_encode(array_keys($age_data)); ?>;
           const ageColors = <?php echo json_encode(array_values($age_colors)); ?>;
           
           new Chart(ageCtx, {
               type: 'bar',
               data: {
                   labels: ageLabels,
                   datasets: [{
                       label: 'Number of Residents',
                       data: ageData,
                       backgroundColor: ageColors,
                       borderWidth: 0,
                       borderRadius: 4,
                       maxBarThickness: 50
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   scales: {
                       y: {
                           beginAtZero: true,
                           grid: {
                               color: 'rgba(0, 0, 0, 0.05)'
                           }
                       },
                       x: {
                           grid: {
                               display: false
                           }
                       }
                   },
                   plugins: {
                       legend: {
                           display: false
                       }
                   },
                   animation: {
                       duration: 2000,
                       easing: 'easeOutQuart'
                   }
               }
           });
           
           // Document Types Chart
           const documentCtx = document.getElementById('documentChart').getContext('2d');
           const documentData = <?php echo json_encode(array_values($document_type_data)); ?>;
           const documentLabels = <?php echo json_encode(array_keys($document_type_data)); ?>;
           const documentColors = <?php echo json_encode(array_values($document_colors)); ?>;
           
           new Chart(documentCtx, {
               type: 'pie',
               data: {
                   labels: documentLabels,
                   datasets: [{
                       data: documentData,
                       backgroundColor: documentColors,
                       borderWidth: 2,
                       borderColor: '#ffffff'
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   plugins: {
                       legend: {
                           display: false
                       },
                       tooltip: {
                           callbacks: {
                               label: function(context) {
                                   const label = context.label || '';
                                   const value = context.raw || 0;
                                   const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                   const percentage = Math.round((value / total) * 100);
                                   return `${label}: ${value} (${percentage}%)`;
                               }
                           }
                       }
                   },
                   animation: {
                       animateScale: true,
                       animateRotate: true
                   }
               }
           });
           
           createLegend('document', <?php echo json_encode($document_type_data); ?>, <?php echo json_encode($document_colors); ?>);
           
           // Blotter Status Chart
           const blotterCtx = document.getElementById('blotterChart').getContext('2d');
           const blotterData = <?php echo json_encode(array_values($blotter_status_data)); ?>;
           const blotterLabels = <?php echo json_encode(array_keys($blotter_status_data)); ?>;
           const blotterColors = <?php echo json_encode(array_values($blotter_colors)); ?>;
           
           new Chart(blotterCtx, {
               type: 'polarArea',
               data: {
                   labels: blotterLabels,
                   datasets: [{
                       data: blotterData,
                       backgroundColor: blotterColors,
                       borderWidth: 1,
                       borderColor: '#ffffff'
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   scales: {
                       r: {
                           ticks: {
                               display: false
                           }
                       }
                   },
                   plugins: {
                       legend: {
                           display: false
                       }
                   },
                   animation: {
                       animateScale: true,
                       animateRotate: true
                   }
               }
           });
           
           createLegend('blotter', <?php echo json_encode($blotter_status_data); ?>, <?php echo json_encode($blotter_colors); ?>);
           
           // Monthly Document Issuance Chart
           const monthlyDocumentCtx = document.getElementById('monthlyDocumentChart').getContext('2d');
           const monthlyData = <?php echo json_encode(array_values($monthly_document_data)); ?>;
           const monthLabels = <?php echo json_encode($month_names); ?>;
           
           new Chart(monthlyDocumentCtx, {
               type: 'line',
               data: {
                   labels: monthLabels,
                   datasets: [{
                       label: 'Documents Issued',
                       data: monthlyData,
                       borderColor: '#3a86ff',
                       backgroundColor: 'rgba(58, 134, 255, 0.1)',
                       borderWidth: 3,
                       pointBackgroundColor: '#ffffff',
                       pointBorderColor: '#3a86ff',
                       pointBorderWidth: 2,
                       pointRadius: 5,
                       pointHoverRadius: 7,
                       fill: true,
                       tension: 0.4
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   scales: {
                       y: {
                           beginAtZero: true,
                           grid: {
                               color: 'rgba(0, 0, 0, 0.05)'
                           }
                       },
                       x: {
                           grid: {
                               display: false
                           }
                       }
                   },
                   plugins: {
                       legend: {
                           display: false
                       }
                   },
                   animation: {
                       duration: 2000,
                       easing: 'easeOutQuart'
                   }
               }
           });
           
           // Add shadow effect to charts
           document.querySelectorAll('.chart-container').forEach(container => {
               container.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.08)';
               container.addEventListener('mouseover', function() {
                   this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.12)';
               });
               container.addEventListener('mouseout', function() {
                   this.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.08)';
               });
           });
       });
   </script>
</body>
</html>
