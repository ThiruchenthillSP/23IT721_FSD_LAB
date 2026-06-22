<?php
// Database connection parameters
$host = "localhost";
$user = "root";
$pass = "";
$db = "sneakerdb";

// Establish connection
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Print status ONLY if the script is run directly (not included)
if (basename($_SERVER['PHP_SELF']) == 'dbconnect.php') {
    echo "Database Connected";
}
?>
