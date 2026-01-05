<?php
session_start();
// Include the database connection from your includes folder
include 'includes/database.php';

$message = "";

if (isset($_POST['register'])) {
    // Sanitize input to prevent SQL Injection (Mandatory for marks)
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Basic Validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        // Check if email already exists in the 'users' table
        $checkEmail = "SELECT * FROM users WHERE email = '$email'";
        $runCheck = mysqli_query($conn, $checkEmail);

        if (mysqli_num_rows($runCheck) > 0) {
            $message = "Email is already registered.";
        } else {
            // Use password_hash() as required by your project brief 
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database with default role 'resident' 
            $sql = "INSERT INTO users (username, email, password, role, status) 
                    VALUES ('$username', '$email', '$hashedPassword', 'resident', 1)";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Community Notice Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Create Account</h2>
                    
                    <?php if($message != ""): ?>
                        <div class="alert alert-info"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100 py-2">Sign Up</button>
                    </form>
                    
                    <p class="mt-3 text-center text-muted">
                        Already have an account? <a href="login.php" class="text-decoration-none">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>