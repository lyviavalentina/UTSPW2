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
    <div class="nav-item more-dropdown-container">
        <div class="nav-item more-dropdown-trigger <?php echo ($current_page == 'manage-courses.php' || $current_page == 'view-participants.php') ? 'active' : ''; ?>">
            <span class="material-icons nav-icon">more_horiz</span>
            <span>More</span>
        </div>
        
        <div class="more-dropdown">
            <a href="manage-courses.php" class="dropdown-item">
                <span class="material-icons">edit</span>
                Manage Courses
            </a>
            <a href="more.php" class="dropdown-item">
                <span class="material-icons">groups</span>
                View Participants
            </a>
        </div>
    </div>
</div>
</div>
<style>
/* Tambahkan CSS ini ke file styles Anda */
.more-dropdown-container {
    position: relative;
}

.more-dropdown {
    position: absolute;
    bottom: 60px;
    right: 0;
    background: var(--white);
    border-radius: 12px;
    box-shadow: var(--shadow);
    padding: 0.5rem;
    min-width: 180px;
    display: none;
    z-index: 1001;
}

.more-dropdown.show {
    display: block;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    color: var(--text);
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background: var(--background);
}

.dropdown-item .material-icons {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .more-dropdown {
        bottom: 50px;
        right: -10px;
    }
}
</style>

<script>
// Tambahkan script ini di akhir file
document.querySelector('.more-dropdown-trigger').addEventListener('click', function(e) {
    e.preventDefault();
    const dropdown = this.parentNode.querySelector('.more-dropdown');
    dropdown.classList.toggle('show');
});

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(e) {
    if (!e.target.closest('.more-dropdown-container')) {
        document.querySelectorAll('.more-dropdown').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }
});
</script>
