<?php
require_once 'php/config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: blotter.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get blotter information with complainant and respondent names
$blotter_query = "SELECT b.*, 
                c.first_name as complainant_first_name, c.middle_name as complainant_middle_name, c.last_name as complainant_last_name,
                r.first_name as respondent_first_name, r.middle_name as respondent_middle_name, r.last_name as respondent_last_name,
                u.full_name as handled_by_name
                FROM blotter b 
                LEFT JOIN residents c ON b.complainant_id = c.id
                LEFT JOIN residents r ON b.respondent_id = r.id
                LEFT JOIN users u ON b.handled_by = u.id
                WHERE b.id = $id";
$blotter_result = $conn->query($blotter_query);

if (!$blotter_result || $blotter_result->num_rows == 0) {
    header("Location: blotter.php?error=1");
    exit();
}

$blotter = $blotter_result->fetch_assoc();

// Log the view action
logActivity($_SESSION['user_id'], "Viewed blotter report: {$blotter['blotter_id']}");

// Include the header
include 'php/includes/header_main.php';
?>

<!-- Blotter Details -->
<div class="form-container">
    <div class="form-title">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-book"></i> Blotter Report Details</span>
            <div>
                <a href="edit_blotter.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="blotter.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
    
    <div class="data-view">
        <div class="data-row">
            <div class="data-label">Blotter ID:</div>
            <div class="data-value"><?php echo $blotter['blotter_id']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Status:</div>
            <div class="data-value">
                <span class="badge badge-<?php echo strtolower($blotter['status']) == 'resolved' ? 'success' : (strtolower($blotter['status']) == 'pending' ? 'warning' : (strtolower($blotter['status']) == 'ongoing' ? 'info' : 'danger')); ?>">
                    <?php echo $blotter['status']; ?>
                </span>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Complainant:</div>
            <div class="data-value">
                <?php if ($blotter['complainant_id']): ?>
                    <a href="view_resident.php?id=<?php echo $blotter['complainant_id']; ?>">
                        <?php echo $blotter['complainant_first_name'] . ' ' . $blotter['complainant_middle_name'] . ' ' . $blotter['complainant_last_name']; ?>
                    </a>
                <?php else: ?>
                    <span class="badge badge-warning">Non-resident complainant</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Respondent:</div>
            <div class="data-value">
                <?php if ($blotter['respondent_id']): ?>
                    <a href="view_resident.php?id=<?php echo $blotter['respondent_id']; ?>">
                        <?php echo $blotter['respondent_first_name'] . ' ' . $blotter['respondent_middle_name'] . ' ' . $blotter['respondent_last_name']; ?>
                    </a>
                <?php else: ?>
                    <span class="badge badge-warning">Non-resident respondent</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Incident Type:</div>
            <div class="data-value"><?php echo $blotter['incident_type']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Incident Date:</div>
            <div class="data-value"><?php echo date('F d, Y', strtotime($blotter['incident_date'])); ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Incident Location:</div>
            <div class="data-value"><?php echo $blotter['incident_location']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Incident Details:</div>
            <div class="data-value"><?php echo nl2br($blotter['incident_details']); ?></div>
        </div>
        
        <?php if (!empty($blotter['resolution'])): ?>
        <div class="data-row">
            <div class="data-label">Resolution:</div>
            <div class="data-value"><?php echo nl2br($blotter['resolution']); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="data-row">
            <div class="data-label">Handled By:</div>
            <div class="data-value"><?php echo $blotter['handled_by_name'] ?: 'Not assigned'; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Date Filed:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($blotter['created_at'])); ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Last Updated:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($blotter['updated_at'])); ?></div>
        </div>
        
        <?php if (!empty($blotter['remarks'])): ?>
        <div class="data-row">
            <div class="data-label">Remarks:</div>
            <div class="data-value"><?php echo nl2br($blotter['remarks']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'php/includes/footer_main.php'; ?>
