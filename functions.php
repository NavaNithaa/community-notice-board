<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "database.php";

// Ensure user is logged in before performing actions
if (!isset($_SESSION['user_id'])) {
    header("Location: ../cnbs/login.php");
    exit;
}

// Set logged-in user ID
$user_id = $_SESSION['user_id'];


// --------------------------
// TOGGLE BOOKMARK
// --------------------------
if (isset($_POST['toggle_bookmark']) && isset($_POST['notice_id'])) {
    $notice_id = intval($_POST['notice_id']);

    // Check if already bookmarked
    $check = mysqli_query($conn, "SELECT * FROM bookmarks WHERE user_id=$user_id AND notice_id=$notice_id");

    if(mysqli_num_rows($check) > 0) {
        // Remove bookmark
        mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id=$user_id AND notice_id=$notice_id");
        // If this is an AJAX request, return JSON
        if(isset($_POST['ajax'])) {
            echo json_encode(['status' => 'removed', 'message' => 'Bookmark removed']);
            exit;
        }
    } else {
        // Add bookmark
        mysqli_query($conn, "INSERT INTO bookmarks (user_id, notice_id) VALUES ($user_id, $notice_id)");
        if(isset($_POST['ajax'])) {
            echo json_encode(['status' => 'added', 'message' => 'Bookmarked']);
            exit;
        }
    }

    // If not AJAX, redirect back
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// --------------------------
// ADD COMMENT
// --------------------------
if (isset($_POST['add_comment']) && isset($_POST['notice_id']) && isset($_POST['comment_content'])) {
    $notice_id = intval($_POST['notice_id']);
    $content = mysqli_real_escape_string($conn, $_POST['comment_content']);

    // Insert comment with default status 'pending'
    mysqli_query($conn, "INSERT INTO comments (user_id, notice_id, content, status, created_at) 
                         VALUES ($user_id, $notice_id, '$content', 'pending', NOW())");

    // Redirect back to notice page
    header("Location: ../view-notice.php?id=$notice_id");
    exit;
}
?>
