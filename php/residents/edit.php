<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../../residents.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get resident information
$resident_query = "SELECT * FROM residents WHERE id = $id";
$resident_result = $conn->query($resident_query);

if (!$resident_result || $resident_result->num_rows == 0) {
    header("Location: ../../residents.php?error=1");
    exit();
}

$resident = $resident_result->fetch_assoc();

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
    $profile_image = $resident['profile_image']; // Keep existing image by default
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
                // If there was a previous image, you might want to delete it
                if ($profile_image && file_exists('../../' . $profile_image)) {
                    unlink('../../' . $profile_image);
                }
                $profile_image = 'uploads/profiles/' . $new_filename;
            }
        }
    }
    
    // Update resident data
    $sql = "UPDATE residents SET 
            first_name = '$first_name', 
            middle_name = '$middle_name', 
            last_name = '$last_name', 
            profile_image = " . ($profile_image ? "'$profile_image'" : "NULL") . ",
            birth_date = '$birth_date', 
            gender = '$gender', 
            civil_status = '$civil_status', 
            contact_number = '$contact_number', 
            email = '$email', 
            occupation = '$occupation', 
            educational_attainment = '$educational_attainment', 
            address = '$address', 
            is_voter = $is_voter, 
            is_head_of_family = $is_head_of_family, 
            nationality = '$nationality', 
            religion = '$religion', 
            sector = '$sector', 
            remarks = '$remarks',
            household_id = " . ($household_id ? "$household_id" : "NULL") . ",
            id_card_no = '$id_card_no',
            blood_type = '$blood_type',
            height = '$height',
            weight = '$weight',
            philhealth_no = '$philhealth_no',
            sss_no = '$sss_no',
            tin_no = '$tin_no',
            pagibig_no = '$pagibig_no',
            emergency_contact_name = '$emergency_contact_name',
            emergency_contact_number = '$emergency_contact_number',
            emergency_contact_relationship = '$emergency_contact_relationship'
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // If resident is head of family, update household
        if ($is_head_of_family && $household_id) {
            // First, reset any existing head of family in this household
            $reset_head = "UPDATE residents SET is_head_of_family = 0 
                          WHERE household_id = $household_id AND id != $id";
            $conn->query($reset_head);
            
            // Then set this resident as head of family
            $update_household = "UPDATE households SET head_of_family_id = $id 
                               WHERE id = $household_id";
            $conn->query($update_household);
        }
        
        // Log activity
        logActivity($_SESSION['user_id'], "Updated resident: $first_name $last_name (ID: $id)");
        
        // Redirect to view page with success message
        header("Location: ../residents/view.php?id=$id&success=1");
        exit();
    } else {
        $error = "Error updating resident: " . $conn->error;
    }
}

// Include the header
$pageTitle = "Edit Resident";
include '../includes/header.php';
?>
<link rel="stylesheet" href="../../assets/css/cs-form.css">

<!-- Edit Resident Form -->
<div class="form-container">
    <div class="form-title">Edit Resident Information</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
