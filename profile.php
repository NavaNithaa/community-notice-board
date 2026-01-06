<?php
include "../includes/auth.php"; // Ensure user is logged in
include "../includes/database.php";

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Profile Update
if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $update_sql = "UPDATE users SET username = '$username', email = '$email' WHERE user_id = $user_id";
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['username'] = $username; // Update session name
        $message = "Profile updated successfully!";
    }
}

// Fetch current user data
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg rounded-4 border-0">
                <div class="card-header bg-primary text-white rounded-top-4">
                    <h4 class="mb-0 fw-bold">Account Settings</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($message != ""): ?>
                        <div class="alert alert-success rounded-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control rounded-3" value="<?php echo $user['username']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role</label>
                            <input type="text" class="form-control rounded-3" value="<?php echo ucfirst($user['role']); ?>" disabled>
                            <small class="text-muted">Contact admin to change your role.</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="submit" name="update" class="btn btn-primary fw-bold px-4">Save Changes</button>
                            <a href="../index.php" class="btn btn-outline-secondary fw-semibold px-4">Back to Board</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>