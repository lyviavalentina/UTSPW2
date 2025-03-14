<?php
// manage-courses.php - CRUD Course Management
session_start();

require_once 'config.php';

// Inisialisasi variabel
$error = $success = "";
$course = [
    'course_name' => '',
    'description' => '',
    'category' => 'Mathematics',
    'education_level' => 'SD',
    'class_level' => '1'
];

// Handle CRUD operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create/Update Course
    if (isset($_POST["save_course"])) {
        $course_id = $_POST["course_id"] ?? 0;
        $course_name = sanitize_input($_POST["course_name"]);
        $description = sanitize_input($_POST["description"]);
        $category = sanitize_input($_POST["category"]);
        $education_level = sanitize_input($_POST["education_level"]);
        $class_level = sanitize_input($_POST["class_level"]);

        // Validasi input
        if (empty($course_name)) {
            $error = "Course name is required";
        } else {
            try {
                if ($course_id > 0) {
                    // Update course
                    $sql = "UPDATE courses SET 
                            course_name = ?, 
                            description = ?, 
                            category = ?, 
                            education_level = ?, 
                            class_level = ?
                            WHERE course_id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssi", 
                        $course_name, $description, $category, 
                        $education_level, $class_level, $course_id);
                } else {
                    // Create new course
                    $sql = "INSERT INTO courses 
                            (course_name, description, category, 
                            education_level, class_level, instructor_id) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssi", 
                        $course_name, $description, $category, 
                        $education_level, $class_level, $_SESSION["user_id"]);
                }
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Course saved successfully";
                    if ($course_id == 0) {
                        $course_id = mysqli_insert_id($conn);
                    }
                } else {
                    $error = "Error saving course: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
} elseif (isset($_GET["action"])) {
    // Delete Course
    // Di bagian delete course
if ($_GET["action"] == "delete") {
    $course_id = (int)$_GET["id"];
    
    try {
        mysqli_begin_transaction($conn);
        
        // Hapus terlebih dahulu data terkait di registrations
        $delete_registrations = "DELETE FROM registrations WHERE course_id = ?";
        $stmt1 = mysqli_prepare($conn, $delete_registrations);
        mysqli_stmt_bind_param($stmt1, "i", $course_id);
        mysqli_stmt_execute($stmt1);
        
        // Kemudian hapus course
        $delete_course = "DELETE FROM courses WHERE course_id = ?";
        $stmt2 = mysqli_prepare($conn, $delete_course);
        mysqli_stmt_bind_param($stmt2, "i", $course_id);
        mysqli_stmt_execute($stmt2);
        
        mysqli_commit($conn);
        $success = "Course deleted successfully";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error deleting course: " . $e->getMessage();
    }
    
    mysqli_stmt_close($stmt1);
    mysqli_stmt_close($stmt2);
}
    
    // Edit Course - Load data
    if ($_GET["action"] == "edit") {
        $course_id = (int)$_GET["id"];
        $sql = "SELECT * FROM courses WHERE course_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $course_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $course = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}

// Get all courses
$courses = [];
$sql = "SELECT * FROM courses ORDER BY course_id DESC"; // Menggunakan course_id sebagai ganti created_at
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}


mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <link rel="stylesheet" href="css/styles7.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Manage Courses</h1>
            <a href="manage-courses.php" class="btn">Add New</a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Course Form -->
        <form method="POST" class="course-form">
            <input type="hidden" name="course_id" 
                   value="<?= $course['course_id'] ?? 0 ?>">
            
            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="course_name" required
                       value="<?= htmlspecialchars($course['course_name']) ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= 
                    htmlspecialchars($course['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Mathematics" <?= 
                            ($course['category'] == 'Mathematics') ? 'selected' : '' ?>>Mathematics</option>
                        <option value="Science" <?= 
                            ($course['category'] == 'Science') ? 'selected' : '' ?>>Science</option>
                        <option value="Language" <?= 
                            ($course['category'] == 'Language') ? 'selected' : '' ?>>Language</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Education Level</label>
                    <select name="education_level" required>
                        <option value="SD" <?= 
                            ($course['education_level'] == 'SD') ? 'selected' : '' ?>>Elementary</option>
                        <option value="SMP" <?= 
                            ($course['education_level'] == 'SMP') ? 'selected' : '' ?>>Junior High</option>
                        <option value="SMA" <?= 
                            ($course['education_level'] == 'SMA') ? 'selected' : '' ?>>Senior High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Class Level</label>
                    <input type="number" name="class_level" min="1" max="12" required
                           value="<?= htmlspecialchars($course['class_level']) ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_course" class="btn primary">
                    <?= isset($course['course_id']) ? 'Update' : 'Create' ?> Course
                </button>
            </div>
        </form>

        <!-- Courses List -->
        <div class="courses-list">
            <?php foreach ($courses as $c): ?>
                <div class="course-item">
                    <div class="course-info">
                        <h3><?= htmlspecialchars($c['course_name']) ?></h3>
                        <p><?= htmlspecialchars($c['description']) ?></p>
                        <div class="course-meta">
                            <span><?= $c['category'] ?></span>
                            <span><?= $c['education_level'] ?> Class <?= $c['class_level'] ?></span>
                        </div>
                    </div>
                    <div class="course-actions">
                        <a href="?action=edit&id=<?= $c['course_id'] ?>" 
                           class="btn icon" title="Edit">
                            <span class="material-icons">edit</span>
                        </a>
                        <a href="?action=delete&id=<?= $c['course_id'] ?>" 
                           class="btn icon danger" title="Delete"
                           onclick="return confirm('Delete this course?')">
                            <span class="material-icons">delete</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include 'bottom-nav.php'; ?>
</body>
</html>

<style>
/* Tambahkan ke file CSS */
.course-form {
    background: var(--white);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.courses-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.course-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--white);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: var(--shadow);
}

.course-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--text);
}

