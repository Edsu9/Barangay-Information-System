<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../../blotter.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get blotter information
$blotter_query = "SELECT * FROM blotter WHERE id = $id";
$blotter_result = $conn->query($blotter_query);

if (!$blotter_result || $blotter_result->num_rows == 0) {
    header("Location: ../../blotter.php?error=1");
    exit();
}

$blotter = $blotter_result->fetch_assoc();

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
    
    // Update blotter data
    $sql = "UPDATE blotter SET 
            complainant_id = " . ($complainant_id ? "$complainant_id" : "NULL") . ", 
            respondent_id = " . ($respondent_id ? "$respondent_id" : "NULL") . ", 
            incident_type = '$incident_type', 
            incident_date = '$incident_date', 
            incident_location = '$incident_location', 
            incident_details = '$incident_details', 
            status = '$status', 
            resolution = '$resolution', 
            remarks = '$remarks',
            handled_by = {$_SESSION['user_id']}
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Log activity
        logActivity($_SESSION['user_id'], "Updated blotter report: {$blotter['blotter_id']}");
        
        // Redirect to blotter page with success message
        header("Location: ../../blotter.php?success=2");
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Include the header
$pageTitle = "Edit Blotter Report";
include '../includes/header.php';
?>

<!-- Edit Blotter Form -->
<div class="form-container">
    <div class="form-title">Edit Blotter Report</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <!-- Blotter ID -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="blotter_id">Blotter ID</label>
                    <input type="text" id="blotter_id" class="form-control" value="<?php echo $blotter['blotter_id']; ?>" readonly>
                </div>
            </div>
        </div>
        
        <!-- Involved Parties -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="complainant_id">Complainant</label>
                    <select id="complainant_id" name="complainant_id" class="form-control">
                        <option value="">Select Complainant (Optional)</option>
                        <?php if($residents && $residents->num_rows > 0): ?>
                            <?php while($resident = $residents->fetch_assoc()): ?>
                            <option value="<?php echo $resident['id']; ?>" <?php echo ($resident['id'] == $blotter['complainant_id']) ? 'selected' : ''; ?>>
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
                            <option value="<?php echo $resident['id']; ?>" <?php echo ($resident['id'] == $blotter['respondent_id']) ? 'selected' : ''; ?>>
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
                    <input type="text" id="incident_type" name="incident_type" class="form-control" value="<?php echo $blotter['incident_type']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="incident_date">Incident Date *</label>
                    <input type="date" id="incident_date" name="incident_date" class="form-control" value="<?php echo $blotter['incident_date']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Pending" <?php echo ($blotter['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="Ongoing" <?php echo ($blotter['status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                        <option value="Resolved" <?php echo ($blotter['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                        <option value="Cancelled" <?php echo ($blotter['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="incident_location">Incident Location *</label>
            <input type="text" id="incident_location" name="incident_location" class="form-control" value="<?php echo $blotter['incident_location']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="incident_details">Incident Details *</label>
            <textarea id="incident_details" name="incident_details" class="form-control" rows="5" required><?php echo $blotter['incident_details']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="resolution">Resolution (if any)</label>
            <textarea id="resolution" name="resolution" class="form-control" rows="3"><?php echo $blotter['resolution']; ?></textarea>
        </div>
        
        <!-- Remarks -->
        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control"><?php echo $blotter['remarks']; ?></textarea>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Blotter Report
            </button>
            <a href="../../blotter.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
