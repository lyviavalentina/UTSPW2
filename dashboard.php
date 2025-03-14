<?php
// dashboard.php - Main dashboard page

// Start session
session_start();

// Check if the user is logged in, if not redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once 'config.php';

// Fetch popular courses
$popular_courses_sql = "SELECT c.course_id, c.course_name, c.description, c.category, c.education_level, c.class_level, 
                       u.full_name AS instructor_name, 
                       (SELECT COUNT(*) FROM registrations WHERE course_id = c.course_id) AS enrollment_count 
                FROM courses c 
                JOIN users u ON c.instructor_id = u.user_id 
                ORDER BY enrollment_count DESC 
                LIMIT 4";
$popular_courses_result = mysqli_query($conn, $popular_courses_sql);

// Fetch new courses
$new_courses_sql = "SELECT c.course_id, c.course_name, c.description, c.category, c.education_level, c.class_level, 
                    u.full_name AS instructor_name 
                FROM courses c 
                JOIN users u ON c.instructor_id = u.user_id 
                ORDER BY c.creation_date DESC 
                LIMIT 4";
$new_courses_result = mysqli_query($conn, $new_courses_sql);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Dashboard</title>
    <link rel="stylesheet" href="css/styles1.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<body>
    <div class="container">
        <div id="app" class="app-frame">
            <div id="main-content">
                <!-- Dashboard Screen -->
                <div id="dashboard" class="screen">
                    <div class="header">
                        <div class="header-title">EduLearn</div>
                        <div class="header-actions">
                            <a href="notifications.php"><span class="material-icons">notifications</span></a>
                        </div>
                    </div>
                    <div class="content">
                        <div class="dashboard">
                            <div class="search-bar">
                                <span class="material-icons search-icon">search</span>
                                <input type="text" class="search-input" placeholder="Search for courses...">
                            </div>
                            <div class="carousel-container mb-4">
    <div id="eduCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner rounded-3">
            <div class="carousel-item active" data-bs-interval="5000">
                <img src="Gambar 1.png" class="d-block w-100" alt="EduLearn 1">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Welcome to EduLearn</h3>
                    <p>Start your learning journey with us</p>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <img src="Gambar 1.jpg" class="d-block w-100" alt="EduLearn 2">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Expert Instructors</h3>
                    <p>Learn from industry professionals</p>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="5000">
                <img src="Gambar 3.png" class="d-block w-100" alt="EduLearn 3">
                <div class="carousel-caption d-none d-md-block">
                    <h3>Interactive Courses</h3>
                    <p>Engaging and practical learning materials</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#eduCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#eduCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
                        
                            
                            <div class="filter-chips">
                                <div class="chip active">All</div>
                                <div class="chip">Elementary (SD)</div>
                                <div class="chip">Junior High (SMP)</div>
                                <div class="chip">High School (SMA)</div>
                            </div>
                            
                            <div class="section-title">
                                <span>Popular Courses</span>
                                <a href="courses.php?filter=popular">See All</a>
                            </div>
                            
                            <div class="course-grid">
                                <?php
                                // Display popular courses
                                if(mysqli_num_rows($popular_courses_result) > 0){
                                    while($course = mysqli_fetch_assoc($popular_courses_result)){
                                ?>
                                <div class="course-card" onclick="window.location.href='course-detail.php?id=<?php echo $course['course_id']; ?>'">
                                    <div class="course-img" style="<?php echo get_course_bg_style($course['category']); ?>">
                                        <span class="material-icons"><?php echo get_course_icon($course['category']); ?></span>
                                    </div>
                                    <div class="course-content">
                                        <div class="course-tag"><?php echo get_education_level_display($course['education_level'], $course['class_level']); ?></div>
                                        <div class="course-title"><?php echo $course['course_name']; ?></div>
                                        <div class="course-instructor">By <?php echo $course['instructor_name']; ?></div>
                                    </div>
                                </div>
                                <?php
                                    }
                                } else {
                                    echo "<p>No popular courses found.</p>";
                                }
                                ?>
                            </div>
                            
                            <div class="section-title">
                                <span>New Courses</span>
                                <a href="courses.php?filter=new">See All</a>
                            </div>
                            
                            <div class="course-grid">
                                <?php
                                // Display new courses
                                if(mysqli_num_rows($new_courses_result) > 0){
                                    while($course = mysqli_fetch_assoc($new_courses_result)){
                                ?>
                                <div class="course-card" onclick="window.location.href='course-detail.php?id=<?php echo $course['course_id']; ?>'">
                                    <div class="course-img" style="<?php echo get_course_bg_style($course['category']); ?>">
                                        <span class="material-icons"><?php echo get_course_icon($course['category']); ?></span>
                                    </div>
                                    <div class="course-content">
                                        <div class="course-tag"><?php echo get_education_level_display($course['education_level'], $course['class_level']); ?></div>
                                        <div class="course-title"><?php echo $course['course_name']; ?></div>
                                        <div class="course-instructor">By <?php echo $course['instructor_name']; ?></div>
                                    </div>
                                </div>
                                <?php
                                    }
                                } else {
                                    echo "<p>No new courses found.</p>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bottom Navigation -->
                    <?php include 'bottom-nav.php'; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>