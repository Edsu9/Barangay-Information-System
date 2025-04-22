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

// Get all residents without a household
$residents_query = "SELECT * FROM residents WHERE household_id IS NULL ORDER BY last_name, first_name ASC";
$residents = $conn->query($residents_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resident_id']) && !empty($_POST['resident_id'])) {
        $resident_id = sanitize($_POST['resident_id']);
        $is_head_of_family = isset($_POST['is_head_of_family']) ? 1 : 0;
        
        // Update resident's household
        $sql = "UPDATE residents SET household_id = $household_id, is_head_of_family = $is_head_of_family WHERE id = $resident_id";
        
        if ($conn->query($sql) === TRUE) {
            // If resident is head of family, update household
            if ($is_head_of_family) {
                // First, reset any existing head of family in this household
                $reset_head = "UPDATE residents SET is_head_of_family = 0 
                              WHERE household_id = $household_id AND id != $resident_id";
                $conn->query($reset_head);
                
                // Then set this resident as head of family in the household table
                $update_household = "UPDATE households SET head_of_family_id = $resident_id 
                                   WHERE id = $household_id";
                $conn->query($update_household);
            }
            
            // Update household's number of members
            $update_household = "UPDATE households SET number_of_members = (SELECT COUNT(*) FROM residents WHERE household_id = $household_id) WHERE id = $household_id";
            $conn->query($update_household);
            
            // Log activity
            logActivity($_SESSION['user_id'], "Added resident ID: $resident_id to household: {$household['household_id']}");
            
            // Redirect to household view page
            header("Location: view.php?id=$household_id&success=1");
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

<style>
    .form-container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e0e0e0;
        background-color: #f9f9f9;
    }
    
    .form-header h2 {
        margin: 0;
        font-size: 1.4rem;
        color: #333;
    }
    
    .form-content {
        padding: 20px;
    }
    
    .info-section {
        background-color: #f9f9f9;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    
    .info-row {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-label {
        width: 150px;
        font-weight: 600;
        color: #333;
    }
    
    .info-value {
        flex: 1;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.2s;
    }
    
    .form-control:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    
    .form-text {
        font-size: 0.875rem;
        color: #666;
        margin-top: 5px;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        margin-top: 10px;
    }
    
    .checkbox-group input {
        margin-right: 8px;
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .btn {
        padding: 10px 16px;
        border-radius: 4px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background-color: #2563eb;
        color: white;
    }
    
    .btn-success {
        background-color: #10b981;
        color: white;
    }
    
    .btn-danger {
        background-color: #ef4444;
        color: white;
    }
    
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .alert-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border-left: 4px solid #ef4444;
    }
</style>

<div class="form-container">
    <div class="form-header">
        <h2>Add Member to Household</h2>
        <a href="view.php?id=<?php echo $household_id; ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Household
        </a>
    </div>
    
    <div class="form-content">
        <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Household ID:</div>
                <div class="info-value"><?php echo $household['household_id']; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Family Name:</div>
                <div class="info-value"><?php echo $household['family_name']; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?php echo $household['address']; ?></div>
            </div>
        </div>
        
        <form action="" method="POST">
            <div class="form-group">
                <label for="resident_id">Select Resident to Add:</label>
                <select id="resident_id" name="resident_id" class="form-control" required>
                    <option value="">-- Select Resident --</option>
                    <?php if($residents && $residents->num_rows > 0): ?>
                        <?php while($resident = $residents->fetch_assoc()): ?>
                        <option value="<?php echo $resident['id']; ?>">
                            <?php echo $resident['last_name'] . ', ' . $resident['first_name'] . ' ' . $resident['middle_name'] . ' (' . $resident['resident_id'] . ')'; ?>
                        </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="" disabled>No available residents found</option>
                    <?php endif; ?>
                </select>
                <div class="form-text">Only residents without an existing household are shown.</div>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="is_head_of_family" name="is_head_of_family">
                <label for="is_head_of_family">Set as Head of Family</label>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add to Household
                </button>
                <a href="view.php?id=<?php echo $household_id; ?>" class="btn btn-danger">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
