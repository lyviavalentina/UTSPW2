<?php
// config.php - Database connection configuration

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'online_course_system');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect to database. " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Function to handle errors
function handle_error($message) {
    echo '<div class="error-message">' . $message . '</div>';
}

// Function to handle success messages
function handle_success($message) {
    echo '<div class="success-message">' . $message . '</div>';
}
?>