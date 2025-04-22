<?php
require_once '../config.php';
requireLogin();

// Get all residents for dropdown
$residents_query = "SELECT * FROM residents ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $complainant_id = !empty($_POST['complainant_id']) ? sanitize($_POST['complainant_id']) : NULL;
    $respondent_id = !empty($_POST['respondent_id']) ? sanitize($_POST['respondent_id']) : NULL;
    $incident_type = sanitize($_POST['incident_type']);
    $incident_date = sanitize($_POST['incident_date']);
    $incident_location = sanitize($_POST['incident_location']);
    $incident_details = sanitize($_POST['incident_details']);
    $status = sanitize($_POST['status']);
    $resolution = sanitize($_POST['resolution']);
    $remarks = sanitize($_POST['remarks']);
    
    // Generate unique blotter ID
    $blotter_id = generateUniqueID('BLT');
    
    // Insert blotter data
    $sql = "INSERT INTO blotter (
                blotter_id, complainant_id, respondent_id, incident_type, 
                incident_date, incident_location, incident_details, status, 
                resolution, handled_by, remarks
            ) VALUES (
                '$blotter_id', " . ($complainant_id ? "$complainant_id" : "NULL") . ", 
                " . ($respondent_id ? "$respondent_id" : "NULL") . ", '$incident_type', 
                '$incident_date', '$incident_location', '$incident_details', '$status', 
                '$resolution', {$_SESSION['user_id']}, '$remarks'
            )";
    
    if ($conn->query($sql) === TRUE) {
        // Log activity
        logActivity($_SESSION['user_id'], "Added new blotter report: $incident_type");
        
        // Redirect to blotter page with success message
        header("Location: ../../blotter.php?success=1");
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Include the header
$pageTitle = "Add Blotter Report";
include '../includes/header.php';
?>

<!-- Add Blotter Form -->
<div class="form-container">
    <div class="form-title">Blotter Report Information</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <!-- Involved Parties -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="complainant_id">Complainant</label>
                    <select id="complainant_id" name="complainant_id" class="form-control">
                        <option value="">Select Complainant (Optional)</option>
                        <?php if($residents && $residents->num_rows > 0): ?>
                            <?php while($resident = $residents->fetch_assoc()): ?>
                            <option value="<?php echo $resident['id']; ?>">
                                <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                            </option>
                            <?php endwhile; ?>
                            <?php $residents->data_seek(0); // Reset pointer to beginning ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text">Leave blank if complainant is not a registered resident.</small>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="respondent_id">Respondent</label>
                    <select id="respondent_id" name="respondent_id" class="form-control">
                        <option value="">Select Respondent (Optional)</option>
                        <?php if($residents && $residents->num_rows > 0): ?>
                            <?php while($resident = $residents->fetch_assoc()): ?>
                            <option value="<?php echo $resident['id']; ?>">
                                <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                    <small class="form-text">Leave blank if respondent is not a registered resident.</small>
                </div>
            </div>
        </div>
        
        <!-- Incident Information -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="incident_type">Incident Type *</label>
                    <input type="text" id="incident_type" name="incident_type" class="form-control" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="incident_date">Incident Date *</label>
                    <input type="date" id="incident_date" name="incident_date" class="form-control" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Pending">Pending</option>
                        <option value="Ongoing">Ongoing</option>
                        <option value="Resolved">Resolved</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="incident_location">Incident Location *</label>
            <input type="text" id="incident_location" name="incident_location" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="incident_details">Incident Details *</label>
            <textarea id="incident_details" name="incident_details" class="form-control" rows="5" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="resolution">Resolution (if any)</label>
            <textarea id="resolution" name="resolution" class="form-control" rows="3"></textarea>
        </div>
        
        <!-- Remarks -->
        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control"></textarea>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save Blotter Report
            </button>
            <a href="../../blotter.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
