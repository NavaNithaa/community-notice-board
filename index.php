<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "includes/auth.php";
include "includes/database.php";
include "includes/functions.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Fetch categories
$categoryQuery = "SELECT * FROM categories ORDER BY name ASC";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = [];
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row;
}

// Get filter inputs from form submission
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';

// Build dynamic WHERE clause
$where = [];
if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $where[] = "(n.title LIKE '%$searchEsc%' OR n.content LIKE '%$searchEsc%')";
}
if ($category !== '') {
    $catEsc = mysqli_real_escape_string($conn, $category);
    $where[] = "c.name='$catEsc'";
}
if ($priority !== '') {
    $prioEsc = mysqli_real_escape_string($conn, $priority);
    $where[] = "n.priority='$prioEsc'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Fetch filtered notices
$noticeQuery = "SELECT n.*, c.name AS category_name, c.color_code
                FROM notices n
                LEFT JOIN categories c ON n.category_id = c.category_id
                $whereSQL
                ORDER BY n.created_at DESC";
$noticeResult = mysqli_query($conn, $noticeQuery);
$notices = [];
while ($row = mysqli_fetch_assoc($noticeResult)) {
    // Check if bookmarked
    if ($user_id) {
        $bmCheck = mysqli_query($conn, "SELECT * FROM bookmarks WHERE user_id=$user_id AND notice_id={$row['notice_id']}");
        $row['isBookmarked'] = mysqli_num_rows($bmCheck) > 0;
    } else {
        $row['isBookmarked'] = false;
    }
    $notices[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Community Notice Board</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/notices.css">
<link rel="stylesheet" href="assets/css/responsive.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Community Notices</h1>
        <div>
            <?php if($user_id): ?>
                <a href="user/profile.php" class="btn btn-outline-primary me-2">My Profile</a>
                <a href="user/bookmarks.php" class="btn btn-warning me-2">Bookmarks</a>
                <a href="includes/auth.php?action=logout" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary me-2">Login</a>
                <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control rounded-3" placeholder="Search notices..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select rounded-3">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat['name']; ?>" <?php echo $category == $cat['name'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="priority" class="form-select rounded-3">
                <option value="">All Priorities</option>
                <option value="High" <?php echo $priority=='High'?'selected':''; ?>>High</option>
                <option value="Medium" <?php echo $priority=='Medium'?'selected':''; ?>>Medium</option>
                <option value="Low" <?php echo $priority=='Low'?'selected':''; ?>>Low</option>
            </select>
        </div>
        <div class="col-12 mt-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Notices -->
    <div class="row g-3">
        <?php if(count($notices) > 0): ?>
            <?php foreach($notices as $n): ?>
                <?php
                    $priority_class = strtolower($n['priority']); // high/medium/low
                    $bookmark_btn_class = $n['isBookmarked'] ? 'btn-success' : 'btn-outline-secondary';
                    $bookmark_btn_text = $n['isBookmarked'] ? 'Bookmarked ✅' : 'Bookmark';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="notice-card notice-<?php echo $priority_class; ?> shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <?php if($n['category_name']): ?>
                                    <span class="badge-category" style="background-color: <?php echo $n['color_code']; ?>;">
                                        <?php echo htmlspecialchars($n['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="notice-priority <?php echo $priority_class; ?>"><?php echo htmlspecialchars($n['priority']); ?></span>
                            </div>
                            <h5 class="notice-title"><?php echo htmlspecialchars($n['title']); ?></h5>
                            <p class="text-muted" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($n['content']); ?>
                            </p>

                            <?php if($user_id): ?>
                                <form method="POST" action="includes/functions.php">
                                    <input type="hidden" name="notice_id" value="<?php echo $n['notice_id']; ?>">
                                    <button type="submit" name="toggle_bookmark" class="btn btn-sm <?php echo $bookmark_btn_class; ?>">
                                        <?php echo $bookmark_btn_text; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-sm btn-outline-secondary">Login to Bookmark</a>
                            <?php endif; ?>

                            <a href="view-notice.php?id=<?php echo $n['notice_id']; ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <p class="text-muted">No notices found matching your criteria.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
