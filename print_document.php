<?php
require_once 'php/config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: documents.php");
   exit();
}

$id = sanitize($_GET['id']);

// Get document information with resident name and issuer name
$document_query = "SELECT d.*, 
                 r.first_name, r.middle_name, r.last_name, r.gender, r.address,
                 u.full_name as issued_by_name
                 FROM documents d 
                 JOIN residents r ON d.resident_id = r.id
                 LEFT JOIN users u ON d.issued_by = u.id
                 WHERE d.id = $id";
$document_result = $conn->query($document_query);

if (!$document_result || $document_result->num_rows == 0) {
   header("Location: documents.php?error=1");
   exit();
}

$document = $document_result->fetch_assoc();

// Log the print action
logActivity($_SESSION['user_id'], "Printed document: {$document['document_id']}");

// Get current barangay officials (in a real system, this would come from a database)
$barangay_captain = "Juan Dela Cruz";
$barangay_secretary = "Maria Santos";

// Check if we're in print mode
$print_mode = isset($_GET['print']) && $_GET['print'] == 'true';
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Print <?php echo $document['document_type']; ?> - Barangay Management System</title>
   <link rel="stylesheet" href="assets/css/style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <style>
       @media print {
           body {
               font-family: 'Times New Roman', Times, serif;
               margin: 0;
               padding: 0;
           }
           .no-print {
               display: none !important;
           }
           .print-document {
               padding: 20px;
               margin: 0;
               box-shadow: none;
           }
           .print-document .document-content {
               border: none;
           }
       }
       
       .print-document {
           max-width: 8.5in;
           margin: 20px auto;
           padding: 20px;
           background-color: white;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
       }
       
       .document-header {
           text-align: center;
           margin-bottom: 30px;
       }
       
       .document-header img {
           width: 80px;
           height: 80px;
       }
       
       .document-header h2, .document-header h3, .document-header h4 {
           margin: 5px 0;
       }
       
       .document-content {
           padding: 20px;
           border: 1px solid #ddd;
           min-height: 500px;
           line-height: 1.6;
       }
       
       .document-title {
           text-align: center;
           font-size: 24px;
           font-weight: bold;
           margin: 20px 0;
           text-transform: uppercase;
           text-decoration: underline;
       }
       
       .document-body {
           margin: 30px 0;
           text-align: justify;
       }
       
       .document-signature {
           margin-top: 80px;
           display: flex;
           justify-content: space-between;
       }
       
       .signature-block {
           text-align: center;
           width: 200px;
       }
       
       .signature-line {
           border-top: 1px solid black;
           margin-top: 40px;
           margin-bottom: 5px;
       }
       
       .print-buttons {
           text-align: center;
           margin: 20px 0;
       }
       
       .preview-container {
           display: flex;
           flex-direction: column;
           min-height: 100vh;
       }
       
       .preview-header {
           background-color: #f8f9fa;
           padding: 15px;
           border-bottom: 1px solid #ddd;
           display: flex;
           justify-content: space-between;
           align-items: center;
       }
       
       .preview-content {
           flex: 1;
           padding: 20px;
           background-color: #eee;
           display: flex;
           justify-content: center;
           align-items: flex-start;
       }
   </style>
   
   <?php if ($print_mode): ?>
   <script>
       // Auto-print when in print mode
       window.onload = function() {
           window.print();
       }
   </script>
   <?php endif; ?>
