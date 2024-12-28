<?php
// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'complaints_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all experts from the database
$sql = "SELECT id, name FROM experts";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $experts = [];
    while ($row = $result->fetch_assoc()) {
        $experts[] = $row;
    }

    // Return the experts as a JSON response
    echo json_encode($experts);
} else {
    echo json_encode(['error' => 'No experts found']);
}

$conn->close();
?>
