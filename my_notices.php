<?php
include "../includes/auth.php"; 
include "../includes/database.php";
checkAuth(); // Ensure only logged-in users can see this

$user_id = $_SESSION['user_id'];

// Handle Deletion by the Owner
if (isset($_GET['delete'])) {
    $notice_id = intval($_GET['delete']);
    // Security: Only delete if the notice belongs to this user
    $del_sql = "DELETE FROM notices WHERE notice_id = $notice_id AND user_id = $user_id";
    mysqli_query($conn, $del_sql);
    header("Location: my-notices.php?msg=Deleted");
}

$sql = "SELECT * FROM notices WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Notices - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Published Notices</h2>
        <a href="../index.php" class="btn btn-outline-secondary">Back to Board</a>
    </div>

    <div class="table-responsive bg-white p-3 shadow-sm rounded">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Priority</th>
                    <th>Date Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?php echo $row['title']; ?></strong></td>
                        <td><span class="badge bg-<?php echo ($row['priority'] == 'High' ? 'danger' : 'info'); ?>">
                            <?php echo $row['priority']; ?></span></td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="../view-notice.php?id=<?php echo $row['notice_id']; ?>" class="btn btn-sm btn-light border">View</a>
                            <a href="my-notices.php?delete=<?php echo $row['notice_id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Delete this notice?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">You haven't posted any notices yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>