</head>
<body>
   <?php if (!$print_mode): ?>
   <div class="preview-container">
       <div class="preview-header no-print">
           <h2><?php echo $document['document_type']; ?> Preview</h2>
           <div>
               <a href="print_document.php?id=<?php echo $id; ?>&print=true" class="btn btn-success">
                   <i class="fas fa-print"></i> Print Document
               </a>
               <a href="documents.php" class="btn btn-primary">
                   <i class="fas fa-arrow-left"></i> Back to Documents
               </a>
           </div>
       </div>
       <div class="preview-content no-print">
   <?php endif; ?>
   
   <div class="print-document">
       <div class="document-header">
           <img src="assets/images/barangay-logo.png" alt="Barangay Logo">
           <h2>Republic of the Philippines</h2>
           <h3>Barangay [Barangay Name]</h3>
           <h4>[Municipality], [Province]</h4>
       </div>
       
       <div class="document-content">
           <div class="document-title">
               <?php echo $document['document_type']; ?>
           </div>
           
           <div class="document-body">
               <?php if ($document['document_type'] == 'Barangay Clearance'): ?>
                   <p>TO WHOM IT MAY CONCERN:</p>
                   
                   <p>This is to certify that <strong><?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?></strong>, 
                   <?php echo $document['gender'] == 'Male' ? 'a Filipino citizen' : 'a Filipino citizen'; ?>, of legal age, and a resident of 
                   <strong><?php echo $document['address']; ?></strong>, is a person of good moral character and reputation. 
                   <?php echo $document['gender'] == 'Male' ? 'He' : 'She'; ?> has no derogatory record filed in this office.</p>
                   
                   <p>This certification is being issued upon the request of the above-named person for 
                   <strong><?php echo $document['purpose']; ?></strong>.</p>
                   
                   <p>Issued this <?php echo date('jS', strtotime($document['issue_date'])); ?> day of 
                   <?php echo date('F, Y', strtotime($document['issue_date'])); ?> at Barangay [Barangay Name], [Municipality], [Province].</p>
               
               <?php elseif ($document['document_type'] == 'Certificate of Indigency'): ?>
                   <p>TO WHOM IT MAY CONCERN:</p>
                   
                   <p>This is to certify that <strong><?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?></strong>, 
                   <?php echo $document['gender'] == 'Male' ? 'a Filipino citizen' : 'a Filipino citizen'; ?>, of legal age, and a resident of 
                   <strong><?php echo $document['address']; ?></strong>, belongs to an indigent family in this Barangay.</p>
                   
                   <p>This certification is being issued upon the request of the above-named person for 
                   <strong><?php echo $document['purpose']; ?></strong>.</p>
                   
                   <p>Issued this <?php echo date('jS', strtotime($document['issue_date'])); ?> day of 
                   <?php echo date('F, Y', strtotime($document['issue_date'])); ?> at Barangay [Barangay Name], [Municipality], [Province].</p>
               
               <?php elseif ($document['document_type'] == 'Certificate of Residency'): ?>
                   <p>TO WHOM IT MAY CONCERN:</p>
                   
                   <p>This is to certify that <strong><?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?></strong>, 
                   <?php echo $document['gender'] == 'Male' ? 'a Filipino citizen' : 'a Filipino citizen'; ?>, of legal age, is a bonafide resident of 
                   <strong><?php echo $document['address']; ?></strong>.</p>
                   
                   <p>This certification is being issued upon the request of the above-named person for 
                   <strong><?php echo $document['purpose']; ?></strong>.</p>
                   
                   <p>Issued this <?php echo date('jS', strtotime($document['issue_date'])); ?> day of 
                   <?php echo date('F, Y', strtotime($document['issue_date'])); ?> at Barangay [Barangay Name], [Municipality], [Province].</p>
               
               <?php elseif ($document['document_type'] == 'Business Permit'): ?>
                   <p>TO WHOM IT MAY CONCERN:</p>
                   
                   <p>This is to certify that <strong><?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?></strong>, 
                   <?php echo $document['gender'] == 'Male' ? 'a Filipino citizen' : 'a Filipino citizen'; ?>, of legal age, and a resident of 
                   <strong><?php echo $document['address']; ?></strong>, is granted permission to operate a business in this Barangay.</p>
                   
                   <p>This permit is being issued for the purpose of <strong><?php echo $document['purpose']; ?></strong>.</p>
                   
                   <p>Issued this <?php echo date('jS', strtotime($document['issue_date'])); ?> day of 
                   <?php echo date('F, Y', strtotime($document['issue_date'])); ?> at Barangay [Barangay Name], [Municipality], [Province].</p>
                   
                   <?php if ($document['expiry_date']): ?>
                   <p>This permit is valid until <?php echo date('F d, Y', strtotime($document['expiry_date'])); ?>.</p>
                   <?php endif; ?>
               
               <?php else: ?>
                   <p>TO WHOM IT MAY CONCERN:</p>
                   
                   <p>This is to certify that <strong><?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?></strong>, 
                   <?php echo $document['gender'] == 'Male' ? 'a Filipino citizen' : 'a Filipino citizen'; ?>, of legal age, and a resident of 
                   <strong><?php echo $document['address']; ?></strong>.</p>
                   
                   <p>This certification is being issued upon the request of the above-named person for 
                   <strong><?php echo $document['purpose']; ?></strong>.</p>
                   
                   <p>Issued this <?php echo date('jS', strtotime($document['issue_date'])); ?> day of 
                   <?php echo date('F, Y', strtotime($document['issue_date'])); ?> at Barangay [Barangay Name], [Municipality], [Province].</p>
               <?php endif; ?>
           </div>
           
           <div class="document-signature">
               <div class="signature-block">
                   <div class="signature-line"></div>
                   <strong>Barangay Secretary</strong><br>
                   <?php echo $barangay_secretary; ?>
               </div>
               
               <div class="signature-block">
                   <div class="signature-line"></div>
                   <strong>Barangay Captain</strong><br>
                   <?php echo $barangay_captain; ?>
               </div>
           </div>
       </div>
   </div>
   
   <?php if (!$print_mode): ?>
       </div>
   </div>
   <?php endif; ?>
</body>
</html>
