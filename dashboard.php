<?php
// 1. Security Check
include "../includes/auth.php";
include "../includes/database.php";
restrictToAdmin(); // Ensure only admins/managers access this

// 2. Fetch Statistics for the Dashboard 
// Get total notice count
$notice_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM notices");
$notice_count = mysqli_fetch_assoc($notice_count_query)['total'];

// Get total user count
$user_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$user_count = mysqli_fetch_assoc($user_count_query)['total'];

// Get pending comments count
$comment_count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM comments WHERE status = 'pending'");
$comment_count = mysqli_fetch_assoc($comment_count_query)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h2>Admin Control Panel</h2>
            <a href="../includes/auth.php?action=logout" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="row text-center mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h3><?php echo $notice_count; ?></h3>
                    <p class="text-muted">Total Notices</p>
                    <a href="manage-notices.php" class="btn btn-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h3><?php echo $user_count; ?></h3>
                    <p class="text-muted">Registered Users</p>
                    <a href="manage-users.php" class="btn btn-success btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h3><?php echo $comment_count; ?></h3>
                    <p class="text-muted">Pending Comments</p>
                    <button class="btn btn-warning btn-sm">Moderate</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-dark text-white">Quick Actions</div>
                <div class="card-body">
                    <a href="../index.php" class="btn btn-outline-dark">View Notice Board</a>
                    <button class="btn btn-info" onclick="alert('Navigate to Add Notice Page')">Add New Notice</button>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>