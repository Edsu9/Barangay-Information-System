<?php
require_once '../config.php';
requireLogin();

// Get all residents for dropdown
$residents_query = "SELECT * FROM residents WHERE household_id IS NULL ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and collect form data
    $family_name = sanitize($_POST['family_name']);
    $address = sanitize($_POST['address']);
    $monthly_income = sanitize($_POST['monthly_income']);
    $remarks = sanitize($_POST['remarks']);
    $head_of_family_id = !empty($_POST['head_of_family_id']) ? sanitize($_POST['head_of_family_id']) : NULL;
    
    // Generate unique household ID
    $household_id = generateUniqueID('HH');
    
    // Insert household data
    $sql = "INSERT INTO households (
                household_id, family_name, address, head_of_family_id, monthly_income, remarks
            ) VALUES (
                '$household_id', '$family_name', '$address', " . ($head_of_family_id ? "$head_of_family_id" : "NULL") . ", '$monthly_income', '$remarks'
            )";
    
    if ($conn->query($sql) === TRUE) {
        $household_id_db = $conn->insert_id;
        
        // Update head of family's household_id
        if ($head_of_family_id) {
            $update_resident = "UPDATE residents SET household_id = $household_id_db, is_head_of_family = 1 WHERE id = $head_of_family_id";
            $conn->query($update_resident);
        }
        
        // Log activity
        logActivity($_SESSION['user_id'], "Added new household: $family_name");
        
        // Redirect to households page with success message
        header("Location: ../../households.php?success=1");
        exit();
    } else {
        $error = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Include the header
$pageTitle = "Add Household";
include '../includes/header.php';
?>

<!-- Add Household Form -->
<div class="form-container">
    <div class="form-title">Household Information</div>
    
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
                    <label for="family_name">Family Name *</label>
                    <input type="text" id="family_name" name="family_name" class="form-control" required>
                </div>
            </div>
            <div class="form-col">
                <div class="form-group">
                    <label for="monthly_income">Monthly Income</label>
                    <input type="number" id="monthly_income" name="monthly_income" class="form-control" step="0.01" min="0">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="address">Address *</label>
            <textarea id="address" name="address" class="form-control" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="head_of_family_id">Head of Family</label>
            <select id="head_of_family_id" name="head_of_family_id" class="form-control">
                <option value="">Select Head of Family</option>
                <?php if($residents && $residents->num_rows > 0): ?>
                    <?php while($resident = $residents->fetch_assoc()): ?>
                    <option value="<?php echo $resident['id']; ?>">
                        <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name']; ?>
                    </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <small class="form-text">Only residents without an existing household are shown. You can add more members after creating the household.</small>
        </div>
        
        <!-- Remarks -->
        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" class="form-control"></textarea>
        </div>
        
        <!-- Submit Button -->
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save Household
            </button>
            <a href="../../households.php" class="btn btn-danger">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
