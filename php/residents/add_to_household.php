<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['household_id'])) {
    header("Location: ../../households.php");
    exit();
}

$household_id = sanitize($_GET['household_id']);

// Get household information
$household_query = "SELECT * FROM households WHERE id = $household_id";
$household_result = $conn->query($household_query);

if (!$household_result || $household_result->num_rows == 0) {
    header("Location: ../../households.php?error=1");
    exit();
}

$household = $household_result->fetch_assoc();

// Get all residents who are not in any household
$residents_query = "SELECT * FROM residents WHERE household_id IS NULL ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resident_id']) && !empty($_POST['resident_id'])) {
        $resident_id = sanitize($_POST['resident_id']);
        $is_head = isset($_POST['is_head_of_family']) ? 1 : 0;
        
        // Update resident's household
        $update_sql = "UPDATE residents SET household_id = $household_id, is_head_of_family = $is_head WHERE id = $resident_id";
        
        if ($conn->query($update_sql) === TRUE) {
            // If set as head of family, update household
            if ($is_head) {
                // First, reset any existing head of family
                $reset_head = "UPDATE residents SET is_head_of_family = 0 
                              WHERE household_id = $household_id AND id != $resident_id";
                $conn->query($reset_head);
                
                // Then update household
                $update_household = "UPDATE households SET head_of_family_id = $resident_id 
                                   WHERE id = $household_id";
                $conn->query($update_household);
            }
            
            // Update household member count
            $count_query = "SELECT COUNT(*) as count FROM residents WHERE household_id = $household_id";
            $count_result = $conn->query($count_query);
            $count = $count_result->fetch_assoc()['count'];
            
            $update_count = "UPDATE households SET number_of_members = $count WHERE id = $household_id";
            $conn->query($update_count);
            
            // Log activity
            logActivity($_SESSION['user_id'], "Added resident to household: {$household['household_id']}");
            
            // Redirect back to household view
            header("Location: ../households/view.php?id=$household_id&success=1");
            exit();
        } else {
            $error = "Error updating resident: " . $conn->error;
        }
    } else {
        $error = "Please select a resident to add to the household.";
    }
}

// Include the header
$pageTitle = "Add Member to Household";
include '../includes/header.php';
?>

<!-- Add Member to Household Form -->
<div class="form-container">
    <div class="form-title">Add Member to Household</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <div class="data-view">
        <div class="data-row">
            <div class="data-label">Household ID:</div>
            <div class="data-value"><?php echo $household['household_id']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Family Name:</div>
            <div class="data-value"><?php echo $household['family_name']; ?></div>
        </div>
        
        <div class="data-row">
            <div class="data-label">Address:</div>
            <div class="data-value"><?php echo $household['address']; ?></div>
        </div>
    </div>
    
    <form action="" method="POST">
        <div class="form-group">
            <label for="resident_id">Select Resident to Add *</label>
            <select id="resident_id" name="resident_id" class="form-control" required>
                <option value="">Select Resident</option>
                <?php if($residents && $residents->num_rows > 0): ?>
                    <?php while($resident = $residents->fetch_assoc()): ?>
                    <option value="<?php echo $resident['id']; ?>">
                        <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                    </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <?php if($residents && $residents->num_rows == 0): ?>
                <small class="form-text text-danger">No available residents found. All residents are already assigned to households.</small>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="is_head_of_family" name="is_head_of_family" class="form-check-input">
                <label for="is_head_of_family" class="form-check-label">Set as Head of Family</label>
            </div>
            <small class="form-text text-muted">Note: If checked, this will replace the current head of family (if any).</small>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success" <?php echo ($residents && $residents->num_rows == 0) ? 'disabled' : ''; ?>>
                <i class="fas fa-user-plus"></i> Add to Household
            </button>
            <a href="../households/view.php?id=<?php echo $household_id; ?>" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
