<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../includes/database.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Remove Bookmark Logic
if (isset($_GET['remove'])) {
    $notice_id = intval($_GET['remove']);
    $rem_sql = "DELETE FROM bookmarks WHERE user_id = $user_id AND notice_id = $notice_id";
    mysqli_query($conn, $rem_sql);
    header("Location: bookmarks.php?msg=Removed");
    exit;
}

// Fetch bookmarked notices
$sql = "SELECT n.notice_id, n.title, n.priority, n.created_at, c.name AS category_name, c.color_code, u.username 
        FROM bookmarks b
        JOIN notices n ON b.notice_id = n.notice_id
        JOIN users u ON n.user_id = u.user_id
        LEFT JOIN categories c ON n.category_id = c.category_id
        WHERE b.user_id = $user_id
        ORDER BY n.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Saved Notices - CNBS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    body { background-color: #f8f9fa; }
    .notice-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
    .notice-title { font-weight: 600; font-size: 1.2rem; margin-bottom: 5px; }
    .badge-category { padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; color: #fff; margin-right: 5px; }
    .badge-priority-high { background-color: #e74c3c; }
    .badge-priority-medium { background-color: #f1c40f; color:#000; }
    .badge-priority-low { background-color: #2ecc71; }
    .btn-remove { font-size: 0.8rem; }
</style>
</head>
<body>

<div class="container py-5">
    <h2 class="mb-4">My Bookmarked Notices</h2>

    <div class="row g-3">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4">
                <div class="notice-card d-flex flex-column">
                    <div>
                        <h5 class="notice-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="text-muted small mb-2">Posted by: <?php echo htmlspecialchars($row['username']); ?></p>

                        <?php if($row['category_name']): ?>
                            <span class="badge-category" style="background-color: <?php echo $row['color_code']; ?>">
                                <?php echo htmlspecialchars($row['category_name']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="badge badge-priority-<?php echo strtolower($row['priority']); ?>">
                            <?php echo htmlspecialchars($row['priority']); ?>
                        </span>
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <a href="../view-notice.php?id=<?php echo $row['notice_id']; ?>" class="btn btn-primary btn-sm">Read More</a>
                        <a href="bookmarks.php?remove=<?php echo $row['notice_id']; ?>" class="btn btn-outline-danger btn-sm btn-remove">Remove</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center mt-5">
                <p class="text-muted">You haven't bookmarked any notices yet.</p>
                <a href="../index.php" class="btn btn-primary">Browse Notices</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
