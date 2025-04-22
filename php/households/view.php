<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
   header("Location: ../../households.php");
   exit();
}

$id = sanitize($_GET['id']);

// Get household information
$household_query = "SELECT h.*, r.first_name, r.middle_name, r.last_name 
                  FROM households h 
                  LEFT JOIN residents r ON h.head_of_family_id = r.id 
                  WHERE h.id = $id";
$household_result = $conn->query($household_query);

if (!$household_result || $household_result->num_rows == 0) {
   header("Location: ../../households.php?error=1");
   exit();
}

$household = $household_result->fetch_assoc();

// Get household members
$members_query = "SELECT * FROM residents WHERE household_id = $id ORDER BY is_head_of_family DESC, last_name, first_name ASC";
$members = $conn->query($members_query);

// Count members
$member_count = $members ? $members->num_rows : 0;

// Include the header
$pageTitle = "View Household";
include '../includes/header.php';
?>

<!-- Household Details -->
<div class="form-container">
  <div class="household-header">
    <div class="household-title">
      <h2><i class="fas fa-home"></i> Household Information</h2>
      <span class="household-id"><?php echo $household['household_id']; ?></span>
    </div>
    <div class="household-actions">
      <a href="../households/edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
      </a>
      <a href="../../households.php" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>
  </div>
  
  <div class="household-card">
    <div class="household-section">
      <div class="household-section-title">Basic Information</div>
      <div class="household-grid">
        <div class="household-field">
          <div class="field-label">Family Name</div>
          <div class="field-value family-name"><?php echo $household['family_name']; ?></div>
        </div>
        
        <div class="household-field">
          <div class="field-label">Monthly Income</div>
          <div class="field-value">
            <i class="fas fa-money-bill-wave text-success"></i>
            <?php echo '₱ ' . number_format($household['monthly_income'], 2); ?>
          </div>
        </div>
      </div>
      
      <div class="household-field full-width">
        <div class="field-label">Address</div>
        <div class="field-value address-text">
          <i class="fas fa-map-marker-alt text-danger"></i>
          <?php echo $household['address']; ?>
        </div>
      </div>
    </div>
    
    <div class="household-section">
      <div class="household-section-title">Head of Family</div>
      <div class="household-field full-width">
        <div class="field-value head-of-family">
          <?php if ($household['head_of_family_id']): ?>
            <div class="head-card">
              <div class="head-icon">
                <i class="fas fa-user"></i>
              </div>
              <div class="head-details">
                <a href="../residents/view.php?id=<?php echo $household['head_of_family_id']; ?>" class="head-name">
                  <?php echo $household['first_name'] . ' ' . $household['middle_name'] . ' ' . $household['last_name']; ?>
                </a>
                <span class="head-label">Head of Family</span>
              </div>
              <div class="head-action">
                <a href="../residents/view.php?id=<?php echo $household['head_of_family_id']; ?>" class="btn btn-sm btn-primary">
                  <i class="fas fa-eye"></i> View
                </a>
              </div>
            </div>
          <?php else: ?>
            <div class="no-head">
              <i class="fas fa-user-slash"></i>
              <span>No Head of Family Assigned</span>
              <a href="../residents/add_to_household.php?household_id=<?php echo $id; ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus"></i> Add Member
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <?php if (!empty($household['remarks'])): ?>
    <div class="household-section">
      <div class="household-section-title">Remarks</div>
      <div class="household-field full-width">
        <div class="field-value remarks-text">
          <?php echo nl2br($household['remarks']); ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    
    <div class="household-section">
      <div class="household-section-title">System Information</div>
      <div class="household-grid">
        <div class="household-field">
          <div class="field-label">Date Added</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($household['created_at'])); ?>
          </div>
        </div>
        
        <div class="household-field">
          <div class="field-label">Last Updated</div>
          <div class="field-value text-muted">
            <?php echo date('F d, Y h:i A', strtotime($household['updated_at'])); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Household Members -->
