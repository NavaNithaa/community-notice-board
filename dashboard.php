<?php
include "../includes/auth.php";
include "../includes/database.php";

restrictToAdminOrManager();

$isManager = $_SESSION['role'] === 'manager';

$backup_msg = $restore_msg = "";

// ---------------------------
// MODERATE COMMENTS ACTIONS
// ---------------------------
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE comments SET status='approved' WHERE comment_id=$id");
}

if (isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    mysqli_query($conn, "UPDATE comments SET status='rejected' WHERE comment_id=$id");
}

if (isset($_GET['delete_comment']) && $_SESSION['role'] === 'admin') {
    $id = intval($_GET['delete_comment']);
    mysqli_query($conn, "DELETE FROM comments WHERE comment_id=$id");
}

// ---------------------------
// BACKUP & RESTORE (ADMIN ONLY)
// ---------------------------
if (!$isManager) {

    if (isset($_POST['backup_db'])) {
        $tables = [];
        $result = mysqli_query($conn, "SHOW TABLES");
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
            $createTable = mysqli_fetch_assoc(mysqli_query($conn, "SHOW CREATE TABLE `$table`"))['Create Table'];
            $sqlScript .= $createTable . ";\n\n";

            $rows = mysqli_query($conn, "SELECT * FROM `$table`");
            while ($r = mysqli_fetch_assoc($rows)) {
                $cols = implode(",", array_map(fn($c) => "`$c`", array_keys($r)));
                $vals = implode(",", array_map(fn($v) => "'" . mysqli_real_escape_string($conn, $v) . "'", array_values($r)));
                $sqlScript .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
            }
            $sqlScript .= "\n";
        }

        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="cnbs_backup_' . date('Ymd_His') . '.sql"');
        echo $sqlScript;
        exit;
    }

    if (isset($_POST['restore_db']) && isset($_FILES['sql_file'])) {
        $sqlFile = $_FILES['sql_file']['tmp_name'];
        $sqlContent = file_get_contents($sqlFile);
        $queries = explode(";\n", $sqlContent);

        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query) {
                mysqli_query($conn, $query);
            }
        }
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

        $restore_msg = "Database restored successfully!";
    }
}

// ---------------------------
// DASHBOARD STATS
// ---------------------------
$notice_count  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM notices"))['total'];
$user_count    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$comment_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM comments WHERE status='pending'"))['total'];

// ---------------------------
// FETCH PENDING COMMENTS
// ---------------------------
$pending_comments = mysqli_query($conn, "
    SELECT c.comment_id, c.content, c.created_at,
           u.username, n.title
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    JOIN notices n ON c.notice_id = n.notice_id
    WHERE c.status = 'pending'
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - CNBS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
    #moderate-comments-section {
        display: none;
    }
</style>

<script>
function toggleModerateComments() {
    const section = document.getElementById('moderate-comments-section');
    section.style.display = (section.style.display === 'none' || section.style.display === '')
        ? 'block'
        : 'none';
}
</script>

</head>
<body class="bg-light">

<div class="container mt-5" id="top">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Admin Control Panel</h2>
        <a href="../includes/auth.php?action=logout" class="btn btn-danger">Logout</a>
    </div>

    <?php if ($backup_msg): ?>
        <div class="alert alert-success"><?= $backup_msg ?></div>
    <?php endif; ?>

    <?php if ($restore_msg): ?>
        <div class="alert alert-success"><?= $restore_msg ?></div>
    <?php endif; ?>

    <!-- DASHBOARD CARDS -->
    <div class="row text-center mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h3><?= $notice_count ?></h3>
                    <p>Total Notices</p>
                    <a href="manage-notices.php" class="btn btn-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>

        <?php if (!$isManager): ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h3><?= $user_count ?></h3>
                    <p>Registered Users</p>
                    <a href="manage-users.php" class="btn btn-success btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-4">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h3><?= $comment_count ?></h3>
                    <p>Pending Comments</p>
                    <button onclick="toggleModerateComments()" class="btn btn-warning btn-sm">
                        Moderate
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- BACKUP / RESTORE (ADMIN ONLY) -->
    <?php if (!$isManager): ?>
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">Backup Database</div>
                <div class="card-body">
                    <form method="post">
                        <button type="submit" name="backup_db" class="btn btn-success">Download Backup</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">Restore Database</div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="sql_file" class="form-control mb-2" required>
                        <button type="submit" name="restore_db" class="btn btn-primary">Restore</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- MODERATE COMMENTS (HIDDEN BY DEFAULT) -->
    <div id="moderate-comments-section">
        <hr class="my-5">

        <h4>Moderate Comments</h4>

        <table class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Notice</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if (mysqli_num_rows($pending_comments) === 0): ?>
                <tr>
                    <td colspan="5" class="text-center">No pending comments</td>
                </tr>
            <?php endif; ?>

            <?php while ($c = mysqli_fetch_assoc($pending_comments)): ?>
                <tr>
                    <td><?= htmlspecialchars($c['username']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= htmlspecialchars($c['content']) ?></td>
                    <td><?= $c['created_at'] ?></td>
                    <td>
                        <a href="?approve=<?= $c['comment_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                        <a href="?reject=<?= $c['comment_id'] ?>" class="btn btn-warning btn-sm">Reject</a>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="?delete_comment=<?= $c['comment_id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this comment?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>

            </tbody>
        </table>
    </div>

</div>
</body>
</html>
