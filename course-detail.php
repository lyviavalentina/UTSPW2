<?php
// course-detail.php - Course detail page

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

// Validate course ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$course_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// Fetch course details with prepared statement
$course = [];
$course_sql = "SELECT c.course_id, c.course_name, c.description, c.category, 
               c.education_level, c.class_level, u.full_name AS instructor_name,
               (SELECT COUNT(*) FROM registrations WHERE course_id = c.course_id) AS enrollment_count
               FROM courses c
               JOIN users u ON c.instructor_id = u.user_id
               WHERE c.course_id = ?";

if ($stmt = mysqli_prepare($conn, $course_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $course = mysqli_fetch_assoc($result);
        if (!$course) {
            header("Location: dashboard.php");
            exit;
        }
    }
    mysqli_stmt_close($stmt);
}

// Check registration status
$registered = false;
$registration_sql = "SELECT 1 FROM registrations WHERE user_id = ? AND course_id = ?";
if ($stmt = mysqli_prepare($conn, $registration_sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
    mysqli_stmt_execute($stmt);
    $registered = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

// Fetch enrolled students
$enrolled_students = [];
$students_sql = "SELECT u.user_id, u.full_name, u.email, r.registration_date 
                FROM registrations r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.course_id = ?
                ORDER BY r.registration_date DESC";
if ($stmt = mysqli_prepare($conn, $students_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $enrolled_students[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Fetch ordered course materials
$materials = [];
$materials_sql = "SELECT material_id, title, order_number, duration_minutes 
                 FROM course_materials 
                 WHERE course_id = ? 
                 ORDER BY order_number";
if ($stmt = mysqli_prepare($conn, $materials_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $materials[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Handle enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll'])) {
    $insert_sql = "INSERT INTO registrations (user_id, course_id) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($conn, $insert_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $course_id);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: course-detail.php?id=" . $course_id);
            exit;
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);

// Helper functions for display
function get_education_level_display($level, $class) {
    $levels = [
        'SD' => 'Grade',
        'SMP' => 'Class',
        'SMA' => 'Class'
    ];
    return htmlspecialchars($levels[$level] . ' ' . $class);
}

function get_course_icon($category) {
    $icons = [
        'math' => 'calculate',
        'science' => 'science',
        'language' => 'menu_book'
    ];
    return $icons[strtolower($category)] ?? 'school';
}

function format_date($date_string) {
    $date = new DateTime($date_string);
    return $date->format('M d, Y');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - <?= htmlspecialchars($course['course_name']) ?></title>
    <link rel="stylesheet" href="css/styles2.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
        }
        
        .student-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .student-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: 500;
            margin-right: 12px;
        }
        
        .student-info {
            flex: 1;
        }
        
        .student-name {
            font-weight: 500;
            margin-bottom: 2px;
        }
        
        .student-email, .student-date {
            font-size: 13px;
            color: #777;
        }
        
        .enrollment-count-btn {
            cursor: pointer;
            text-decoration: underline;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="app" class="app-frame">
            <div id="main-content">
                <!-- Course Detail Screen -->
                <div id="course-detail" class="screen">
                    <div class="header">
                        <a href="dashboard.php" class="back-button">
                            <span class="material-icons">arrow_back</span>
                        </a>
                        <div class="header-title">Course Detail</div>
                        <a href="#" class="share-button">
                            <span class="material-icons">share</span>
                        </a>
                    </div>
                    
                    <div class="content">
                        <!-- Hero Section -->
                        <div class="course-hero">
                            <div class="course-image" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);">
                                <span class="material-icons"><?= get_course_icon($course['category']) ?></span>
                            </div>
                            <h1 class="course-title"><?= htmlspecialchars($course['course_name']) ?></h1>
                            <div class="course-meta">
                                <span class="meta-item">
                                    <span class="material-icons">school</span>
                                    <?= get_education_level_display($course['education_level'], $course['class_level']) ?>
                                </span>
                                <span class="meta-item">
                                    <span class="material-icons">people</span>
                                    <span class="enrollment-count-btn" id="showEnrollmentList">
                                        <?= htmlspecialchars($course['enrollment_count']) ?> Students
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Course Actions -->
                        <div class="course-actions">
                            <?php if ($registered): ?>
                                <div class="progress-container">
                                    <div class="progress-text">Progress: 50%</div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: 50%;"></div>
                                    </div>
                                </div>
                                <a href="learn.php?course=<?= $course_id ?>" class="btn btn-primary">
                                    <span class="material-icons">play_circle</span>
                                    Continue Learning
                                </a>
                            <?php else: ?>
                                <form method="post">
                                    <button type="submit" name="enroll" class="btn btn-primary">
                                        <span class="material-icons">add</span>
                                        Enroll Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <!-- Course Description -->
                            <div class="course-description">
                            <div class="section-title">Description</div>
                            <p><?= htmlspecialchars($course['description']) ?></p>
                        </div>
                        <!-- Instructor Section -->
                        <div class="instructor-section">
                            <div class="section-title">Instructor</div>
                            <div class="instructor-card">
                                <div class="instructor-avatar">
                                    <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                                </div>
                                <div class="instructor-info">
                                    <div class="instructor-name"><?= htmlspecialchars($course['instructor_name']) ?></div>
                                    <div class="instructor-bio">Experienced educator with 10+ years teaching</div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Content Section -->
                        <div class="course-content-section">
                            <div class="section-title">Course Content</div>
                            <div class="materials-list">
                                <?php foreach ($materials as $index => $material): ?>
                                <div class="material-item <?= ($registered && $index == 0) ? 'unlocked' : '' ?>">
                                    <div class="material-order"><?= $index + 1 ?></div>
                                    <div class="material-info">
                                        <div class="material-title"><?= htmlspecialchars($material['title']) ?></div>
                                        <div class="material-meta">
                                            <?php if ($material['duration_minutes']): ?>
                                            <span class="material-duration">
                                                <span class="material-icons">schedule</span>
                                                <?= htmlspecialchars($material['duration_minutes']) ?> mins
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($registered): ?>
                                        <a href="learn.php?course=<?= $course_id ?>&material=<?= $material['material_id'] ?>" 
                                           class="material-action <?= $index == 0 ? 'available' : 'locked' ?>">
                                            <span class="material-icons"><?= $index == 0 ? 'play_circle' : 'lock' ?></span>
                                        </a>
                                    <?php else: ?>
                                        <div class="material-action locked">
                                            <span class="material-icons">lock</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bottom Navigation -->
                    <?php include 'bottom-nav.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment List Modal -->
    <div id="enrollmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Enrolled Students (<?= count($enrolled_students) ?>)</div>
                <span class="close-modal">&times;</span>
            </div>
            <?php if (count($enrolled_students) > 0): ?>
                <ul class="student-list">
                    <?php foreach ($enrolled_students as $student): ?>
                        <li class="student-item">
                            <div class="student-avatar">
                                <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                            </div>
                            <div class="student-info">
                                <div class="student-name"><?= htmlspecialchars($student['full_name']) ?></div>
                                <div class="student-email"><?= htmlspecialchars($student['email']) ?></div>
                                <div class="student-date">Joined: <?= format_date($student['registration_date']) ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No students have enrolled in this course yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('enrollmentModal');
            const btn = document.getElementById('showEnrollmentList');
            const closeBtn = document.querySelector('.close-modal');
            
            btn.addEventListener('click', function() {
                modal.style.display = 'block';
            });
            
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>