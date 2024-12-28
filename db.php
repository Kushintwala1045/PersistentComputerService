<?php
$servername = "localhost";
$username = "root"; 
$password = "";     // Use your MySQL password
$dbname = "complaints_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
