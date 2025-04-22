<?php
require_once '../config.php';
requireLogin();

// Get all households for dropdown
$households_query = "SELECT * FROM households ORDER BY family_name ASC";
$households = $conn->query($households_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   // Sanitize and collect form data
   $first_name = sanitize($_POST['first_name']);
   $middle_name = sanitize($_POST['middle_name']);
   $last_name = sanitize($_POST['last_name']);
   $birth_date = sanitize($_POST['birth_date']);
   $gender = sanitize($_POST['gender']);
   $civil_status = sanitize($_POST['civil_status']);
   $contact_number = sanitize($_POST['contact_number']);
   $email = sanitize($_POST['email']);
   $occupation = sanitize($_POST['occupation']);
   $educational_attainment = sanitize($_POST['educational_attainment']);
   $address = sanitize($_POST['address']);
   $is_voter = isset($_POST['is_voter']) ? 1 : 0;
   $is_head_of_family = isset($_POST['is_head_of_family']) ? 1 : 0;
   $nationality = sanitize($_POST['nationality']);
   $religion = sanitize($_POST['religion']);
   $sector = sanitize($_POST['sector']);
   $remarks = sanitize($_POST['remarks']);
   $household_id = !empty($_POST['household_id']) ? sanitize($_POST['household_id']) : NULL;
   
   // Additional profiling fields
   $id_card_no = sanitize($_POST['id_card_no']);
   $blood_type = sanitize($_POST['blood_type']);
   $height = sanitize($_POST['height']);
   $weight = sanitize($_POST['weight']);
   $philhealth_no = sanitize($_POST['philhealth_no']);
   $sss_no = sanitize($_POST['sss_no']);
   $tin_no = sanitize($_POST['tin_no']);
   $pagibig_no = sanitize($_POST['pagibig_no']);
   $emergency_contact_name = sanitize($_POST['emergency_contact_name']);
   $emergency_contact_number = sanitize($_POST['emergency_contact_number']);
   $emergency_contact_relationship = sanitize($_POST['emergency_contact_relationship']);
   
   // Handle profile image upload
   $profile_image = NULL;
   if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
       $allowed = ['jpg', 'jpeg', 'png', 'gif'];
       $filename = $_FILES['profile_image']['name'];
       $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
       
       if (in_array($ext, $allowed)) {
           $upload_dir = '../../uploads/profiles/';
           
           // Create directory if it doesn't exist
           if (!file_exists($upload_dir)) {
               mkdir($upload_dir, 0777, true);
           }
           
           $new_filename = uniqid() . '.' . $ext;
           $destination = $upload_dir . $new_filename;
           
           if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
               $profile_image = 'uploads/profiles/' . $new_filename;
           }
       }
   }
   
   // Generate unique resident ID
   $resident_id = generateUniqueID('RES');
   
   // Insert resident data
   $sql = "INSERT INTO residents (
               resident_id, household_id, first_name, middle_name, last_name, profile_image,
               birth_date, gender, civil_status, contact_number, email, 
               occupation, educational_attainment, address, is_voter, 
               is_head_of_family, nationality, religion, sector, remarks,
               id_card_no, blood_type, height, weight, philhealth_no, sss_no, tin_no, pagibig_no,
               emergency_contact_name, emergency_contact_number, emergency_contact_relationship
           ) VALUES (
               '$resident_id', " . ($household_id ? "$household_id" : "NULL") . ", '$first_name', '$middle_name', '$last_name', " . ($profile_image ? "'$profile_image'" : "NULL") . ",
               '$birth_date', '$gender', '$civil_status', '$contact_number', '$email', 
               '$occupation', '$educational_attainment', '$address', $is_voter, 
               $is_head_of_family, '$nationality', '$religion', '$sector', '$remarks',
               '$id_card_no', '$blood_type', '$height', '$weight', '$philhealth_no', '$sss_no', '$tin_no', '$pagibig_no',
               '$emergency_contact_name', '$emergency_contact_number', '$emergency_contact_relationship'
           )";
   
   if ($conn->query($sql) === TRUE) {
       $resident_id_db = $conn->insert_id;
       
       // If resident is head of family, update household
       if ($is_head_of_family && $household_id) {
           $update_household = "UPDATE households SET head_of_family_id = $resident_id_db WHERE id = $household_id";
           $conn->query($update_household);
       }
       
       // Log activity
       logActivity($_SESSION['user_id'], "Added new resident: $first_name $last_name");
       
       // Redirect to residents page with success message
       header("Location: ../../residents.php?success=1");
       exit();
   } else {
       $error = "Error: " . $sql . "<br>" . $conn->error;
   }
}

