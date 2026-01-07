<?php
// login.php
session_start();
include "includes/database.php";

// Redirect already logged-in users
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Fetch user with status=1 only
    $query = "SELECT * FROM users WHERE email='$email' AND status=1 LIMIT 1";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if(password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            // Redirect based on role
            if($user['role'] === 'admin' || $user['role'] === 'manager') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found or account disabled.";
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

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Login</h3>

                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>

                    <p class="text-center mt-3">
                        Don't have an account? <a href="register.php">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
