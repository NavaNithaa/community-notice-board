<?php
session_start();
include 'includes/database.php';

$message = "";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        $checkEmail = "SELECT * FROM users WHERE email = '$email'";
        $runCheck = mysqli_query($conn, $checkEmail);

        if (mysqli_num_rows($runCheck) > 0) {
            $message = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password, role, status) 
                    VALUES ('$username', '$email', '$hashedPassword', 'resident', 1)";
            if (mysqli_query($conn, $sql)) {
                $message = "<span class='text-success'>Registration successful!</span> <a href='login.php'>Login here</a>";
            } else {
                $message = "<span class='text-danger'>Error: " . mysqli_error($conn) . "</span>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - CNBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4 fw-bold text-primary">Create Account</h2>

                    <?php if($message != ""): ?>
                        <div class="alert alert-info rounded-3"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control rounded-3" placeholder="Enter username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="email@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Create a password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-bold">Sign Up</button>
                    </form>

                    <p class="mt-3 text-center text-muted small">
                        Already have an account? <a href="login.php" class="text-decoration-none text-primary fw-semibold">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
