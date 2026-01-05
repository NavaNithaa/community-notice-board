<?php
// includes/get_notices.php
include "database.php";

// Capture filter variables from the query string
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$priority = isset($_GET['priority']) ? mysqli_real_escape_string($conn, $_GET['priority']) : '';

// Build the SQL query with filters
$sql = "SELECT n.*, c.name as category_name 
        FROM notices n 
        LEFT JOIN categories c ON n.category_id = c.category_id 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (n.title LIKE '%$search%' OR n.content LIKE '%$search%')";
}
if (!empty($category)) {
    $sql .= " AND c.category_name = '$category'";
}
if (!empty($priority)) {
    $sql .= " AND n.priority = '$priority'";
}

$sql .= " ORDER BY n.created_at DESC";

$result = mysqli_query($conn, $sql);
$notices = [];

while ($row = mysqli_fetch_assoc($result)) {
    $notices[] = $row;
}

// Return the data as JSON so ajax.js can read it
header('Content-Type: application/json');
echo json_encode($notices);
?>