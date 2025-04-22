<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
  header("Location: ../../documents.php");
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
  header("Location: ../../documents.php?error=1");
  exit();
}

$document = $document_result->fetch_assoc();

// Log the view action
logActivity($_SESSION['user_id'], "Viewed document: {$document['document_id']}");

// Include the header
$pageTitle = "View Document";
include '../includes/header.php';
?>

<!-- Document Details -->
<div class="form-container">
  <div class="document-header">
    <div class="document-title">
      <h2><i class="fas fa-file-alt"></i> Document Information</h2>
      <span class="document-id"><?php echo $document['document_id']; ?></span>
    </div>
    <div class="document-actions">
      <a href="../documents/edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
      </a>
      <a href="../../print_document.php?id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
        <i class="fas fa-print"></i> Print
      </a>
      <a href="../../documents.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </div>
  
  <div class="document-card">
    <div class="document-status-badge <?php echo strtolower($document['status']); ?>">
      <?php echo $document['status']; ?>
    </div>
    
    <div class="document-section">
      <div class="document-section-title">Basic Information</div>
      <div class="document-grid">
        <div class="document-field">
          <div class="field-label">Document Type</div>
          <div class="field-value"><?php echo $document['document_type']; ?></div>
        </div>
        
        <div class="document-field">
          <div class="field-label">Resident</div>
          <div class="field-value">
            <a href="../residents/view.php?id=<?php echo $document['resident_id']; ?>" class="resident-link">
              <?php echo $document['first_name'] . ' ' . $document['middle_name'] . ' ' . $document['last_name']; ?>
              <i class="fas fa-external-link-alt"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <div class="document-section">
      <div class="document-section-title">Document Details</div>
      <div class="document-field full-width">
        <div class="field-label">Purpose</div>
        <div class="field-value purpose-text"><?php echo nl2br($document['purpose']); ?></div>
      </div>
      
      <div class="document-grid">
        <div class="document-field">
          <div class="field-label">Issue Date</div>
          <div class="field-value">
            <i class="fas fa-calendar-alt text-primary"></i>
            <?php echo date('F d, Y', strtotime($document['issue_date'])); ?>
          </div>
        </div>
        
        <?php if ($document['expiry_date']): ?>
        <div class="document-field">
          <div class="field-label">Expiry Date</div>
          <div class="field-value">
            <i class="fas fa-calendar-times text-danger"></i>
            <?php echo date('F d, Y', strtotime($document['expiry_date'])); ?>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="document-field">
          <div class="field-label">Issued By</div>
          <div class="field-value">
            <i class="fas fa-user-shield text-info"></i>
            <?php echo $document['issued_by_name'] ?: 'Not assigned'; ?>
          </div>
        </div>
      </div>
    </div>
    
    <div class="document-section">
      <div class="document-section-title">System Information</div>
      <div class="document-grid">
        <div class="document-field">
          <div class="field-label">Date Created</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($document['created_at'])); ?>
          </div>
        </div>
        
        <div class="document-field">
          <div class="field-label">Last Updated</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($document['updated_at'])); ?>
          </div>
        </div>
      </div>
    </div>
    
    <?php if (!empty($document['remarks'])): ?>
    <div class="document-section">
      <div class="document-section-title">Additional Notes</div>
      <div class="document-field full-width">
        <div class="field-value remarks-text">
          <?php echo nl2br($document['remarks']); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.document-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #2563eb;
}

.document-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.document-title h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.document-title h2 i {
  color: #2563eb;
}

.document-id {
  background-color: #e0e7ff;
  color: #4338ca;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
}

.document-actions {
  display: flex;
  gap: 0.5rem;
}

.document-card {
  background-color: #fff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  padding: 0;
  position: relative;
  overflow: hidden;
}

.document-status-badge {
  position: absolute;
  top: 0;
  right: 2rem;
  padding: 0.25rem 1.5rem;
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.document-status-badge.issued {
  background-color: #10b981;
}

.document-status-badge.pending {
  background-color: #f59e0b;
}

.document-status-badge.cancelled {
  background-color: #ef4444;
}

.document-status-badge.expired {
  background-color: #6b7280;
}

.document-section {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.document-section:last-child {
  border-bottom: none;
}

.document-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px dashed #e5e7eb;
}

.document-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.document-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.document-field.full-width {
  grid-column: 1 / -1;
}

.field-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
}

.field-value {
  font-size: 1rem;
  color: #1f2937;
}

.field-value i {
  margin-right: 0.5rem;
  width: 16px;
  text-align: center;
}

.text-primary {
  color: #2563eb;
}

.text-danger {
  color: #dc2626;
}

.text-info {
  color: #0ea5e9;
}

.text-muted {
  color: #6b7280;
  font-size: 0.875rem;
}

.resident-link {
  color: #2563eb;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.2s;
}

.resident-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

.resident-link i {
  font-size: 0.75rem;
}

.purpose-text {
  background-color: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #2563eb;
}

.remarks-text {
  background-color: #fffbeb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #f59e0b;
  font-style: italic;
}

@media (max-width: 640px) {
  .document-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .document-actions {
    width: 100%;
    justify-content: flex-start;
  }
  
  .document-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
