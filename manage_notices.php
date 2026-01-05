<?php
include "../includes/auth.php"; // Check if admin is logged in
include "../includes/database.php";

// Handle Deletion logic (from your delete_notice.php)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM notices WHERE notice_id = $id";
    mysqli_query($conn, $sql);
    header("Location: manage-notices.php?msg=Deleted");
}

// Fetch notices logic (from your fetch_notices.php)
$sql = "SELECT notices.*, users.username FROM notices 
        JOIN users ON notices.user_id = users.user_id 
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Notices - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Manage Community Notices</h2>
    <a href="../index.php" class="btn btn-secondary mb-3">Back to Site</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Title</th>
                <th>Posted By</th>
                <th>Priority</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><span class="badge bg-primary"><?php echo $row['priority']; ?></span></td>
                <td>
                    <a href="manage-notices.php?delete=<?php echo $row['notice_id']; ?>" 
                       class="btn btn-danger btn-sm" 
                       onclick="return confirm('Delete this notice?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>