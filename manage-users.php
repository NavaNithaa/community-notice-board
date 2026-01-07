<?php
include "../includes/auth.php";
include "../includes/database.php";
restrictToAdmin(); // make sure only admin/manager can access

// Handle Status Toggle (Enable/Disable User)
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $new_status = intval($_GET['status']);
    mysqli_query($conn, "UPDATE users SET status=$new_status WHERE user_id=$id");
    header("Location: manage-users.php?msg=StatusUpdated");
    exit;
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
    header("Location: manage-users.php?msg=Deleted");
    exit;
}

// Handle Edit User Submission
if (isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    mysqli_query($conn, "UPDATE users SET username='$username', email='$email', role='$role' WHERE user_id=$id");
    header("Location: manage-users.php?msg=Updated");
    exit;
}

// Fetch all users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Member Management</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <table class="table table-hover">
        <thead class="table-dark">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><span class="badge bg-info text-dark"><?php echo ucfirst($row['role']); ?></span></td>
                <td>
                    <?php if($row['status'] == 1): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Disabled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Edit Button triggers modal -->
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $row['user_id']; ?>">Edit</button>

                    <!-- Enable/Disable -->
                    <?php if($row['status'] == 1): ?>
                        <a href="manage-users.php?id=<?php echo $row['user_id']; ?>&status=0" class="btn btn-warning btn-sm">Disable</a>
                    <?php else: ?>
                        <a href="manage-users.php?id=<?php echo $row['user_id']; ?>&status=1" class="btn btn-success btn-sm">Enable</a>
                    <?php endif; ?>

                    <!-- Delete -->
                    <a href="manage-users.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>

            <!-- Modal for editing user -->
            <div class="modal fade" id="editUserModal<?php echo $row['user_id']; ?>" tabindex="-1" aria-labelledby="editUserLabel<?php echo $row['user_id']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="manage-users.php">
                      <div class="modal-header">
                        <h5 class="modal-title" id="editUserLabel<?php echo $row['user_id']; ?>">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                          <div class="mb-3">
                              <label>Username</label>
                              <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                          </div>
                          <div class="mb-3">
                              <label>Email</label>
                              <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                          </div>
                          <div class="mb-3">
                              <label>Role</label>
                              <select name="role" class="form-select" required>
                                  <option value="resident" <?php if($row['role']=='resident') echo 'selected'; ?>>Resident</option>
                                  <option value="manager" <?php if($row['role']=='manager') echo 'selected'; ?>>Manager</option>
                                  <option value="admin" <?php if($row['role']=='admin') echo 'selected'; ?>>Admin</option>
                              </select>
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>

        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