.course-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text);
    opacity: 0.8;
}

.course-actions {
    display: flex;
    gap: 0.5rem;
}

.btn.icon {
    padding: 0.5rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
}

.btn.icon.danger {
    color: #dc3545;
    background: #ffe3e3;
}

.btn.icon:hover {
    background: var(--background);
}

@media (max-width: 768px) {
    .course-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .course-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
body {
    padding-bottom: 60px; /* Sesuaikan dengan tinggi navbar */
}

.content {
    padding-bottom: 60px; /* Hindari konten tertutup oleh navbar */
}
/* Bottom Navigation Container */
.bottom-nav {
    position: fixed; /* Tetap di bagian bawah layar */
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--white); /* Latar belakang putih */
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1); /* Shadow di bagian atas */
    display: flex;
    justify-content: space-around; /* Membagi ruang secara merata */
    padding: 0.75rem 0; /* Padding atas dan bawah */
    z-index: 1000; /* Pastikan di atas elemen lain */
}

/* Navigation Item */
.nav-item {
    display: flex;
    flex-direction: column; /* Ikon di atas, teks di bawah */
    align-items: center;
    text-decoration: none;
    color: var(--text); /* Warna teks default */
    padding: 0.5rem 1rem; /* Padding untuk area klik */
    border-radius: 12px; /* Sudut melengkung */
    transition: all 0.3s ease; /* Animasi transisi */
    background: var(--white); /* Latar belakang putih */
    box-shadow: var(--neumorphism-shadow); /* Efek Neumorphism */
}

/* Active Navigation Item */
.nav-item.active {
    color: var(--primary); /* Warna teks saat aktif */
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%); /* Gradien saat aktif */
    box-shadow: 0 4px 6px rgba(67, 97, 238, 0.2); /* Shadow saat aktif */
}

/* Ikon dan Teks saat Aktif */
.nav-item.active .nav-icon,
.nav-item.active span {
    color: var(--white); /* Warna putih saat aktif */
}

/* Hover Effect */
.nav-item:hover {
    transform: translateY(-5px); /* Naik sedikit saat dihover */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Shadow lebih besar saat dihover */
}

/* Ikon Material */
.nav-icon {
    font-size: 1.5rem; /* Ukuran ikon */
    margin-bottom: 0.25rem; /* Jarak antara ikon dan teks */
    color: var(--text); /* Warna ikon default */
    transition: color 0.3s ease; /* Animasi perubahan warna */
}

/* Teks Navigasi */
.nav-item span {
    font-size: 0.875rem; /* Ukuran teks */
    font-weight: 500; /* Ketebalan teks */
    color: var(--text); /* Warna teks default */
    transition: color 0.3s ease; /* Animasi perubahan warna */
}

/* Responsive Design */
@media (max-width: 768px) {
    .bottom-nav {
        padding: 0.5rem 0; /* Padding lebih kecil untuk mobile */
    }

    .nav-item {
        padding: 0.5rem; /* Padding lebih kecil untuk mobile */
    }

    .nav-icon {
        font-size: 1.25rem; /* Ukuran ikon lebih kecil untuk mobile */
    }

    .nav-item span {
        font-size: 0.75rem; /* Ukuran teks lebih kecil untuk mobile */
    }
}

</style>