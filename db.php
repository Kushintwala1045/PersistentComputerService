<?php
$host = 'sql208.infinityfree.com'; // Replace with your database host
$dbname = 'if0_37997732_complaints_db'; // Replace with your database name
$username = 'if0_37997732'; // Replace with your database username
$password = '5bs39z5fNHeupBL '; // Replace with your database password

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
