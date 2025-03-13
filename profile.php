<?php
// profile.php - User profile page

// Start session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'config.php';

$user_id = $_SESSION["user_id"];
$success_message = $error_message = "";

// Fetch user data
$user_sql = "SELECT user_id, full_name, email, registration_date FROM users WHERE user_id = ?";
$user = null;

if($stmt = mysqli_prepare($conn, $user_sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1){
            $user = mysqli_fetch_assoc($result);
        } else {
            // User not found - should not happen
            header("location: logout.php");
            exit;
        }
    } else {
        $error_message = "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

// Fetch user's course statistics
$stats_sql = "SELECT COUNT(*) as total_courses, 
             (SELECT COUNT(*) FROM registrations r JOIN course_materials cm ON r.course_id = cm.course_id 
              WHERE r.user_id = ? GROUP BY r.user_id) as total_materials
             FROM registrations WHERE user_id = ?";
$stats = ['total_courses' => 0, 'total_materials' => 0];

if($stmt = mysqli_prepare($conn, $stats_sql)){
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);
            $stats['total_courses'] = $row['total_courses'];
            $stats['total_materials'] = $row['total_materials'] ? $row['total_materials'] : 0;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Handle form submission for profile update
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])){
    $full_name = sanitize_input($_POST["full_name"]);
    $email = sanitize_input($_POST["email"]);
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate input
    $valid = true;
    
    if(empty($full_name)){
        $error_message = "Full name cannot be empty.";
        $valid = false;
    }
    
    if(empty($email)){
        $error_message = "Email cannot be empty.";
        $valid = false;
    }
    
    // Check if email is already taken by another user
    if($valid && $email != $user['email']){
        $check_email_sql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
        
        if($stmt = mysqli_prepare($conn, $check_email_sql)){
            mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $error_message = "This email is already taken.";
                    $valid = false;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Verify current password if changing password
    if($valid && (!empty($new_password) || !empty($confirm_password))){
        // Get the current hashed password
        $pass_check_sql = "SELECT password FROM users WHERE user_id = ?";
        
        if($stmt = mysqli_prepare($conn, $pass_check_sql)){
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_assoc($result);
                    $hashed_password = $row["password"];
                    
                    if(!password_verify($current_password, $hashed_password)){
                        $error_message = "Current password is incorrect.";
                        $valid = false;
                    }
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        // Validate new password
        if($valid && strlen($new_password) < 6){
            $error_message = "New password must have at least 6 characters.";
            $valid = false;
        }
        
        // Confirm new password
        if($valid && $new_password != $confirm_password){
            $error_message = "New password and confirmation do not match.";
            $valid = false;
        }
    }
    
    // Update user profile
    if($valid){
        // Prepare update statement
        if(!empty($new_password)){
            // Update with new password
            $update_sql = "UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?";
            
            if($stmt = mysqli_prepare($conn, $update_sql)){
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $hashed_password, $user_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success_message = "Profile updated successfully.";
                    
                    // Update session variables
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["email"] = $email;
                    
                    // Refresh user data
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                } else {
                    $error_message = "Oops! Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            // Update without changing password
            $update_sql = "UPDATE users SET full_name = ?, email = ? WHERE user_id = ?";
            
            if($stmt = mysqli_prepare($conn, $update_sql)){
                mysqli_stmt_bind_param($stmt, "ssi", $full_name, $email, $user_id);
                
                if(mysqli_stmt_execute($stmt)){
                    $success_message = "Profile updated successfully.";
                    
                    // Update session variables
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["email"] = $email;
                    
                    // Refresh user data
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                } else {
                    $error_message = "Oops! Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Format registration date
$registration_date = new DateTime($user['registration_date']);
$formatted_date = $registration_date->format('F j, Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Profile</title>
    <link rel="stylesheet" href="css/styles6.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div id="app" class="app-frame">
            <div id="main-content">
                <!-- Profile Screen -->
                <div id="profile" class="screen">
                    <div class="header">
                        <div class="header-title">Profile</div>
                        <div class="header-actions">
                            <a href="login.php"><span class="material-icons">logout</span></a>
                        </div>
                    </div>
                    <div class="content">
                        <div class="profile-view">
                            <?php if(!empty($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if(!empty($error_message)): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <div class="profile-card view-profile-section">
                                <div class="profile-avatar">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <div class="profile-info">
                                    <div class="profile-name"><?php echo $user['full_name']; ?></div>
                                    <div class="profile-email"><?php echo $user['email']; ?></div>
                                    <div class="profile-date">Member since <?php echo $formatted_date; ?></div>
                                </div>
                                
                                <div class="stats-container">
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $stats['total_courses']; ?></div>
                                        <div class="stat-label">Courses</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $stats['total_materials']; ?></div>
                                        <div class="stat-label">Materials</div>
                                    </div>
                                </div>
                                
                                <button id="showEditProfileBtn" class="edit-profile-btn">Edit Profile</button>
                            </div>
                            
                            <div class="profile-card edit-profile-section">
                                <h3>Edit Profile</h3>
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="form-group">
                                        <label for="full_name">Full Name</label>
                                        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                    
                                    <div class="password-section">
                                        <div class="section-title">Change Password (Optional)</div>
                                        
                                        <div class="form-group">
                                            <label for="current_password">Current Password</label>
                                            <input type="password" id="current_password" name="current_password" class="form-control">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" id="new_password" name="new_password" class="form-control">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                        <button type="button" id="cancelEditBtn" class="btn" style="background: #f8f9fa;">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bottom Navigation -->
                    <?php include 'bottom-nav.php'; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle between view and edit profile sections
        document.getElementById('showEditProfileBtn').addEventListener('click', function() {
            document.querySelector('.view-profile-section').style.display = 'none';
            document.querySelector('.edit-profile-section').classList.add('active');
        });
        
        document.getElementById('cancelEditBtn').addEventListener('click', function() {
            document.querySelector('.view-profile-section').style.display = 'block';
            document.querySelector('.edit-profile-section').classList.remove('active');
        });
    </script>
</body>
</html>