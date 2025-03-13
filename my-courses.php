<?php
// my-courses.php - My Courses page with course registration form

// Start session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'config.php';

// Get user information
$user_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];

// Initialize variables for form
$selected_courses = [];
$education_level = '';
$error_message = '';
$success_message = '';

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate education level selection
    if(empty($_POST["education_level"])){
        $error_message = "Please select an education level.";
    } else {
        $education_level = sanitize_input($_POST["education_level"]);
    }
    
    // Validate course selection
    if(!isset($_POST["courses"]) || empty($_POST["courses"])){
        $error_message = "Please select at least one course.";
    } else {
        $selected_courses = $_POST["courses"];
    }
    
    // If no errors, process the registration
    if(empty($error_message)){
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            $success_count = 0;
            $already_registered = 0;
            
            // Prepare insert statement
            $insert_sql = "INSERT INTO registrations (user_id, course_id) VALUES (?, ?)";
            
            if($stmt = mysqli_prepare($conn, $insert_sql)){
                // Bind parameters
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
                
                // Process each selected course
                foreach($selected_courses as $course_id){
                    // Check if already registered
                    $check_sql = "SELECT * FROM registrations WHERE user_id = ? AND course_id = ?";
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $course_id);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);
                    
                    if(mysqli_num_rows($check_result) > 0){
                        $already_registered++;
                        continue;
                    }
                    
                    // Register for the course
                    if(mysqli_stmt_execute($stmt)){
                        $success_count++;
                    }
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Set appropriate message
            if($success_count > 0){
                $success_message = "Successfully registered for " . $success_count . " course(s).";
                if($already_registered > 0){
                    $success_message .= " " . $already_registered . " course(s) were already in your list.";
                }
            } else if($already_registered > 0){
                $success_message = "You were already registered for all selected courses.";
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error_message = "Error registering for courses: " . $e->getMessage();
        }
    }
}

