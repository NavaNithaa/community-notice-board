<?php
// search.php - Handles search queries
include "includes/database.php";

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Build the SQL query
$sql = "SELECT n.*, c.category_name 
        FROM notices n 
        JOIN categories c ON n.category_id = c.category_id 
        WHERE (n.title LIKE '%$search%' OR n.content LIKE '%$search%')";

if (!empty($category)) {
    $sql .= " AND n.category_id = '$category'";
}

$sql .= " ORDER BY n.created_at DESC";

$result = mysqli_query($conn, $sql);
$notices = [];

while ($row = mysqli_fetch_assoc($result)) {
    $notices[] = $row;
}

// Return data as JSON for the AJAX script
header('Content-Type: application/json');
echo json_encode($notices);
?>