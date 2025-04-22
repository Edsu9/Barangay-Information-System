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
                r.first_name, r.middle_name, r.last_name,
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

// Log the view action
logActivity($_SESSION['user_id'], "Viewed document: {$document['document_id']}");

// Include the header
include 'php/includes/header_main.php';
?>

<!-- Document Details -->
<div class="form-container">
    <div class="form-title">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fas fa-file-alt"></i> Document Details</span>
            <div>
                <a href="edit_document.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="print_document.php?id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
                    <i class="fas fa-print"></i> Print
                </a>
                <a href="documents.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
    
    <div class="data-view">
        <div class="data-row">
            <div class="data-label">Document ID:</div>
            <div class="data-value"><?php echo $document['document_id']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Document Type:</div>
            <div class="data-value"><?php echo $document['document_type']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Resident:</div>
            <div class="data-value">
                <a href="view_resident.php?id=<?php echo $document['resident_id']; ?>">
                    <?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?>
                </a>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Purpose:</div>
            <div class="data-value"><?php echo nl2br($document['purpose']); ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Issue Date:</div>
            <div class="data-value"><?php echo date('F d, Y', strtotime($document['issue_date'])); ?></div>
        </div>
        
        <?php if ($document['expiry_date']): ?>
        <div class="data-row">
            <div class="data-label">Expiry Date:</div>
            <div class="data-value"><?php echo date('F d, Y', strtotime($document['expiry_date'])); ?></div>
        </div>
        <?php endif; ?>
        
        <div class="data-row">
            <div class="data-label">Status:</div>
            <div class="data-value">
                <span class="badge badge-<?php echo strtolower($document['status']) == 'issued' ? 'success' : (strtolower($document['status']) == 'pending' ? 'warning' : 'danger'); ?>">
                    <?php echo $document['status']; ?>
                </span>
            </div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Issued By:</div>
            <div class="data-value"><?php echo $document['issued_by_name'] ?: 'Not assigned'; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Date Created:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($document['created_at'])); ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Last Updated:</div>
            <div class="data-value"><?php echo date('F d, Y h:i A', strtotime($document['updated_at'])); ?></div>
        </div>
        
        <?php if (!empty($document['remarks'])): ?>
        <div class="data-row">
            <div class="data-label">Remarks:</div>
            <div class="data-value"><?php echo nl2br($document['remarks']); ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'php/includes/footer_main.php'; ?>
