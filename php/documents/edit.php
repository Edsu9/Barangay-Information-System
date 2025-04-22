<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: ../../documents.php");
   exit();
}

$id = sanitize($_GET['id']);

// Get document information
$document_query = "SELECT * FROM documents WHERE id = $id";
$document_result = $conn->query($document_query);

if (!$document_result || $document_result->num_rows == 0) {
   header("Location: ../../documents.php?error=1");
   exit();
}

$document = $document_result->fetch_assoc();

// Get all residents for dropdown
$residents_query = "SELECT * FROM residents ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Sanitize and collect form data
   $resident_id = sanitize($_POST['resident_id']);
   $document_type = sanitize($_POST['document_type']);
   $purpose = sanitize($_POST['purpose']);
   $issue_date = sanitize($_POST['issue_date']);
   $expiry_date = !empty($_POST['expiry_date']) ? sanitize($_POST['expiry_date']) : NULL;
   $status = sanitize($_POST['status']);
   $remarks = sanitize($_POST['remarks']);
   
   // Update document data
   $sql = "UPDATE documents SET 
           resident_id = $resident_id, 
           document_type = '$document_type', 
           purpose = '$purpose', 
           issue_date = '$issue_date', 
           expiry_date = " . ($expiry_date ? "'$expiry_date'" : "NULL") . ", 
           status = '$status', 
           remarks = '$remarks',
           issued_by = {$_SESSION['user_id']}
           WHERE id = $id";
   
   if ($conn->query($sql) === TRUE) {
       // Log activity
       logActivity($_SESSION['user_id'], "Updated document: {$document['document_id']}");
       
       // Redirect to documents page with success message
       header("Location: ../../documents.php?success=2");
       exit();
   } else {
       $error = "Error: " . $sql . "<br>" . $conn->error;
   }
}

// Include the header
$pageTitle = "Edit Document";
include '../includes/header.php';
?>

<!-- Edit Document Form -->
<div class="form-container">
   <div class="form-title">Edit Document Information</div>
   
   <?php if(isset($error)): ?>
   <div class="alert alert-danger">
       <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
   </div>
   <?php endif; ?>
   
   <form action="" method="POST">
       <!-- Document ID -->
       <div class="form-row">
           <div class="form-col">
               <div class="form-group">
                   <label for="document_id">Document ID</label>
                   <input type="text" id="document_id" class="form-control" value="<?php echo $document['document_id']; ?>" readonly>
               </div>
           </div>
       </div>
       
       <!-- Document Information -->
       <div class="form-row">
           <div class="form-col">
               <div class="form-group">
                   <label for="resident_id">Resident *</label>
                   <select id="resident_id" name="resident_id" class="form-control" required>
                       <option value="">Select Resident</option>
                       <?php if($residents && $residents->num_rows > 0): ?>
                           <?php while($resident = $residents->fetch_assoc()): ?>
                           <option value="<?php echo $resident['id']; ?>" <?php echo ($resident['id'] == $document['resident_id']) ? 'selected' : ''; ?>>
                               <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                           </option>
                           <?php endwhile; ?>
                       <?php endif; ?>
                   </select>
               </div>
           </div>
           <div class="form-col">
               <div class="form-group">
                   <label for="document_type">Document Type *</label>
                   <select id="document_type" name="document_type" class="form-control" required>
                       <option value="Barangay Clearance" <?php echo ($document['document_type'] == 'Barangay Clearance') ? 'selected' : ''; ?>>Barangay Clearance</option>
                       <option value="Certificate of Indigency" <?php echo ($document['document_type'] == 'Certificate of Indigency') ? 'selected' : ''; ?>>Certificate of Indigency</option>
                       <option value="Certificate of Residency" <?php echo ($document['document_type'] == 'Certificate of Residency') ? 'selected' : ''; ?>>Certificate of Residency</option>
                       <option value="Business Permit" <?php echo ($document['document_type'] == 'Business Permit') ? 'selected' : ''; ?>>Business Permit</option>
                       <option value="Other" <?php echo ($document['document_type'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                   </select>
               </div>
           </div>
       </div>
       
       <div class="form-group">
           <label for="purpose">Purpose *</label>
           <textarea id="purpose" name="purpose" class="form-control" required><?php echo $document['purpose']; ?></textarea>
       </div>
       
       <div class="form-row">
           <div class="form-col">
               <div class="form-group">
                   <label for="issue_date">Issue Date *</label>
                   <input type="date" id="issue_date" name="issue_date" class="form-control" value="<?php echo $document['issue_date']; ?>" required>
               </div>
           </div>
           <div class="form-col">
               <div class="form-group">
                   <label for="expiry_date">Expiry Date</label>
                   <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="<?php echo $document['expiry_date']; ?>">
               </div>
           </div>
           <div class="form-col">
               <div class="form-group">
                   <label for="status">Status *</label>
                   <select id="status" name="status" class="form-control" required>
                       <option value="Pending" <?php echo ($document['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                       <option value="Issued" <?php echo ($document['status'] == 'Issued') ? 'selected' : ''; ?>>Issued</option>
                       <option value="Cancelled" <?php echo ($document['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                       <option value="Expired" <?php echo ($document['status'] == 'Expired') ? 'selected' : ''; ?>>Expired</option>
                   </select>
               </div>
           </div>
       </div>
       
       <!-- Remarks -->
       <div class="form-group">
           <label for="remarks">Remarks</label>
           <textarea id="remarks" name="remarks" class="form-control"><?php echo $document['remarks']; ?></textarea>
       </div>
       
       <!-- Submit Button -->
       <div style="display: flex; gap: 1rem; margin-top: 1rem;">
           <button type="submit" class="btn btn-success">
               <i class="fas fa-save"></i> Update Document
           </button>
           <a href="../../documents.php" class="btn btn-danger">
               <i class="fas fa-times"></i> Cancel
           </a>
       </div>
   </form>
</div>

<?php include '../includes/footer.php'; ?>
