<?php
require_once '../config.php';
requireLogin();

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
   
   // Generate unique document ID
   $document_id = generateUniqueID('DOC');
   
   // Insert document data
   $sql = "INSERT INTO documents (
               document_id, resident_id, document_type, purpose, 
               issue_date, expiry_date, status, issued_by, remarks
           ) VALUES (
               '$document_id', $resident_id, '$document_type', '$purpose', 
               '$issue_date', " . ($expiry_date ? "'$expiry_date'" : "NULL") . ", '$status', {$_SESSION['user_id']}, '$remarks'
           )";
   
   if ($conn->query($sql) === TRUE) {
       // Log activity
       logActivity($_SESSION['user_id'], "Created new $document_type document for resident ID: $resident_id");
       
       // Redirect to documents page with success message
       header("Location: ../../documents.php?success=1");
       exit();
   } else {
       $error = "Error: " . $sql . "<br>" . $conn->error;
   }
}

// Include the header
$pageTitle = "Create Document";
include '../includes/header.php';
?>

<!-- Add Document Form -->
<div class="form-container">
   <div class="form-title">Document Information</div>
   
   <?php if(isset($error)): ?>
   <div class="alert alert-danger">
       <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
   </div>
   <?php endif; ?>
   
   <form action="" method="POST">
       <!-- Document Information -->
       <div class="form-row">
           <div class="form-col">
               <div class="form-group">
                   <label for="resident_id">Resident *</label>
                   <select id="resident_id" name="resident_id" class="form-control" required>
                       <option value="">Select Resident</option>
                       <?php if($residents && $residents->num_rows > 0): ?>
                           <?php while($resident = $residents->fetch_assoc()): ?>
                           <option value="<?php echo $resident['id']; ?>">
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
                       <option value="">Select Document Type</option>
                       <option value="Barangay Clearance">Barangay Clearance</option>
                       <option value="Certificate of Indigency">Certificate of Indigency</option>
                       <option value="Certificate of Residency">Certificate of Residency</option>
                       <option value="Business Permit">Business Permit</option>
                       <option value="Other">Other</option>
                   </select>
               </div>
           </div>
       </div>
       
       <div class="form-group">
           <label for="purpose">Purpose *</label>
           <textarea id="purpose" name="purpose" class="form-control" required></textarea>
       </div>
       
       <div class="form-row">
           <div class="form-col">
               <div class="form-group">
                   <label for="issue_date">Issue Date *</label>
                   <input type="date" id="issue_date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
               </div>
           </div>
           <div class="form-col">
               <div class="form-group">
                   <label for="expiry_date">Expiry Date</label>
                   <input type="date" id="expiry_date" name="expiry_date" class="form-control">
               </div>
           </div>
           <div class="form-col">
               <div class="form-group">
                   <label for="status">Status *</label>
                   <select id="status" name="status" class="form-control" required>
                       <option value="Pending">Pending</option>
                       <option value="Issued" selected>Issued</option>
                       <option value="Cancelled">Cancelled</option>
                       <option value="Expired">Expired</option>
                   </select>
               </div>
           </div>
       </div>
       
       <!-- Remarks -->
       <div class="form-group">
           <label for="remarks">Remarks</label>
           <textarea id="remarks" name="remarks" class="form-control"></textarea>
       </div>
       
       <!-- Submit Button -->
       <div style="display: flex; gap: 1rem; margin-top: 1rem;">
           <button type="submit" class="btn btn-success">
               <i class="fas fa-save"></i> Create Document
           </button>
           <a href="../../documents.php" class="btn btn-danger">
               <i class="fas fa-times"></i> Cancel
           </a>
       </div>
   </form>
</div>

<?php include '../includes/footer.php'; ?>