<div class="form-container">
  <div class="members-header">
    <div class="members-title">
      <h2><i class="fas fa-users"></i> Household Members</h2>
      <span class="members-count"><?php echo $member_count; ?> <?php echo $member_count == 1 ? 'member' : 'members'; ?></span>
    </div>
    <a href="../residents/add_to_household.php?household_id=<?php echo $id; ?>" class="btn btn-success">
      <i class="fas fa-user-plus"></i> Add Member
    </a>
  </div>
  
  <div class="members-card">
    <?php if($members && $members->num_rows > 0): ?>
      <div class="members-list">
        <?php while($member = $members->fetch_assoc()): ?>
          <?php 
            $birthdate = new DateTime($member['birth_date']);
            $today = new DateTime();
            $age = $birthdate->diff($today)->y;
          ?>
          <div class="member-item <?php echo $member['is_head_of_family'] ? 'is-head' : ''; ?>">
            <div class="member-avatar">
              <?php if (!empty($member['profile_image'])): ?>
                <img src="../../<?php echo $member['profile_image']; ?>" alt="Profile Image">
              <?php else: ?>
                <div class="avatar-placeholder">
                  <i class="fas fa-user"></i>
                </div>
              <?php endif; ?>
            </div>
            <div class="member-details">
              <div class="member-name">
                <?php echo $member['last_name'] . ', ' . $member['first_name'] . ' ' . $member['middle_name']; ?>
                <?php if($member['is_head_of_family']): ?>
                  <span class="head-badge">Head of Family</span>
                <?php endif; ?>
              </div>
              <div class="member-info">
                <span><i class="fas fa-venus-mars"></i> <?php echo $member['gender']; ?></span>
                <span><i class="fas fa-birthday-cake"></i> <?php echo $age; ?> years old</span>
                <span><i class="fas fa-id-card"></i> <?php echo $member['resident_id']; ?></span>
              </div>
            </div>
            <div class="member-actions">
              <a href="../residents/view.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-primary" title="View">
                <i class="fas fa-eye"></i>
              </a>
              <a href="../residents/edit.php?id=<?php echo $member['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <a href="../residents/remove_from_household.php?id=<?php echo $member['id']; ?>&household_id=<?php echo $id; ?>" 
                class="btn btn-sm btn-danger" 
                title="Remove from Household"
                onclick="return confirm('Are you sure you want to remove this member from the household?')">
                <i class="fas fa-user-minus"></i>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="no-members">
        <i class="fas fa-users-slash"></i>
        <p>No members found in this household</p>
        <a href="../residents/add_to_household.php?household_id=<?php echo $id; ?>" class="btn btn-success">
          <i class="fas fa-user-plus"></i> Add Member
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
.household-header, .members-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #2563eb;
}

.household-title, .members-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.household-title h2, .members-title h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  color: #1f2937;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.household-title h2 i, .members-title h2 i {
  color: #2563eb;
}

.household-id, .members-count {
  background-color: #e0e7ff;
  color: #4338ca;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
}

.household-actions {
  display: flex;
  gap: 0.5rem;
}

.household-card, .members-card {
  background-color: #fff;
  border-radius: 0.75rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
  padding: 0;
  overflow: hidden;
}

.household-section {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.household-section:last-child {
  border-bottom: none;
}

.household-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px dashed #e5e7eb;
}

.household-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.household-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.household-field.full-width {
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

.text-success {
  color: #10b981;
}

.text-danger {
  color: #ef4444;
}

.text-muted {
  color: #6b7280;
  font-size: 0.875rem;
}

.family-name {
  font-weight: 600;
  font-size: 1.125rem;
}

.address-text {
  background-color: #f9fafb;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
}

.head-of-family {
  padding: 0;
}

.head-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f0f9ff;
  border-radius: 0.5rem;
  border: 1px solid #bae6fd;
}

.head-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background-color: #0ea5e9;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.head-details {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.head-name {
  font-weight: 600;
  color: #0369a1;
  text-decoration: none;
}

.head-name:hover {
  text-decoration: underline;
}

.head-label {
  font-size: 0.75rem;
  color: #0ea5e9;
  font-weight: 500;
}

.no-head {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #fef2f2;
  border-radius: 0.5rem;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.no-head i {
  font-size: 1.25rem;
}

.remarks-text {
  background-color: #fffbeb;
  padding: 1rem;
  border-radius: 0.5rem;
  border-left: 4px solid #f59e0b;
  font-style: italic;
}

.members-list {
  display: flex;
  flex-direction: column;
}

.member-item {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s;
}

.member-item:last-child {
  border-bottom: none;
}

.member-item:hover {
  background-color: #f9fafb;
}

.member-item.is-head {
  background-color: #f0f9ff;
}

.member-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 1rem;
  flex-shrink: 0;
}

.member-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background-color: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
}

.member-details {
  flex: 1;
}

.member-name {
  font-weight: 500;
  margin-bottom: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.head-badge {
  background-color: #0ea5e9;
  color: white;
  font-size: 0.75rem;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
}

.member-info {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.member-info span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.member-actions {
  display: flex;
  gap: 0.5rem;
}

.no-members {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #6b7280;
}

.no-members i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.no-members p {
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .household-header, .members-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .household-actions {
    width: 100%;
  }
  
  .household-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .member-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .member-avatar {
    margin-right: 0;
  }
  
  .member-info {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .member-actions {
    width: 100%;
    justify-content: flex-start;
    margin-top: 0.5rem;
  }
}
</style>

<?php include '../includes/footer.php'; ?>
