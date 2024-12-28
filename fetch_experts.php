<?php
// Database connection
$host = 'sql208.infinityfree.com'; // Replace with your database host
$dbname = 'if0_37997732_complaints_db'; // Replace with your database name
$username = 'if0_37997732'; // Replace with your database username
$password = '5bs39z5fNHeupBL '; // Replace with your database password

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
