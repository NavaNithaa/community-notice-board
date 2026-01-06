<?php
include "../includes/auth.php";
include "../includes/database.php";

restrictToAdminOrManager(); // Admin & manager access

$isManager = $_SESSION['role'] === 'manager';
$msg = "";

// --------------------------
// CATEGORY LIST
// --------------------------
$categories = [
    1 => 'Events',
    2 => 'Maintenance',
    3 => 'Emergency',
    4 => 'General'
];

// --------------------------
// CREATE OR EDIT NOTICE
// --------------------------
if (isset($_POST['save_notice'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $priority = $_POST['priority'];
    $category_id = intval($_POST['category']);
    $expiry = $_POST['expiry'] ?? NULL;
    $notice_id = $_POST['notice_id'] ?? NULL;
    $user_id = $_SESSION['user_id'];

    if ($notice_id) {
        // Edit existing notice
        $sql = "UPDATE notices 
                SET title='$title', content='$content', priority='$priority', category_id=$category_id, expiry_date=" . ($expiry ? "'$expiry'" : "NULL") . " 
                WHERE notice_id=$notice_id";
        mysqli_query($conn, $sql);
        $msg = "Notice updated successfully!";
    } else {
        // Create new notice
        $sql = "INSERT INTO notices (user_id, title, content, priority, category_id, created_at, expiry_date) 
                VALUES ($user_id, '$title', '$content', '$priority', $category_id, NOW(), " . ($expiry ? "'$expiry'" : "NULL") . ")";
        mysqli_query($conn, $sql);
        $msg = "Notice created successfully!";
    }
}

// --------------------------
// DELETE NOTICE
// --------------------------
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Managers cannot delete notices created by admins
    if ($isManager) {
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM notices WHERE notice_id=$id"));
        $creator_role = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role FROM users WHERE user_id=".$check['user_id']))['role'] ?? '';
        if ($creator_role === 'admin') {
            $msg = "Managers cannot delete admin notices!";
        } else {
            mysqli_query($conn, "DELETE FROM notices WHERE notice_id=$id");
            $msg = "Notice deleted!";
        }
    } else {
        mysqli_query($conn, "DELETE FROM notices WHERE notice_id=$id");
        $msg = "Notice deleted!";
    }
}

// --------------------------
// FETCH NOTICES
// --------------------------
$result = mysqli_query($conn, "SELECT n.*, u.username, u.role FROM notices n JOIN users u ON n.user_id=u.user_id ORDER BY created_at DESC");

// --------------------------
// FETCH NOTICE TO EDIT
// --------------------------
$edit_notice = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_notice = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM notices WHERE notice_id=$edit_id"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Notices - CNBS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Notices</h2>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success"><?=$msg?></div>
    <?php endif; ?>

    <!-- Notice Form -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white"><?= $edit_notice ? "Edit Notice" : "Create New Notice" ?></div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="notice_id" value="<?= $edit_notice['notice_id'] ?? '' ?>">

                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($edit_notice['title'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <?php
                        foreach($categories as $id => $name) {
                            $selected = ($edit_notice['category_id'] ?? '') == $id ? "selected" : "";
                            echo "<option value='$id' $selected>$name</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select" required>
                        <?php
                        $priorities = ['High','Medium','Low'];
                        foreach($priorities as $p) {
                            $selected = ($edit_notice['priority'] ?? '') === $p ? "selected" : "";
                            echo "<option value='$p' $selected>$p</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry" class="form-control" value="<?= $edit_notice['expiry_date'] ?? '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Content</label>
                    <textarea name="content" class="form-control" required><?= htmlspecialchars($edit_notice['content'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="save_notice" class="btn btn-primary"><?= $edit_notice ? "Update Notice" : "Create Notice" ?></button>
            </form>
        </div>
    </div>

    <!-- Notices Table -->
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Posted By</th>
                <th>Created At</th>
                <th>Expiry</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= $categories[$row['category_id']] ?? '-' ?></td>
                <td><?= htmlspecialchars($row['priority']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= $row['expiry_date'] ?? '-' ?></td>
                <td>
                    <a href="manage-notices.php?edit=<?= $row['notice_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <?php
                    $creator_role = $row['role'] ?? '';
                    if (!$isManager || $creator_role !== 'admin') : ?>
                        <a href="manage-notices.php?delete=<?= $row['notice_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this notice?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    CKEDITOR.replace('content');
</script>
</body>
</html>
