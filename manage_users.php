<?php
include "../includes/auth.php"; // Mandatory session check
include "../includes/database.php"; // Database connection

// Handle Status Toggle (Enable/Disable User)
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $new_status = intval($_GET['status']);
    
    $update_sql = "UPDATE users SET status = $new_status WHERE user_id = $id";
    mysqli_query($conn, $update_sql);
    header("Location: manage-users.php?msg=StatusUpdated");
}

// Fetch all users except the currently logged-in admin
$sql = "SELECT user_id, username, email, role, status FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Member Management</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo ucfirst($row['role']); ?></span></td>
                        <td>
                            <?php if($row['status'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['status'] == 1): ?>
                                <a href="manage-users.php?id=<?php echo $row['user_id']; ?>&status=0" class="btn btn-warning btn-sm">Disable</a>
                            <?php else: ?>
                                <a href="manage-users.php?id=<?php echo $row['user_id']; ?>&status=1" class="btn btn-success btn-sm">Enable</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>