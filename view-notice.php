<?php
include "includes/auth.php"; 
include "includes/database.php";
include "includes/functions.php";

// 1. Get Notice ID from URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$notice_id = intval($_GET['id']);

// 2. Fetch Notice Details with Category
// Using LEFT JOIN ensures the page loads even if a category or user is missing
$sql = "SELECT n.*, c.category_name, u.username 
        FROM notices n 
        LEFT JOIN categories c ON n.category_id = c.category_id 
        LEFT JOIN users u ON n.user_id = u.user_id 
        WHERE n.notice_id = $notice_id";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Mapper Error: " . mysqli_error($conn)); // This will tell us if it's still failing
}

$notice = mysqli_fetch_assoc($result);

if (!$notice) {
    echo "Notice not found.";
    exit();
}

$notice = mysqli_fetch_assoc($result);

// 3. Fetch Approved Comments for this Notice
$comments_sql = "SELECT c.*, u.username FROM comments c 
                 JOIN users u ON c.user_id = u.user_id 
                 WHERE c.notice_id = $notice_id AND c.status = 'approved' 
                 ORDER BY c.created_at DESC";
$comments_result = mysqli_query($conn, $comments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($notice['title']); ?> - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <span class="badge bg-primary mb-2">
                        <?php echo $notice['priority']; ?> Priority
                    </span>
                    <h1 class="display-6"><?php echo htmlspecialchars($notice['title']); ?></h1>
                    <p class="text-muted small">
                        Category: <?php echo $notice['category_name'] ?? 'Uncategorized'; ?> | 
                        Posted by: <?php echo $notice['username'] ?? 'System'; ?> | 
                        Date: <?php echo date('M d, Y', strtotime($notice['created_at'])); ?>
                    </p>
                    <hr>
                    <p class="lead" style="white-space: pre-wrap;"><?php echo htmlspecialchars($notice['content']); ?></p>
                </div>
                <div class="card-footer bg-white border-top-0 d-flex justify-content-between">
                    <a href="index.php" class="btn btn-outline-secondary">Back to Board</a>
                    <button class="btn btn-outline-primary" onclick="alert('Added to Bookmarks!')">Save for Later</button>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5>Comments</h5></div>
                <div class="card-body">
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="mb-4">
                        <textarea id="commentText" class="form-control" rows="3" placeholder="Write a comment..."></textarea>
                        <button class="btn btn-primary mt-2" onclick="postComment(<?php echo $notice_id; ?>, document.getElementById('commentText').value)">Post Comment</button>
                    </div>
                    <?php else: ?>
                        <p class="text-muted"><a href="login.php">Login</a> to join the conversation.</p>
                    <?php endif; ?>

                    <div id="commentList">
                        <?php if(mysqli_num_rows($comments_result) > 0): ?>
                            <?php while($c = mysqli_fetch_assoc($comments_result)): ?>
                                <div class="border-bottom py-2 mb-2">
                                    <strong><?php echo htmlspecialchars($c['username']); ?></strong> 
                                    <span class="text-muted small">| <?php echo date('d M, H:i', strtotime($c['created_at'])); ?></span>
                                    <p class="mb-0 mt-1"><?php echo htmlspecialchars($c['comment_text']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No comments yet. Be the first to join the discussion!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/ajax.js"></script>
</body>
</html>