<!-- Update the form styling to match the government form style -->

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
                         <?php if (!empty($resident['profile_image'])): ?>
                             <img src="../../<?php echo $resident['profile_image']; ?>" class="profile-image" alt="Profile Image">
                         <?php else: ?>
                             <div class="profile-image-placeholder">
                                 <i class="fas fa-user"></i>
                                 <span>2x2 ID Picture</span>
                             </div>
                         <?php endif; ?>
                     </div>
                     <div class="profile-image-upload" style="padding: 5px;">
                         <input type="file" name="profile_image" id="profile_image" accept="image/*" style="font-size: 10px;">
                     </div>
                 </div>
                 <div class="cs-form-field" style="flex: 1;">
                     <label>Resident ID</label>
                     <input type="text" id="resident_id" value="<?php echo $resident['resident_id']; ?>" disabled>
                     
                     <label>ID Card No.</label>
                     <input type="text" id="id_card_no" name="id_card_no" value="<?php echo $resident['id_card_no']; ?>">
                     
                     <label>Household</label>
                     <select id="household_id" name="household_id">
                         <option value="">Select Household (Optional)</option>
                         <?php while($household = $households->fetch_assoc()): ?>
                         <option value="<?php echo $household['id']; ?>" <?php echo ($resident['household_id'] == $household['id']) ? 'selected' : ''; ?>>
                             <?php echo $household['family_name'] . ' - ' . $household['address']; ?>
                         </option>
                         <?php endwhile; ?>
                     </select>
                     <div class="cs-form-checkbox" style="margin-top: 5px;">
                         <input type="checkbox" id="is_head_of_family" name="is_head_of_family" <?php echo $resident['is_head_of_family'] ? 'checked' : ''; ?>>
                         <label for="is_head_of_family" style="display: inline; border: none; background: none; padding: 0; font-size: 12px;">Head of Family</label>
                     </div>
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field third-width">
                     <label>Last Name</label>
                     <input type="text" id="last_name" name="last_name" value="<?php echo $resident['last_name']; ?>" required>
                 </div>
                 <div class="cs-form-field third-width">
                     <label>First Name</label>
                     <input type="text" id="first_name" name="first_name" value="<?php echo $resident['first_name']; ?>" required>
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Middle Name</label>
                     <input type="text" id="middle_name" name="middle_name" value="<?php echo $resident['middle_name']; ?>">
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field third-width">
                     <label>Birth Date</label>
                     <input type="date" id="birth_date" name="birth_date" value="<?php echo $resident['birth_date']; ?>" required>
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Gender</label>
                     <select id="gender" name="gender" required>
                         <option value="">Select Gender</option>
                         <option value="Male" <?php echo ($resident['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                         <option value="Female" <?php echo ($resident['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                         <option value="Other" <?php echo ($resident['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                     </select>
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Civil Status</label>
                     <select id="civil_status" name="civil_status" required>
                         <option value="">Select Civil Status</option>
                         <option value="Single" <?php echo ($resident['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                         <option value="Married" <?php echo ($resident['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                         <option value="Widowed" <?php echo ($resident['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                         <option value="Divorced" <?php echo ($resident['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                         <option value="Separated" <?php echo ($resident['civil_status'] == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                     </select>
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field half-width">
                     <label>Nationality</label>
                     <input type="text" id="nationality" name="nationality" value="<?php echo $resident['nationality']; ?>">
                 </div>
                 <div class="cs-form-field half-width">
                     <label>Religion</label>
                     <input type="text" id="religion" name="religion" value="<?php echo $resident['religion']; ?>">
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field full-width">
                     <label>Complete Address</label>
                     <textarea id="address" name="address" rows="2" required><?php echo $resident['address']; ?></textarea>
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
                     <input type="text" id="contact_number" name="contact_number" value="<?php echo $resident['contact_number']; ?>">
                 </div>
                 <div class="cs-form-field half-width">
                     <label>Email Address</label>
                     <input type="email" id="email" name="email" value="<?php echo $resident['email']; ?>">
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field third-width">
                     <label>Emergency Contact Name</label>
                     <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo $resident['emergency_contact_name']; ?>">
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Emergency Contact Number</label>
                     <input type="text" id="emergency_contact_number" name="emergency_contact_number" value="<?php echo $resident['emergency_contact_number']; ?>">
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Relationship</label>
                     <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="<?php echo $resident['emergency_contact_relationship']; ?>">
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
                     <input type="text" id="occupation" name="occupation" value="<?php echo $resident['occupation']; ?>">
                 </div>
                 <div class="cs-form-field half-width">
                     <label>Educational Attainment</label>
                     <select id="educational_attainment" name="educational_attainment">
                         <option value="">Select Educational Attainment</option>
                         <option value="Elementary" <?php echo ($resident['educational_attainment'] == 'Elementary') ? 'selected' : ''; ?>>Elementary</option>
                         <option value="High School" <?php echo ($resident['educational_attainment'] == 'High School') ? 'selected' : ''; ?>>High School</option>
                         <option value="Vocational" <?php echo ($resident['educational_attainment'] == 'Vocational') ? 'selected' : ''; ?>>Vocational</option>
                         <option value="College" <?php echo ($resident['educational_attainment'] == 'College') ? 'selected' : ''; ?>>College</option>
                         <option value="Post Graduate" <?php echo ($resident['educational_attainment'] == 'Post Graduate') ? 'selected' : ''; ?>>Post Graduate</option>
                         <option value="None" <?php echo ($resident['educational_attainment'] == 'None') ? 'selected' : ''; ?>>None</option>
                     </select>
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field third-width">
                     <label>Blood Type</label>
                     <select id="blood_type" name="blood_type">
                         <option value="">Select Blood Type</option>
                         <option value="A+" <?php echo ($resident['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                         <option value="A-" <?php echo ($resident['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                         <option value="B+" <?php echo ($resident['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                         <option value="B-" <?php echo ($resident['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                         <option value="AB+" <?php echo ($resident['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                         <option value="AB-" <?php echo ($resident['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                         <option value="O+" <?php echo ($resident['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                         <option value="O-" <?php echo ($resident['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                     </select>
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Height (cm)</label>
                     <input type="number" id="height" name="height" step="0.01" min="0" value="<?php echo $resident['height']; ?>" placeholder="Height in cm">
                 </div>
                 <div class="cs-form-field third-width">
                     <label>Weight (kg)</label>
                     <input type="number" id="weight" name="weight" step="0.01" min="0" value="<?php echo $resident['weight']; ?>" placeholder="Weight in kg">
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field half-width">
                     <label>Sector</label>
                     <select id="sector" name="sector">
                         <option value="None" <?php echo ($resident['sector'] == 'None') ? 'selected' : ''; ?>>None</option>
                         <option value="Senior Citizen" <?php echo ($resident['sector'] == 'Senior Citizen') ? 'selected' : ''; ?>>Senior Citizen</option>
                         <option value="PWD" <?php echo ($resident['sector'] == 'PWD') ? 'selected' : ''; ?>>PWD</option>
                         <option value="Solo Parent" <?php echo ($resident['sector'] == 'Solo Parent') ? 'selected' : ''; ?>>Solo Parent</option>
                         <option value="Indigenous People" <?php echo ($resident['sector'] == 'Indigenous People') ? 'selected' : ''; ?>>Indigenous People</option>
                         <option value="Youth" <?php echo ($resident['sector'] == 'Youth') ? 'selected' : ''; ?>>Youth</option>
                     </select>
                 </div>
                 <div class="cs-form-field half-width">
                     <label>Registered Voter</label>
                     <div class="cs-form-checkbox" style="margin-top: 5px;">
                         <input type="checkbox" id="is_voter" name="is_voter" <?php echo $resident['is_voter'] ? 'checked' : ''; ?>>
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
                     <input type="text" id="philhealth_no" name="philhealth_no" value="<?php echo $resident['philhealth_no']; ?>">
                 </div>
                 <div class="cs-form-field half-width">
                     <label>SSS Number</label>
                     <input type="text" id="sss_no" name="sss_no" value="<?php echo $resident['sss_no']; ?>">
                 </div>
             </div>
             
             <div class="cs-form-row">
                 <div class="cs-form-field half-width">
                     <label>TIN Number</label>
                     <input type="text" id="tin_no" name="tin_no" value="<?php echo $resident['tin_no']; ?>">
                 </div>
                 <div class="cs-form-field half-width">
                     <label>Pag-IBIG Number</label>
                     <input type="text" id="pagibig_no" name="pagibig_no" value="<?php echo $resident['pagibig_no']; ?>">
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
                     <textarea id="remarks" name="remarks" rows="3"><?php echo $resident['remarks']; ?></textarea>
                 </div>
             </div>
         </div>
     </div>
     
     <!-- Submit Buttons -->
     <div class="cs-form-buttons">
         <button type="submit" class="btn btn-success">
             <i class="fas fa-save"></i> Update Resident
         </button>
         <a href="../residents/view.php?id=<?php echo $id; ?>" class="btn btn-danger">
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
