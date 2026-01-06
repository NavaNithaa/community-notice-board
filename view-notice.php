<?php
session_start();
include "includes/database.php";
include "includes/functions.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle bookmark toggle
$bookmarkMessage = '';
if (isset($_POST['toggle_bookmark'])) {
    $notice_id = intval($_POST['notice_id']);

    // Check if already bookmarked
    $check = mysqli_query($conn, "SELECT * FROM bookmarks WHERE user_id=$user_id AND notice_id=$notice_id");
    if(mysqli_num_rows($check) > 0) {
        // Remove bookmark
        mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id=$user_id AND notice_id=$notice_id");
        $bookmarkMessage = "Bookmark removed ✅";
    } else {
        // Add bookmark
        mysqli_query($conn, "INSERT INTO bookmarks (user_id, notice_id) VALUES ($user_id, $notice_id)");
        $bookmarkMessage = "Bookmarked ✅";
    }
}

// Get notice ID
$notice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($notice_id <= 0) { echo "Invalid notice ID."; exit; }

// Fetch notice
$stmt = mysqli_prepare($conn, "SELECT n.*, c.name AS category_name, c.color_code
                                FROM notices n 
                                LEFT JOIN categories c ON n.category_id = c.category_id 
                                WHERE n.notice_id = ?");
mysqli_stmt_bind_param($stmt, "i", $notice_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$notice = mysqli_fetch_assoc($result);

if (!$notice) { echo "Notice not found or expired."; exit; }

$priority_class = strtolower($notice['priority']); // high/medium/low
mysqli_query($conn, "UPDATE notices SET views = views + 1 WHERE notice_id = $notice_id");

// Fetch approved comments
$commentStmt = mysqli_prepare($conn, "SELECT c.*, u.username 
                                       FROM comments c 
                                       JOIN users u ON c.user_id = u.user_id 
                                       WHERE c.notice_id = ? AND c.status='approved'
                                       ORDER BY c.created_at ASC");
mysqli_stmt_bind_param($commentStmt, "i", $notice_id);
mysqli_stmt_execute($commentStmt);
$commentResult = mysqli_stmt_get_result($commentStmt);
$comments = mysqli_fetch_all($commentResult, MYSQLI_ASSOC);

// Check if bookmarked
$bookmarkCheck = mysqli_query($conn, "SELECT * FROM bookmarks WHERE user_id=$user_id AND notice_id=$notice_id");
$isBookmarked = mysqli_num_rows($bookmarkCheck) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($notice['title']); ?> - CNBS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
    body { background-color: #f8f9fa; }
    .notice-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .notice-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 10px; }
    .badge-category { padding: 5px 12px; border-radius: 12px; color: #fff; font-size: 0.9rem; margin-right: 10px; }
    .badge-priority-high { background-color: #e74c3c; }
    .badge-priority-medium { background-color: #f1c40f; color:#000; }
    .badge-priority-low { background-color: #2ecc71; }
    .notice-date { color: #888; font-size: 0.9rem; }
    .comment { border-bottom: 1px solid #eee; padding: 10px 0; }
    .comment-user { font-weight: 600; }
    .bookmark-btn { float:right; }
    textarea { resize: none; }
</style>
</head>
<body>

<div class="container py-5">

    <?php if($bookmarkMessage): ?>
        <div class="alert alert-success"><?php echo $bookmarkMessage; ?></div>
    <?php endif; ?>

    <div class="notice-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="notice-title">
                <?php 
                    echo $priority_class == "high" ? "🚨 " : ($priority_class == "medium" ? "⚠️ " : "ℹ️ ");
                ?>
                <?php echo htmlspecialchars($notice['title']); ?>
            </h3>
            <form method="POST" class="bookmark-btn">
                <input type="hidden" name="notice_id" value="<?php echo $notice_id; ?>">
                <button type="submit" name="toggle_bookmark" class="btn btn-sm <?php echo $isBookmarked ? 'btn-success' : 'btn-outline-secondary'; ?>">
                    <?php echo $isBookmarked ? 'Bookmarked ✅' : 'Bookmark'; ?>
                </button>
            </form>
        </div>

        <?php if($notice['category_name']): ?>
            <span class="badge-category" style="background-color: <?php echo $notice['color_code']; ?>">
                <?php echo htmlspecialchars($notice['category_name']); ?>
            </span>
        <?php endif; ?>
        <span class="badge badge-priority-<?php echo $priority_class; ?>">
            <?php echo htmlspecialchars($notice['priority']); ?>
        </span>

        <p class="mt-3"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
        <p class="notice-date">Posted on: <?php echo date("d M Y H:i", strtotime($notice['created_at'])); ?> | Views: <?php echo $notice['views']+1; ?></p>
    </div>

    <!-- Comments Section -->
    <div class="mt-5">
        <h5>Comments (<?php echo count($comments); ?>)</h5>
        <?php if(count($comments) > 0): ?>
            <?php foreach($comments as $c): ?>
                <div class="comment">
                    <span class="comment-user"><?php echo htmlspecialchars($c['username']); ?></span>
                    <span class="text-muted" style="font-size:0.8rem;"> - <?php echo date("d M Y H:i", strtotime($c['created_at'])); ?></span>
                    <p><?php echo nl2br(htmlspecialchars($c['content'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No comments yet.</p>
        <?php endif; ?>
    </div>

    <!-- Add Comment Form -->
    <div class="mt-4">
        <h5>Add a Comment</h5>
        <form method="POST" action="includes/functions.php">
            <input type="hidden" name="notice_id" value="<?php echo $notice_id; ?>">
            <div class="mb-3">
                <textarea name="comment_content" class="form-control" rows="3" placeholder="Write your comment..." required></textarea>
            </div>
            <button type="submit" name="add_comment" class="btn btn-primary">Submit Comment</button>
        </form>
    </div>

    <a href="index.php" class="btn btn-outline-secondary mt-4">Back to Notices</a>
</div>

</body>
</html>
