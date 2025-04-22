<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: ../../blotter.php");
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
   header("Location: ../../blotter.php?error=1");
   exit();
}

$blotter = $blotter_result->fetch_assoc();

// Log the view action
logActivity($_SESSION['user_id'], "Viewed blotter report: {$blotter['blotter_id']}");

// Include the header
$pageTitle = "View Blotter Report";
include '../includes/header.php';
?>

<!-- Blotter Details -->
<div class="form-container">
  <div class="blotter-header">
    <div class="blotter-title">
      <h2><i class="fas fa-book"></i> Blotter Report</h2>
      <span class="blotter-id"><?php echo $blotter['blotter_id']; ?></span>
    </div>
    <div class="blotter-actions">
      <a href="../blotter/edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
      </a>
      <a href="../../blotter.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </div>
  
  <div class="blotter-card">
    <div class="blotter-status-badge <?php echo strtolower($blotter['status']); ?>">
      <?php echo $blotter['status']; ?>
    </div>
    
    <div class="blotter-section">
      <div class="blotter-section-title">Incident Information</div>
      <div class="blotter-grid">
        <div class="blotter-field">
          <div class="field-label">Incident Type</div>
          <div class="field-value incident-type"><?php echo $blotter['incident_type']; ?></div>
        </div>
        
        <div class="blotter-field">
          <div class="field-label">Incident Date</div>
          <div class="field-value">
            <i class="fas fa-calendar-day text-primary"></i>
            <?php echo date('F d, Y', strtotime($blotter['incident_date'])); ?>
          </div>
        </div>
      </div>
      
      <div class="blotter-field full-width">
        <div class="field-label">Incident Location</div>
        <div class="field-value location-text">
          <i class="fas fa-map-marker-alt text-danger"></i>
          <?php echo $blotter['incident_location']; ?>
        </div>
      </div>
    </div>
    
    <div class="blotter-section">
      <div class="blotter-section-title">Involved Parties</div>
      <div class="blotter-grid">
        <div class="blotter-field">
          <div class="field-label">Complainant</div>
          <div class="field-value party-card complainant">
            <?php if ($blotter['complainant_id']): ?>
              <a href="../residents/view.php?id=<?php echo $blotter['complainant_id']; ?>" class="party-link">
                <i class="fas fa-user-circle"></i>
                <?php echo $blotter['complainant_first_name'] . ' ' . $blotter['complainant_middle_name'] . ' ' . $blotter['complainant_last_name']; ?>
              </a>
            <?php else: ?>
              <span class="non-resident">
                <i class="fas fa-user-slash"></i>
                Non-resident complainant
              </span>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="blotter-field">
          <div class="field-label">Respondent</div>
          <div class="field-value party-card respondent">
            <?php if ($blotter['respondent_id']): ?>
              <a href="../residents/view.php?id=<?php echo $blotter['respondent_id']; ?>" class="party-link">
                <i class="fas fa-user-circle"></i>
                <?php echo $blotter['respondent_first_name'] . ' ' . $blotter['respondent_middle_name'] . ' ' . $blotter['respondent_last_name']; ?>
              </a>
            <?php else: ?>
              <span class="non-resident">
                <i class="fas fa-user-slash"></i>
                Non-resident respondent
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    
    <div class="blotter-section">
      <div class="blotter-section-title">Incident Details</div>
      <div class="blotter-field full-width">
        <div class="field-value details-text">
          <?php echo nl2br($blotter['incident_details']); ?>
        </div>
      </div>
    </div>
    
    <?php if (!empty($blotter['resolution'])): ?>
    <div class="blotter-section">
      <div class="blotter-section-title">Resolution</div>
      <div class="blotter-field full-width">
        <div class="field-value resolution-text">
          <?php echo nl2br($blotter['resolution']); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="blotter-section">
      <div class="blotter-section-title">Case Management</div>
      <div class="blotter-grid">
        <div class="blotter-field">
          <div class="field-label">Handled By</div>
          <div class="field-value">
            <i class="fas fa-user-shield text-info"></i>
            <?php echo $blotter['handled_by_name'] ?: 'Not assigned'; ?>
          </div>
        </div>
        
        <div class="blotter-field">
          <div class="field-label">Date Filed</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($blotter['created_at'])); ?>
          </div>
        </div>
        
        <div class="blotter-field">
          <div class="field-label">Last Updated</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($blotter['updated_at'])); ?>
          </div>
        </div>
      </div>
    </div>
    
    <?php if (!empty($blotter['remarks'])): ?>
    <div class="blotter-section">
      <div class="blotter-section-title">Additional Notes</div>
      <div class="blotter-field full-width">
        <div class="field-value remarks-text">
          <?php echo nl2br($blotter['remarks']); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.blotter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #2563eb;
}

.blotter-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.blotter-title h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.blotter-title h2 i {
  color: #2563eb;
}

.blotter-id {
  background-color: #e0e7ff;
  color: #4338ca;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
}

.blotter-actions {
  display: flex;
  gap: 0.5rem;
}

.blotter-card {
  background-color: #fff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  padding: 0;
  position: relative;
  overflow: hidden;
}

.blotter-status-badge {
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

.blotter-status-badge.resolved {
  background-color: #10b981;
}

.blotter-status-badge.pending {
  background-color: #f59e0b;
}

.blotter-status-badge.ongoing {
  background-color: #3b82f6;
}

.blotter-status-badge.cancelled {
  background-color: #ef4444;
}

.blotter-section {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.blotter-section:last-child {
  border-bottom: none;
}

.blotter-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px dashed #e5e7eb;
}

.blotter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.blotter-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.blotter-field.full-width {
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

.incident-type {
  font-weight: 600;
  color: #4b5563;
}

.location-text {
  background-color: #f9fafb;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}

.party-card {
  padding: 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.party-card.complainant {
  background-color: #fee2e2;
  border-color: #fecaca;
}

.party-card.respondent {
  background-color: #e0f2fe;
  border-color: #bae6fd;
}

.party-link {
  color: #2563eb;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.2s;
}

.party-link:hover {
  color: #1d4ed8;
  text-decoration: underline;
}

.non-resident {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6b7280;
  font-style: italic;
}

.details-text {
  background-color: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #2563eb;
  white-space: pre-line;
}

.resolution-text {
  background-color: #ecfdf5;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #10b981;
  white-space: pre-line;
}

.remarks-text {
  background-color: #fffbeb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #f59e0b;
  font-style: italic;
}

@media (max-width: 640px) {
  .blotter-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .blotter-actions {
    width: 100%;
    justify-content: flex-start;
  }
  
  .blotter-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
