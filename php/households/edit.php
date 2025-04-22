<?php
require_once '../config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: ../../households.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get household information
$household_query = "SELECT * FROM households WHERE id = $id";
$household_result = $conn->query($household_query);

if (!$household_result || $household_result->num_rows == 0) {
    header("Location: ../../households.php?error=1");
    exit();
}

$household = $household_result->fetch_assoc();

// Get current head of family
$current_head_query = "SELECT * FROM residents WHERE id = {$household['head_of_family_id']}";
$current_head_result = $conn->query($current_head_query);
$current_head = $current_head_result && $current_head_result->num_rows > 0 ? $current_head_result->fetch_assoc() : null;

// Get all residents for dropdown (excluding those already in other households except current head)
$residents_query = "SELECT * FROM residents WHERE household_id IS NULL OR id = {$household['head_of_family_id']} ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $family_name = sanitize($_POST['family_name']);
    $address = sanitize($_POST['address']);
    $monthly_income = sanitize($_POST['monthly_income']);
    $remarks = sanitize($_POST['remarks']);
    $head_of_family_id = !empty($_POST['head_of_family_id']) ? sanitize($_POST['head_of_family_id']) : NULL;
    
    // Update household data
    $sql = "UPDATE households SET 
            family_name = '$family_name', 
            address = '$address', 
            head_of_family_id = " . ($head_of_family_id ? "$head_of_family_id" : "NULL") . ", 
            monthly_income = '$monthly_income', 
            remarks = '$remarks'
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // If head of family changed, update residents table
        if ($head_of_family_id != $household['head_of_family_id']) {
            // Reset old head of family if exists
            if ($household['head_of_family_id']) {
                $reset_old_head = "UPDATE residents SET is_head_of_family = 0 WHERE id = {$household['head_of_family_id']}";
                $conn->query($reset_old_head);
            }
            
            // Set new head of family if selected
            if ($head_of_family_id) {
                $set_new_head = "UPDATE residents SET household_id = $id, is_head_of_family = 1 WHERE id = $head_of_family_id";
                $conn->query($set_new_head);
            }
        }
        
        // Log activity
        logActivity($_SESSION['user_id'], "Updated household: $family_name (ID: {$household['household_id']})");
        
        // Redirect to households page with success message
        header("Location: ../../households.php?success=2");
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Include the header
$pageTitle = "Edit Household";
include '../includes/header.php';
?>

<!-- Edit Household Form -->
<div class="form-container">
    <div class="form-title">Edit Household Information</div>
    
    <?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form action="" method="POST">
        <!-- Household Information -->
        <div class="form-row">
            <div class="form-col">
                <div class="form-group">
                    <label for="household_id">Household ID</label>
                    <input type="text" id="household_id" class="form-control" value="<?php echo $household['household_id']; ?>" readonly>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="family_name">Family Name *</label>
                    <input type="text" id="family_name" name="family_name" class="form-control" value="<?php echo $household['family_name']; ?>" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="monthly_income">Monthly Income</label>
                    <input type="number" id="monthly_income" name="monthly_income" class="form-control" step="0.01" min="0" value="<?php echo $household['monthly_income']; ?>">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="address">Address *</label>
            <textarea id="address" name="address" class="form-control" required><?php echo $household['address']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="head_of_family_id">Head of Family</label>
            <select id="head_of_family_id" name="head_of_family_id" class="form-control">
                <option value="">Select Head of Family</option>
                <?php if($residents && $residents->num_rows > 0): ?>
                    <?php while($resident = $residents->fetch_assoc()): ?>
                    <option value="<?php echo $resident['id']; ?>" <?php echo ($resident['id'] == $household['head_of_family_id']) ? 'selected' : ''; ?>>
                        <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                    </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <!-- Remarks -->
        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control"><?php echo $household['remarks']; ?></textarea>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Household
            </button>
            <a href="../../households.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
