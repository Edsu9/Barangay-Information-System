<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: ../../residents.php");
   exit();
}

$id = sanitize($_GET['id']);

// Get resident information
$resident_query = "SELECT r.*, h.family_name, h.household_id as hh_id 
                 FROM residents r 
                 LEFT JOIN households h ON r.household_id = h.id 
                 WHERE r.id = $id";
$resident_result = $conn->query($resident_query);

if (!$resident_result || $resident_result->num_rows == 0) {
   header("Location: ../../residents.php?error=1");
   exit();
}

$resident = $resident_result->fetch_assoc();

// Calculate age
$birthdate = new DateTime($resident['birth_date']);
$today = new DateTime();
$age = $birthdate->diff($today)->y;

// Log the view action
logActivity($_SESSION['user_id'], "Viewed resident: {$resident['first_name']} {$resident['last_name']} (ID: $id)");

// Include the header
$pageTitle = "View Resident";
include '../includes/header.php';
?>

<!-- Add CS Form Styling -->
<link rel="stylesheet" href="../../assets/css/cs-form.css">

<!-- Replace the existing resident details div with this new government-style form -->

<!-- Resident Details -->
<div class="form-container">
  <div class="form-title">
      <div style="display: flex; justify-content: space-between; align-items: center;">
    <span><i class="fas fa-user"></i> Resident Profile</span>
    <div>
        <a href="../residents/edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="../residents/delete.php?id=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this resident?')">
            <i class="fas fa-trash"></i> Delete
        </a>
        <a href="<?php echo isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') !== false ? '../../dashboard.php' : '../../residents.php'; ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to <?php echo isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard.php') !== false ? 'Dashboard' : 'List'; ?>
        </a>
    </div>
