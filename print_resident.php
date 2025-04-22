<?php
require_once 'php/config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header("Location: residents.php");
    exit();
}

$id = sanitize($_GET['id']);

// Get resident information
$resident_query = "SELECT r.*, h.family_name, h.household_id as hh_id 
                FROM residents r 
                LEFT JOIN households h ON r.household_id = h.id 
                WHERE r.id = $id";
$resident_result = $conn->query($resident_query);

if (!$resident_result || $resident_result->num_rows == 0) {
    header("Location: residents.php?error=1");
    exit();
}

$resident = $resident_result->fetch_assoc();

// Calculate age
$birthdate = new DateTime($resident['birth_date']);
$today = new DateTime();
$age = $birthdate->diff($today)->y;

// Log the print action
logActivity($_SESSION['user_id'], "Printed resident profile: {$resident['first_name']} {$resident['last_name']} (ID: $id)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Resident Profile - <?php echo $resident['first_name'] . ' ' . $resident['last_name']; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cs-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
            }
            .no-print {
                display: none !important;
            }
            .cs-form {
                border: 1px solid #000;
                margin: 0;
                width: 100%;
                box-sizing: border-box;
            }
            .cs-form-header {
                text-align: center;
                padding: 10px 0;
                border-bottom: 2px solid #000;
                background-color: #f0f0f0;
            }
            .cs-form-section-header {
                background-color: #e0e0e0;
                padding: 5px 10px;
                font-weight: bold;
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                text-transform: uppercase;
                font-size: 12px;
            }
            .print-container {
                padding: 0;
                margin: 0;
            }
            .print-header {
                text-align: center;
                margin-bottom: 20px;
            }
            .print-header img {
                width: 80px;
                height: 80px;
            }
            .print-footer {
                margin-top: 30px;
                text-align: center;
                font-size: 12px;
            }
            .signature-section {
                display: flex;
                justify-content: space-between;
                margin-top: 50px;
                padding: 0 50px;
            }
            .signature-block {
                text-align: center;
                width: 200px;
            }
            .signature-line {
                border-top: 1px solid #000;
                margin-bottom: 5px;
            }
        }
        
        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }
        
        .print-container {
            max-width: 8.5in;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-buttons no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="php/residents/view.php?id=<?php echo $id; ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>
        
        <div class="cs-form">
            <div class="cs-form-header">
                <h2>Republic of the Philippines</h2>
                <h3>Barangay Resident Information Form</h3>
                <p>CS Form No. 1 | Series of <?php echo date('Y'); ?></p>
            </div>
            
            <!-- Profile Image and Basic Info Section -->
            <div class="cs-form-section">
                <div class="cs-form-section-header">I. Personal Information</div>
                <div class="cs-form-section-content">
                    <div class="cs-form-row">
                        <div class="cs-form-field" style="flex: 0 0 120px;">
                            <label>ID Photo</label>
                            <div class="profile-image-container">
                                <?php if (!empty($resident['profile_image'])): ?>
                                    <img src="<?php echo $resident['profile_image']; ?>" class="profile-image" alt="Profile Image">
                                <?php else: ?>
                                    <div class="profile-image-placeholder">
                                        <i class="fas fa-user"></i>
                                        <span>No Image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="cs-form-field" style="flex: 1;">
                            <label>Resident ID</label>
                            <div class="data-value"><?php echo $resident['resident_id']; ?></div>
                            
                            <label>ID Card No.</label>
                            <div class="data-value"><?php echo $resident['id_card_no'] ?: 'Not provided'; ?></div>
                            
                            <label>Household</label>
                            <div class="data-value">
                                <?php if ($resident['household_id']): ?>
                                    <?php echo $resident['family_name'] . ' (' . $resident['hh_id'] . ')'; ?>
                                    <?php if ($resident['is_head_of_family']): ?>
                                        <span class="badge badge-primary">Head of Family</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-warning">No Household</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field third-width">
                            <label>Last Name</label>
                            <div class="data-value"><?php echo $resident['last_name']; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>First Name</label>
                            <div class="data-value"><?php echo $resident['first_name']; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Middle Name</label>
                            <div class="data-value"><?php echo $resident['middle_name'] ?: 'N/A'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field third-width">
                            <label>Birth Date</label>
                            <div class="data-value"><?php echo date('F d, Y', strtotime($resident['birth_date'])); ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Age</label>
                            <div class="data-value"><?php echo $age; ?> years old</div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Gender</label>
                            <div class="data-value"><?php echo $resident['gender']; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>Civil Status</label>
                            <div class="data-value"><?php echo $resident['civil_status']; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>Nationality</label>
                            <div class="data-value"><?php echo $resident['nationality']; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field full-width">
                            <label>Complete Address</label>
                            <div class="data-value"><?php echo $resident['address']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information Section -->
            <div class="cs-form-section">
                <div class="cs-form-section-header">II. Contact Information</div>
                <div class="cs-form-section-content">
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>Contact Number</label>
                            <div class="data-value"><?php echo $resident['contact_number'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>Email Address</label>
                            <div class="data-value"><?php echo $resident['email'] ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field third-width">
                            <label>Emergency Contact Name</label>
                            <div class="data-value"><?php echo $resident['emergency_contact_name'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Emergency Contact Number</label>
                            <div class="data-value"><?php echo $resident['emergency_contact_number'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Relationship</label>
                            <div class="data-value"><?php echo $resident['emergency_contact_relationship'] ?: 'Not provided'; ?></div>
                        </div>
                    </div  ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information Section -->
            <div class="cs-form-section">
                <div class="cs-form-section-header">III. Additional Information</div>
                <div class="cs-form-section-content">
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>Occupation</label>
                            <div class="data-value"><?php echo $resident['occupation'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>Educational Attainment</label>
                            <div class="data-value"><?php echo $resident['educational_attainment'] ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field third-width">
                            <label>Blood Type</label>
                            <div class="data-value"><?php echo $resident['blood_type'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Height (cm)</label>
                            <div class="data-value"><?php echo $resident['height'] ? $resident['height'] . ' cm' : 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field third-width">
                            <label>Weight (kg)</label>
                            <div class="data-value"><?php echo $resident['weight'] ? $resident['weight'] . ' kg' : 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>Sector</label>
                            <div class="data-value"><?php echo $resident['sector']; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>Religion</label>
                            <div class="data-value"><?php echo $resident['religion'] ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field" style="flex: 0 0 200px;">
                            <label>Registered Voter</label>
                            <div class="data-value">
                                <div class="cs-form-checkbox">
                                    <input type="checkbox" <?php echo $resident['is_voter'] ? 'checked' : ''; ?> disabled>
                                    <span><?php echo $resident['is_voter'] ? 'Yes' : 'No'; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="cs-form-field" style="flex: 0 0 200px;">
                            <label>Head of Family</label>
                            <div class="data-value">
                                <div class="cs-form-checkbox">
                                    <input type="checkbox" <?php echo $resident['is_head_of_family'] ? 'checked' : ''; ?> disabled>
                                    <span><?php echo $resident['is_head_of_family'] ? 'Yes' : 'No'; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="cs-form-field" style="flex: 1;">
                            <!-- Spacer -->
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Government IDs Section -->
            <div class="cs-form-section">
                <div class="cs-form-section-header">IV. Government IDs</div>
                <div class="cs-form-section-content">
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>PhilHealth Number</label>
                            <div class="data-value"><?php echo $resident['philhealth_no'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>SSS Number</label>
                            <div class="data-value"><?php echo $resident['sss_no'] ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="cs-form-row">
                        <div class="cs-form-field half-width">
                            <label>TIN Number</label>
                            <div class="data-value"><?php echo $resident['tin_no'] ?: 'Not provided'; ?></div>
                        </div>
                        <div class="cs-form-field half-width">
                            <label>Pag-IBIG Number</label>
                            <div class="data-value"><?php echo $resident['pagibig_no'] ?: 'Not provided'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Remarks Section -->
            <?php if (!empty($resident['remarks'])): ?>
            <div class="cs-form-section">
                <div class="cs-form-section-header">V. Remarks</div>
                <div class="cs-form-section-content">
                    <div class="cs-form-row">
                        <div class="cs-form-field full-width">
                            <div class="data-value" style="padding: 10px;"><?php echo nl2br($resident['remarks']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <strong>Resident's Signature</strong>
                </div>
                
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <strong>Barangay Official</strong><br>
                    <small>Position</small>
                </div>
            </div>
            
            <div class="print-footer">
                <p>Date Printed: <?php echo date('F d, Y h:i A'); ?></p>
                <p>This document is computer-generated and does not require a signature.</p>
            </div>
        </div>
    </div>
</body>
</html>
