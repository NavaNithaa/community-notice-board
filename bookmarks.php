<?php
include "../includes/auth.php";
include "../includes/database.php";
checkAuth();

$user_id = $_SESSION['user_id'];

// Remove Bookmark Logic
if (isset($_GET['remove'])) {
    $notice_id = intval($_GET['remove']);
    $rem_sql = "DELETE FROM bookmarks WHERE user_id = $user_id AND notice_id = $notice_id";
    mysqli_query($conn, $rem_sql);
    header("Location: bookmarks.php?msg=Removed");
}

// Join query to get notice details for bookmarked items
$sql = "SELECT n.notice_id, n.title, n.priority, u.username 
        FROM bookmarks b
        JOIN notices n ON b.notice_id = n.notice_id
        JOIN users u ON n.user_id = u.user_id
        WHERE b.user_id = $user_id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Saved Notices - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h2 class="mb-4">Bookmarked Notices</h2>
    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['title']; ?></h5>
                        <p class="text-muted small">Posted by: <?php echo $row['username']; ?></p>
                        <a href="../view-notice.php?id=<?php echo $row['notice_id']; ?>" class="btn btn-primary btn-sm">Read More</a>
                        <a href="bookmarks.php?remove=<?php echo $row['notice_id']; ?>" class="btn btn-link text-danger btn-sm">Remove</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center mt-5">
                <p class="text-muted">No saved notices found.</p>
                <a href="../index.php" class="btn btn-primary">Browse Notices</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>