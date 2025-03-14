<?php
// search-participants.php - Search for course participants
// Standalone page with inline results display

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

// Initialize variables
$courses = [];
$students = [];
$selected_course_id = null;
$search_term = '';
$error_message = '';
$course_name = '';

// Fetch all available courses for dropdown
$courses_sql = "SELECT course_id, course_name FROM courses ORDER BY course_name ASC";
if ($stmt = mysqli_prepare($conn, $courses_sql)) {
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row;
        }
    } else {
        $error_message = "Terjadi kesalahan dalam mengambil data kursus.";
    }
    mysqli_stmt_close($stmt);
}

// Process form submission - stays on same page
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    // Get selected course
    if (isset($_POST['course_id']) && !empty($_POST['course_id']) && ctype_digit($_POST['course_id'])) {
        $selected_course_id = (int)$_POST['course_id'];
    } else {
        $error_message = "Silakan pilih kursus terlebih dahulu.";
    }
    
    // Get search term if provided
    if (isset($_POST['search_term'])) {
        $search_term = trim($_POST['search_term']);
    }
    
    // If course is selected, fetch participants
    if ($selected_course_id) {
        // Get course name for display
        foreach ($courses as $course) {
            if ($course['course_id'] == $selected_course_id) {
                $course_name = $course['course_name'];
                break;
            }
        }
        
        // Base SQL query - FIXED to remove the enrollment_date column
        $students_sql = "SELECT u.user_id, u.full_name, u.email, c.course_name, r.registration_date 
                        FROM users u
                        JOIN registrations r ON u.user_id = r.user_id
                        JOIN courses c ON r.course_id = c.course_id
                        WHERE r.course_id = ?";
        
        // Add search condition if search term is provided
        $params = [$selected_course_id];
        if (!empty($search_term)) {
            $students_sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $search_param = "%" . $search_term . "%";
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        // Add sorting
        $students_sql .= " ORDER BY u.full_name ASC";
        
        // Prepare and execute query
        if ($stmt = mysqli_prepare($conn, $students_sql)) {
            if (count($params) == 1) {
                mysqli_stmt_bind_param($stmt, "i", $params[0]);
            } else {
                mysqli_stmt_bind_param($stmt, "iss", $params[0], $params[1], $params[2]);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $students[] = $row;
                }
            } else {
                $error_message = "Terjadi kesalahan dalam mencari data peserta.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Close connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduLearn - Pencarian Peserta Kursus</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563EB;
            --secondary: #4F46E5;
            --accent: #F59E0B;
            --text: #333333;
            --text-light: #666666;
            --background: #F3F4F6;
            --card: #FFFFFF;
            --error: #EF4444;
            --success: #10B981;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            font-size: 16px;
            color: var(--text-light);
        }
        
        .search-section {
            background-color: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-secondary {
            background-color: #e5e7eb;
            color: var(--text);
        }
        
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
        
        .results-section {
            background-color: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .results-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text);
        }
        
        .results-count {
            font-size: 14px;
            color: var(--text-light);
            background-color: #e5e7eb;
            padding: 5px 10px;
            border-radius: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .participants-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .participants-table th,
        .participants-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .participants-table th {
            background-color: #f9fafb;
            font-weight: 500;
            color: var(--text);
        }
        
        .participants-table tr:last-child td {
            border-bottom: none;
        }
        
        .participants-table tr:hover {
            background-color: #f9fafb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-icon {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 15px;
        }
        
        .empty-text {
            font-size: 16px;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .error-message {
            background-color: #fee2e2;
            color: var(--error);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success-message {
            background-color: #d1fae5;
            color: var(--success);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .actions-bar {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .form-group {
                width: 100%;
            }
        }
        
        /* Print styles */
        @media print {
            .search-section, .page-subtitle, .actions-bar {
                display: none;
            }
            
            .container {
                padding: 0;
            }
            
            .page-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 1px solid #000;
            }
            
            .results-section {
                box-shadow: none;
                padding: 0;
            }
            
            .participants-table th, 
            .participants-table td {
                padding: 8px;
                border-bottom: 1px solid #000;
            }
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
body {
    padding-bottom: 60px; /* Sesuaikan dengan tinggi navbar */
}

.content {
    padding-bottom: 60px; /* Hindari konten tertutup oleh navbar */
}
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Pencarian Peserta Kursus</h1>
            <p class="page-subtitle">Cari dan lihat daftar peserta yang terdaftar pada kursus tertentu</p>
        </div>
        
        <?php if ($error_message): ?>
        <div class="error-message">
            <?= htmlspecialchars($error_message) ?>
        </div>
        <?php endif; ?>
        
        <div class="search-section">
            <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="search-form">
                <div class="form-group">
                    <label for="course_id" class="form-label">Pilih Kursus</label>
                    <select name="course_id" id="course_id" class="form-control" required>
                        <option value="">-- Pilih Kursus --</option>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>" <?= ($selected_course_id == $course['course_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($course['course_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search_term" class="form-label">Kata Kunci (Opsional)</label>
                    <input type="text" name="search_term" id="search_term" class="form-control" 
                           placeholder="Cari berdasarkan nama atau email" 
                           value="<?= htmlspecialchars($search_term) ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="search" class="btn btn-primary">
                        <span class="material-icons">search</span> Cari
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (isset($_POST['search']) && $selected_course_id): ?>
        <div class="results-section">
            <div class="results-header">
                <h2 class="results-title">Peserta Kursus: <?= htmlspecialchars($course_name) ?></h2>
                <span class="results-count"><?= count($students) ?> peserta</span>
            </div>
            
            <?php if (!empty($students)): ?>
                <div class="table-responsive">
                    <table class="participants-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>Tanggal Pendaftaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= date('d F Y', strtotime($student['registration_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="actions-bar">
                    <button onclick="window.print()" class="btn btn-secondary">
                        <span class="material-icons">print</span> Cetak
                    </button>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="material-icons empty-icon">people</span>
                    <p class="empty-text">Tidak ada peserta yang ditemukan untuk kursus ini.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php elseif (!isset($_POST['search'])): ?>
        <div class="results-section">
            <div class="empty-state">
                <span class="material-icons empty-icon">school</span>
                <p class="empty-text">Silakan pilih kursus dan klik tombol cari untuk melihat daftar peserta.</p>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    <?php include 'bottom-nav.php'; ?>
</body>
</html>