<?php
session_start();
// Use the mandatory path for your database connection
include 'includes/database.php';

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Prepared statement to prevent SQL Injection (Required for Marks)
    $sql = "SELECT * FROM users WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Verify password using password_hash/verify as per project brief
            if (password_verify($password, $user['password'])) {
                // Correcting column name: Your SQL uses 'user_id', not 'id'
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                session_regenerate_id(true);

                // Redirect based on role
                if ($user['role'] == 'admin' || $user['role'] == 'manager') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Community Notice Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Login</h2>
                    
                    <?php if($error != ""): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2">Sign In</button>
                    </form>
                    
                    <p class="mt-3 text-center text-muted">
                        Don't have an account? <a href="register.php" class="text-decoration-none">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>