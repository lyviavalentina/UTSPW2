<?php
// courses.php - Page to display all courses

// Start secure session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Check authentication
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Fetch all courses
$courses = [];
$sql = "SELECT c.course_id, c.course_name, c.description, c.category, c.education_level, c.class_level, 
               u.full_name AS instructor_name, 
               (SELECT COUNT(*) FROM registrations WHERE course_id = c.course_id) AS enrollment_count 
        FROM courses c 
        JOIN users u ON c.instructor_id = u.user_id 
        ORDER BY c.creation_date DESC";

if ($result = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

mysqli_close($conn);

// Helper function to get education level display
function get_education_level_display($level, $class) {
    $levels = [
        'SD' => 'Grade',
        'SMP' => 'Class',
        'SMA' => 'Class'
    ];
    return htmlspecialchars($levels[$level] . ' ' . $class);
}

// Helper function to get course icon
function get_course_icon($category) {
    $icons = [
        'math' => 'calculate',
        'science' => 'science',
        'language' => 'menu_book'
    ];
    return $icons[strtolower($category)] ?? 'school';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - All Courses</title>
    <link rel="stylesheet" href="css/styles3.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div id="app" class="app-frame">
            <div id="main-content">
                <!-- All Courses Screen -->
                <div id="all-courses" class="screen">
                    <div class="header">
                        <a href="dashboard.php" class="back-button">
                            <span class="material-icons">arrow_back</span>
                        </a>
                        <div class="header-title">All Courses</div>
                    </div>
                    
                    <div class="content">
                        <!-- Filter Chips -->
                        <div class="filter-chips">
                            <div class="chip active">All</div>
                            <div class="chip">Elementary (SD)</div>
                            <div class="chip">Junior High (SMP)</div>
                            <div class="chip">High School (SMA)</div>
                        </div>

                        <!-- Course Grid -->
                        <div class="course-grid">
                            <?php foreach ($courses as $course): ?>
                            <div class="course-card" onclick="window.location.href='course-detail.php?id=<?= $course['course_id'] ?>'">
                                <div class="course-img" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);">
                                    <span class="material-icons"><?= get_course_icon($course['category']) ?></span>
                                </div>
                                <div class="course-content">
                                    <div class="course-tag"><?= get_education_level_display($course['education_level'], $course['class_level']) ?></div>
                                    <div class="course-title"><?= htmlspecialchars($course['course_name']) ?></div>
                                    <div class="course-instructor">By <?= htmlspecialchars($course['instructor_name']) ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
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