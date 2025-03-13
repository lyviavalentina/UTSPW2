<?php
// bottom-nav.php - Bottom navigation component for all pages

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div>
<div class="bottom-nav">
    <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <span class="material-icons nav-icon">home</span>
        <span>Home</span>
    </a>
    <a href="my-courses.php" class="nav-item <?php echo ($current_page == 'my-courses.php') ? 'active' : ''; ?>">
        <span class="material-icons nav-icon">book</span>
        <span>My Courses</span>
    </a>
    <a href="profile.php" class="nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <span class="material-icons nav-icon">person</span>
        <span>Profile</span>
    </a>
    <a href="more.php" class="nav-item <?php echo ($current_page == 'more.php') ? 'active' : ''; ?>">
        <span class="material-icons nav-icon">more_horiz</span>
        <span>More</span>
    </a>
</div>
</div>