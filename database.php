<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "cnbs_db";

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for security and special characters
mysqli_set_charset($conn, "utf8mb4");
?>