// Fetch available courses based on selected education level
$available_courses = [];
if(!empty($education_level)){
    $courses_sql = "SELECT c.course_id, c.course_name, c.category, c.class_level, 
                    u.full_name AS instructor_name
                    FROM courses c
                    JOIN users u ON c.instructor_id = u.user_id
                    WHERE c.education_level = ?
                    AND c.course_id NOT IN (
                        SELECT course_id FROM registrations WHERE user_id = ?
                    )
                    ORDER BY c.course_name";
    
    if($stmt = mysqli_prepare($conn, $courses_sql)){
        mysqli_stmt_bind_param($stmt, "si", $education_level, $user_id);
        
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)){
                $available_courses[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Fetch user's enrolled courses with progress information
$enrolled_courses_sql = "SELECT c.course_id, c.course_name, c.description, c.category, 
                        c.education_level, c.class_level, u.full_name AS instructor_name, 
                        r.progress, r.registration_date,
                        (SELECT COUNT(*) FROM course_materials WHERE course_id = c.course_id) AS total_materials
                        FROM registrations r
                        JOIN courses c ON r.course_id = c.course_id
                        JOIN users u ON c.instructor_id = u.user_id
                        WHERE r.user_id = ?
                        ORDER BY r.registration_date DESC";

$enrolled_courses = [];

if($stmt = mysqli_prepare($conn, $enrolled_courses_sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)){
            $enrolled_courses[] = $row;
        }
    } else {
        handle_error("Error fetching enrolled courses: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
}

// Function to get education level display name
function get_education_level_display($level, $class) {
    return $level . " Kelas " . $class;
}

// Function to get course icon class based on category
function get_course_icon($category) {
    $icons = [
        'Science' => 'science',
        'Mathematics' => 'calculate',
        'Language' => 'language',
        'Social Studies' => 'history',
        'Arts' => 'palette'
    ];
    
    return isset($icons[$category]) ? $icons[$category] : 'school';
}

// Function to get course background style based on category
function get_course_bg_style($category) {
    $styles = [
        'Science' => 'background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);',
        'Mathematics' => 'background: linear-gradient(135deg, #f72585 0%, #b5179e 100%);',
        'Language' => 'background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);',
        'Social Studies' => 'background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);',
        'Arts' => 'background: linear-gradient(135deg, #f72585 0%, #7209b7 100%);'
    ];
    
    return isset($styles[$category]) ? $styles[$category] : '';
}

// Function to format date
function format_date($date) {
    return date("d M Y", strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - My Courses</title>
    <link rel="stylesheet" href="css/styles5.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div id="app" class="app-frame">
            <div id="main-content">
                <!-- My Courses Screen -->
                <div id="my-courses" class="screen">
                    <div class="header">
                        <div class="header-title">My Courses</div>
                        <div class="header-actions">
                            <a href="notifications.php"><span class="material-icons">notifications</span></a>
                        </div>
                    </div>
                    
                    <div class="content">
                        <div class="user-header">
                            <div class="user-avatar">
                                <span class="material-icons">person</span>
                            </div>
                            <div class="user-info">
                                <h2><?php echo htmlspecialchars($full_name); ?></h2>
                                <p><?php echo htmlspecialchars($_SESSION["email"]); ?></p>
                            </div>
                        </div>
                        
                        <?php if(!empty($error_message)): ?>
                            <div class="error-message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if(!empty($success_message)): ?>
                            <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        
                        <div class="add-course-btn" onclick="showModal()">
                            <span class="material-icons">add_circle</span>
                            Add New Courses
                        </div>
                        
                        <div class="tabs">
                            <div class="tab active" onclick="switchTab(this, 'all-courses')">All Courses</div>
                            <div class="tab" onclick="switchTab(this, 'in-progress')">In Progress</div>
                            <div class="tab" onclick="switchTab(this, 'completed')">Completed</div>
                        </div>
                        
                        <div id="all-courses" class="tab-content">
                            <?php if(count($enrolled_courses) > 0): ?>
                                <?php foreach($enrolled_courses as $course): ?>
                                    <div class="course-card" onclick="window.location.href='course-detail.php?id=<?php echo $course['course_id']; ?>'">
                                        <div class="course-img" style="<?php echo get_course_bg_style($course['category']); ?>">
                                            <span class="material-icons"><?php echo get_course_icon($course['category']); ?></span>
                                        </div>
                                        <div class="course-content">
                                            <div class="course-tag"><?php echo get_education_level_display($course['education_level'], $course['class_level']); ?></div>
                                            <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                            <div class="course-instructor">By <?php echo htmlspecialchars($course['instructor_name']); ?></div>
                                            
                                            <?php 
                                            // Calculate progress percentage
                                            $progress_percentage = 0;
                                            if($course['total_materials'] > 0) {
                                                $progress_percentage = round(($course['progress'] / $course['total_materials']) * 100);
                                            }
                                            ?>
                                            
                                            <div class="progress-bar">
                                                <div class="progress-bar-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
                                            </div>
                                            
                                            <div class="course-status">
                                                <div class="course-progress">
                                                    <?php echo $progress_percentage; ?>% Complete
                                                </div>
                                                <div class="course-date">
                                                    Enrolled: <?php echo format_date($course['registration_date']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-courses">
                                    <span class="material-icons">school</span>
                                    <h3>No courses yet</h3>
                                    <p>You haven't enrolled in any courses yet</p>
                                    <button class="btn btn-primary" onclick="showModal()">Add Courses Now</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="in-progress" class="tab-content" style="display:none;">
                            <!-- Content for in-progress courses would go here -->
                            <!-- This would need filtering logic in a real implementation -->
                        </div>
                        
                        <div id="completed" class="tab-content" style="display:none;">
                            <!-- Content for completed courses would go here -->
                            <!-- This would need filtering logic in a real implementation -->
                        </div>
                    </div>
                    
                    <!-- Bottom Navigation -->
                    <?php include 'bottom-nav.php'; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Courses Modal -->
    <div id="addCoursesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Courses</h3>
                <span class="close-modal" onclick="hideModal()">&times;</span>
            </div>
            
            <form id="addCoursesForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="education_level">Select Education Level</label>
                    <select id="education_level" name="education_level" class="form-control" onchange="this.form.submit()">
                        <option value="">Choose an education level</option>
                        <option value="SD" <?php echo ($education_level == 'SD') ? 'selected' : ''; ?>>Elementary School (SD)</option>
                        <option value="SMP" <?php echo ($education_level == 'SMP') ? 'selected' : ''; ?>>Junior High School (SMP)</option>
                        <option value="SMA" <?php echo ($education_level == 'SMA') ? 'selected' : ''; ?>>Senior High School (SMA)</option>
                    </select>
                </div>
                
                <?php if(!empty($education_level) && count($available_courses) > 0): ?>
                    <p>Select courses to add (minimum one):</p>
                    
                    <div class="course-select-container">
                        <?php foreach($available_courses as $course): ?>
                            <div class="course-checkbox">
                                <input type="checkbox" id="course_<?php echo $course['course_id']; ?>" 
                                       name="courses[]" value="<?php echo $course['course_id']; ?>"
                                       <?php echo (in_array($course['course_id'], $selected_courses)) ? 'checked' : ''; ?>>
                                <div class="course-info">
                                    <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                    <div class="course-details">Instructor: <?php echo htmlspecialchars($course['instructor_name']); ?></div>
                                    <div class="course-level-info">Class: <?php echo $course['class_level']; ?> • Category: <?php echo htmlspecialchars($course['category']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">
                        Add Selected Courses
                    </button>
                <?php elseif(!empty($education_level) && count($available_courses) == 0): ?>
                    <div class="empty-courses" style="padding: 20px 0;">
                        <p>No available courses for the selected education level</p>
                        <p>You're already enrolled in all available courses for this level</p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <script>
        // Show the modal
        function showModal() {
            document.getElementById('addCoursesModal').style.display = 'block';
        }
        
        // Hide the modal
        function hideModal() {
            document.getElementById('addCoursesModal').style.display = 'none';
        }
        
        // Switch tabs
        function switchTab(tabElement, tabId) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].style.display = 'none';
            }
            
            // Remove active class from all tabs
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // Show the selected tab content and mark tab as active
            document.getElementById(tabId).style.display = 'block';
            tabElement.classList.add('active');
        }
        
        // If there was a form submission, show the modal
        <?php if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($education_level)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showModal();
            });
        <?php endif; ?>
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addCoursesModal');
            if (event.target == modal) {
                hideModal();
            }
        }
    </script>
</body>
</html>