</div>
  </div>
  
  <div class="cs-form">
      <div class="cs-form-header">
          <h2>Republic of the Philippines</h2>
          <h3>Barangay Resident Information Form</h3>
          <p>RS Form No. 1 | Series of <?php echo date('Y'); ?></p>
      </div>
      
      <!-- Profile Image and Basic Info Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">I. Personal Information</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field" style="flex: 0 0 120px;">
                      <label>ID Photo</label>
                      <div class="profile-image-container">
                          <?php if (!empty($resident['profile_image'])): ?>
                              <img src="../../<?php echo $resident['profile_image']; ?>" class="profile-image" alt="Profile Image">
                          <?php else: ?>
                              <div class="profile-image-placeholder">
                                  <i class="fas fa-user"></i>
                                  <span>No Image</span>
                              </div>
                          <?php endif; ?>
                      </div>
                  </div>
                  <div class="cs-form-field" style="flex: 1;">
                      <label>Resident ID</label>
                      <div class="data-value"><?php echo $resident['resident_id']; ?></div>
                      
                      <label>ID Card No.</label>
                      <div class="data-value"><?php echo $resident['id_card_no'] ?: 'Not provided'; ?></div>
                      
                      <label>Household</label>
                      <div class="data-value">
                          <?php if ($resident['household_id']): ?>
                              <a href="../households/view.php?id=<?php echo $resident['household_id']; ?>">
                                  <?php echo $resident['family_name'] . ' (' . $resident['hh_id'] . ')'; ?>
                              </a>
                              <?php if ($resident['is_head_of_family']): ?>
                                  <span class="badge badge-primary">Head of Family</span>
                              <?php endif; ?>
                          <?php else: ?>
                              No Household
                          <?php endif; ?>
                      </div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Last Name</label>
                      <div class="data-value"><?php echo $resident['last_name']; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>First Name</label>
                      <div class="data-value"><?php echo $resident['first_name']; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Middle Name</label>
                      <div class="data-value"><?php echo $resident['middle_name'] ?: 'N/A'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Birth Date</label>
                      <div class="data-value"><?php echo date('F d, Y', strtotime($resident['birth_date'])); ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Age</label>
                      <div class="data-value"><?php echo $age; ?> years old</div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Gender</label>
                      <div class="data-value"><?php echo $resident['gender']; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Civil Status</label>
                      <div class="data-value"><?php echo $resident['civil_status']; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Nationality</label>
                      <div class="data-value"><?php echo $resident['nationality']; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field full-width">
                      <label>Complete Address</label>
                      <div class="data-value"><?php echo $resident['address']; ?></div>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Contact Information Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">II. Contact Information</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Contact Number</label>
                      <div class="data-value"><?php echo $resident['contact_number'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Email Address</label>
                      <div class="data-value"><?php echo $resident['email'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Emergency Contact Name</label>
                      <div class="data-value"><?php echo $resident['emergency_contact_name'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Emergency Contact Number</label>
                      <div class="data-value"><?php echo $resident['emergency_contact_number'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Relationship</label>
                      <div class="data-value"><?php echo $resident['emergency_contact_relationship'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Additional Information Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">III. Additional Information</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Occupation</label>
                      <div class="data-value"><?php echo $resident['occupation'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Educational Attainment</label>
                      <div class="data-value"><?php echo $resident['educational_attainment'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Blood Type</label>
                      <div class="data-value"><?php echo $resident['blood_type'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Height (cm)</label>
                      <div class="data-value"><?php echo $resident['height'] ? $resident['height'] . ' cm' : 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Weight (kg)</label>
                      <div class="data-value"><?php echo $resident['weight'] ? $resident['weight'] . ' kg' : 'Not provided'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Sector</label>
                      <div class="data-value"><?php echo $resident['sector']; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Religion</label>
                      <div class="data-value"><?php echo $resident['religion'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field" style="flex: 0 0 200px;">
                      <label>Registered Voter</label>
                      <div class="data-value">
                          <div class="cs-form-checkbox">
                              <input type="checkbox" <?php echo $resident['is_voter'] ? 'checked' : ''; ?> disabled>
                              <span><?php echo $resident['is_voter'] ? 'Yes' : 'No'; ?></span>
                          </div>
                      </div>
                  </div>
                  <div class="cs-form-field" style="flex: 0 0 200px;">
                      <label>Head of Family</label>
                      <div class="data-value">
                          <div class="cs-form-checkbox">
                              <input type="checkbox" <?php echo $resident['is_head_of_family'] ? 'checked' : ''; ?> disabled>
                              <span><?php echo $resident['is_head_of_family'] ? 'Yes' : 'No'; ?></span>
                          </div>
                      </div>
                  </div>
                  <div class="cs-form-field" style="flex: 1;">
                      <!-- Spacer -->
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Government IDs Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">IV. Government IDs</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>PhilHealth Number</label>
                      <div class="data-value"><?php echo $resident['philhealth_no'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>SSS Number</label>
                      <div class="data-value"><?php echo $resident['sss_no'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>TIN Number</label>
                      <div class="data-value"><?php echo $resident['tin_no'] ?: 'Not provided'; ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Pag-IBIG Number</label>
                      <div class="data-value"><?php echo $resident['pagibig_no'] ?: 'Not provided'; ?></div>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Remarks Section -->
      <?php if (!empty($resident['remarks'])): ?>
      <div class="cs-form-section">
          <div class="cs-form-section-header">V. Remarks</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field full-width">
                      <div class="data-value" style="padding: 10px;"><?php echo nl2br($resident['remarks']); ?></div>
                  </div>
              </div>
          </div>
      </div>
      <?php endif; ?>
      
      <!-- System Information -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">VI. System Information</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Date Added</label>
                      <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($resident['created_at'])); ?></div>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Last Updated</label>
                      <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($resident['updated_at'])); ?></div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<!-- Documents Issued -->
<?php
// Get documents issued to this resident
$documents_query = "SELECT * FROM documents WHERE resident_id = $id ORDER BY created_at DESC";
$documents = $conn->query($documents_query);
?>
<div class="form-container">
   <div class="form-title">
       <div style="display: flex; justify-content: space-between; align-items: center;">
           <span><i class="fas fa-file-alt"></i> Documents Issued</span>
           <a href="../../php/documents/add.php?resident_id=<?php echo $id; ?>" class="btn btn-success">
               <i class="fas fa-plus"></i> Issue New Document
           </a>
       </div>
   </div>
   
   <div class="data-table">
       <table>
           <thead>
               <tr>
                   <th>Document ID</th>
                   <th>Type</th>
                   <th>Purpose</th>
                   <th>Issue Date</th>
                   <th>Status</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               <?php if($documents && $documents->num_rows > 0): ?>
                   <?php while($document = $documents->fetch_assoc()): ?>
                   <tr>
                       <td><?php echo $document['document_id']; ?></td>
                       <td><?php echo $document['document_type']; ?></td>
                       <td><?php echo $document['purpose']; ?></td>
                       <td><?php echo date('M d, Y', strtotime($document['issue_date'])); ?></td>
                       <td>
                           <span class="badge badge-<?php echo strtolower($document['status']) == 'issued' ? 'success' : (strtolower($document['status']) == 'pending' ? 'warning' : 'danger'); ?>">
                               <?php echo $document['status']; ?>
                           </span>
                       </td>
                       <td class="action-buttons">
                           <a href="../../view_document.php?id=<?php echo $document['id']; ?>" class="btn btn-primary">
                               <i class="fas fa-eye"></i>
                           </a>
                           <a href="../../print_document.php?id=<?php echo $document['id']; ?>" class="btn btn-success">
                               <i class="fas fa-print"></i>
                           </a>
                       </td>
                   </tr>
                   <?php endwhile; ?>
               <?php else: ?>
                   <tr>
                       <td colspan="6" style="text-align: center;">No documents found</td>
                   </tr>
               <?php endif; ?>
           </tbody>
       </table>
   </div>
</div>

<!-- Blotter Records -->
<?php
// Get blotter records involving this resident
$blotter_query = "SELECT * FROM blotter WHERE complainant_id = $id OR respondent_id = $id ORDER BY created_at DESC";
$blotters = $conn->query($blotter_query);
?>
<div class="form-container">
   <div class="form-title">
       <div style="display: flex; justify-content: space-between; align-items: center;">
           <span><i class="fas fa-book"></i> Blotter Records</span>
       </div>
   </div>
   
   <div class="data-table">
       <table>
           <thead>
               <tr>
                   <th>Blotter ID</th>
                   <th>Incident Type</th>
                   <th>Incident Date</th>
                   <th>Status</th>
                   <th>Role</th>
                   <th>Actions</th>
               </tr>
           </thead>
           <tbody>
               <?php if($blotters && $blotters->num_rows > 0): ?>
                   <?php while($blotter = $blotters->fetch_assoc()): ?>
                   <tr>
                       <td><?php echo $blotter['blotter_id']; ?></td>
                       <td><?php echo $blotter['incident_type']; ?></td>
                       <td><?php echo date('M d, Y', strtotime($blotter['incident_date'])); ?></td>
                       <td>
                           <span class="badge badge-<?php echo strtolower($blotter['status']) == 'resolved' ? 'success' : (strtolower($blotter['status']) == 'pending' ? 'warning' : 'info'); ?>">
                               <?php echo $blotter['status']; ?>
                           </span>
                       </td>
                       <td>
                           <?php if($blotter['complainant_id'] == $id): ?>
                               <span class="badge badge-danger">Complainant</span>
                           <?php else: ?>
                               <span class="badge badge-warning">Respondent</span>
                           <?php endif; ?>
                       </td>
                       <td class="action-buttons">
                           <a href="../../view_blotter.php?id=<?php echo $blotter['id']; ?>" class="btn btn-primary">
                               <i class="fas fa-eye"></i>
                           </a>
                       </td>
                   </tr>
                   <?php endwhile; ?>
               <?php else: ?>
                   <tr>
                       <td colspan="6" style="text-align: center;">No blotter records found</td>
                   </tr>
               <?php endif; ?>
           </tbody>
       </table>
   </div>
</div>

<?php include '../includes/footer.php'; ?>
