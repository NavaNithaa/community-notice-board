<?php
// includes/post_comment.php
include "auth.php"; // Ensure user is logged in
include "database.php";

// Set response header to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to post a comment.']);
    exit();
}

// Check if required data exists
if (isset($_POST['notice_id']) && isset($_POST['content'])) {
    $user_id = $_SESSION['user_id'];
    $notice_id = intval($_POST['notice_id']);
    $comment_text = mysqli_real_escape_string($conn, $_POST['content']);

    // Insert comment into database with 'pending' status
    $sql = "INSERT INTO comments (notice_id, user_id, comment_text, status) 
            VALUES ($notice_id, $user_id, '$comment_text', 'pending')";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['success' => true, 'message' => 'Comment submitted for approval.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required information.']);
}
?>