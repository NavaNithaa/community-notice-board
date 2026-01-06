<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "database.php";

// --------------------------
// SESSION CHECK
// --------------------------
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

// --------------------------
// ADMIN & MANAGER ACCESS
// --------------------------
function restrictToAdminOrManager() {
    if (!isset($_SESSION['role'])) {
        header("Location: ../login.php");
        exit;
    }
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'manager') {
        header("Location: ../index.php");
        exit;
    }
}

// --------------------------
// ADMIN ONLY ACCESS
// --------------------------
function restrictToAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}

// --------------------------
// LOGOUT
// --------------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>
