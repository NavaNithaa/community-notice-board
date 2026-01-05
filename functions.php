<?php
// functions.php - Helper functions
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkRole($role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != $role) {
        header("Location: ../login.php");
        exit();
    }
}
?>