// Include the header
$pageTitle = "Add Resident";
include '../includes/header.php';
?>

<!-- Add Resident Form with CS Form Layout -->
<link rel="stylesheet" href="../../assets/css/cs-form.css">

<div class="form-container">
   <div class="form-title">
       <div style="display: flex; justify-content: space-between; align-items: center;">
           <span><i class="fas fa-user-plus"></i> Add New Resident</span>
       </div>
   </div>
   
   <?php if(isset($error)): ?>
   <div class="alert alert-danger">
       <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
   </div>
   <?php endif; ?>
   
   
<form action="" method="POST" enctype="multipart/form-data" class="cs-form">
      <div class="cs-form-header">
          <h2>Republic of the Philippines</h2>
          <h3>Barangay Resident Information Form</h3>
          <p>RS Form No. 1 | Series of <?php echo date('Y'); ?></p>
      </div>
      
      <!-- Profile Image Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">I. Personal Information</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field" style="flex: 0 0 120px;">
                      <label>ID Photo</label>
                      <div class="profile-image-container">
                          <div class="profile-image-placeholder">
                              <i class="fas fa-user"></i>
                              <span>2x2 ID Picture</span>
                          </div>
                      </div>
                      <div class="profile-image-upload" style="padding: 5px;">
                          <input type="file" name="profile_image" id="profile_image" accept="image/*" style="font-size: 10px;">
                      </div>
                  </div>
                  <div class="cs-form-field" style="flex: 1;">
                      <label>Resident ID</label>
                      <input type="text" id="resident_id" value="Auto-generated" disabled>
                      
                      <label>ID Card No.</label>
                      <input type="text" id="id_card_no" name="id_card_no" placeholder="Optional">
                      
                      <label>Household</label>
                      <select id="household_id" name="household_id">
                          <option value="">Select Household (Optional)</option>
                          <?php if($households && $households->num_rows > 0): ?>
                              <?php while($household = $households->fetch_assoc()): ?>
                              <option value="<?php echo $household['id']; ?>">
                                  <?php echo $household['family_name'] . ' - ' . $household['address']; ?>
                              </option>
                              <?php endwhile; ?>
                          <?php endif; ?>
                      </select>
                      <div class="cs-form-checkbox" style="margin-top: 5px;">
                          <input type="checkbox" id="is_head_of_family" name="is_head_of_family">
                          <label for="is_head_of_family" style="display: inline; border: none; background: none; padding: 0; font-size: 12px;">Head of Family</label>
                      </div>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Last Name</label>
                      <input type="text" id="last_name" name="last_name" required>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>First Name</label>
                      <input type="text" id="first_name" name="first_name" required>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Middle Name</label>
                      <input type="text" id="middle_name" name="middle_name">
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Birth Date</label>
                      <input type="date" id="birth_date" name="birth_date" required>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Gender</label>
                      <select id="gender" name="gender" required>
                          <option value="">Select Gender</option>
                          <option value="Male">Male</option>
                          <option value="Female">Female</option>
                          <option value="Other">Other</option>
                      </select>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Civil Status</label>
                      <select id="civil_status" name="civil_status" required>
                          <option value="">Select Civil Status</option>
                          <option value="Single">Single</option>
                          <option value="Married">Married</option>
                          <option value="Widowed">Widowed</option>
                          <option value="Divorced">Divorced</option>
                          <option value="Separated">Separated</option>
                      </select>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Nationality</label>
                      <input type="text" id="nationality" name="nationality" value="Filipino">
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Religion</label>
                      <input type="text" id="religion" name="religion">
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field full-width">
                      <label>Complete Address</label>
                      <textarea id="address" name="address" rows="2" required></textarea>
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
                      <input type="text" id="contact_number" name="contact_number">
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Email Address</label>
                      <input type="email" id="email" name="email">
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Emergency Contact Name</label>
                      <input type="text" id="emergency_contact_name" name="emergency_contact_name">
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Emergency Contact Number</label>
                      <input type="text" id="emergency_contact_number" name="emergency_contact_number">
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Relationship</label>
                      <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship">
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
                      <input type="text" id="occupation" name="occupation">
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Educational Attainment</label>
                      <select id="educational_attainment" name="educational_attainment">
                          <option value="">Select Educational Attainment</option>
                          <option value="Elementary">Elementary</option>
                          <option value="High School">High School</option>
                          <option value="Vocational">Vocational</option>
                          <option value="College">College</option>
                          <option value="Post Graduate">Post Graduate</option>
                          <option value="None">None</option>
                      </select>
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field third-width">
                      <label>Blood Type</label>
                      <select id="blood_type" name="blood_type">
                          <option value="">Select Blood Type</option>
                          <option value="A+">A+</option>
                          <option value="A-">A-</option>
                          <option value="B+">B+</option>
                          <option value="B-">B-</option>
                          <option value="AB+">AB+</option>
                          <option value="AB-">AB-</option>
                          <option value="O+">O+</option>
                          <option value="O-">O-</option>
                      </select>
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Height (cm)</label>
                      <input type="number" id="height" name="height" step="0.01" min="0" placeholder="Height in cm">
                  </div>
                  <div class="cs-form-field third-width">
                      <label>Weight (kg)</label>
                      <input type="number" id="weight" name="weight" step="0.01" min="0" placeholder="Weight in kg">
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>Sector</label>
                      <select id="sector" name="sector">
                          <option value="None">None</option>
                          <option value="Senior Citizen">Senior Citizen</option>
                          <option value="PWD">PWD</option>
                          <option value="Solo Parent">Solo Parent</option>
                          <option value="Indigenous People">Indigenous People</option>
                          <option value="Youth">Youth</option>
                      </select>
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Registered Voter</label>
                      <div class="cs-form-checkbox" style="margin-top: 5px;">
                          <input type="checkbox" id="is_voter" name="is_voter">
                          <label for="is_voter" style="display: inline; border: none; background: none; padding: 0; font-size: 12px;">Yes</label>
                      </div>
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
                      <input type="text" id="philhealth_no" name="philhealth_no">
                  </div>
                  <div class="cs-form-field half-width">
                      <label>SSS Number</label>
                      <input type="text" id="sss_no" name="sss_no">
                  </div>
              </div>
              
              <div class="cs-form-row">
                  <div class="cs-form-field half-width">
                      <label>TIN Number</label>
                      <input type="text" id="tin_no" name="tin_no">
                  </div>
                  <div class="cs-form-field half-width">
                      <label>Pag-IBIG Number</label>
                      <input type="text" id="pagibig_no" name="pagibig_no">
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Remarks Section -->
      <div class="cs-form-section">
          <div class="cs-form-section-header">V. Remarks</div>
          <div class="cs-form-section-content">
              <div class="cs-form-row">
                  <div class="cs-form-field full-width">
                      <label>Additional Notes/Remarks</label>
                      <textarea id="remarks" name="remarks" rows="3"></textarea>
                  </div>
              </div>
          </div>
      </div>
      
      <!-- Submit Buttons -->
      <div class="cs-form-buttons">
          <button type="submit" class="btn btn-success">
              <i class="fas fa-save"></i> Save Resident
          </button>
          <a href="../../residents.php" class="btn btn-danger">
              <i class="fas fa-times"></i> Cancel
          </a>
      </div>
  </form>

</div>

<script>
// Preview uploaded image
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const container = document.querySelector('.profile-image-container');
            container.innerHTML = `<img src="${event.target.result}" class="profile-image" alt="Profile Image">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include '../includes/footer.php'; ?>
