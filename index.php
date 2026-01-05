<?php
// 1. Include security and database connection
include "includes/auth.php"; 
include "includes/database.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Notice Board</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Community Notices</h1>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="user/profile.php" class="btn btn-outline-primary">My Profile</a>
                <a href="includes/auth.php?action=logout" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Login</a>
                <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" id="searchInput" class="form-control" placeholder="Search notices...">
        </div>
        <div class="col-md-3">
            <select id="categorySelect" class="form-select">
                <option value="">All Categories</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Events">Events</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="prioritySelect" class="form-select">
                <option value="">All Priorities</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
            </select>
        </div>
    </div>

    <div id="noticeList" class="row">
        <p class="text-center">Loading notices...</p>
    </div>
</div>

<script src="assets/js/ajax.js"></script>
<script src="assets/js/main.js"></script>

<script>
    // Initial load of notices when the page opens
    document.addEventListener("DOMContentLoaded", () => {
        fetchNotices(); 
    });

    // Trigger search when typing
    document.getElementById('searchInput').addEventListener('input', (e) => {
        const search = e.target.value;
        const category = document.getElementById('categorySelect').value;
        const priority = document.getElementById('prioritySelect').value;
        fetchNotices(search, category, priority);
    });

    // Trigger filter when changing category
    document.getElementById('categorySelect').addEventListener('change', (e) => {
        const search = document.getElementById('searchInput').value;
        const category = e.target.value;
        const priority = document.getElementById('prioritySelect').value;
        fetchNotices(search, category, priority);
    });

    // Trigger filter when changing priority
    document.getElementById('prioritySelect').addEventListener('change', (e) => {
        const search = document.getElementById('searchInput').value;
        const category = document.getElementById('categorySelect').value;
        const priority = e.target.value;
        fetchNotices(search, category, priority);
    });
</script>

</body>
</html>