<?php
// assign_expert.php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'complaints_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ticket_id']) && isset($_POST['expert_id'])) {
        $ticket_id = $_POST['ticket_id'];
        $expert_id = $_POST['expert_id'];

        // Update the ticket with the assigned expert
        $sql = "UPDATE complaints SET expert_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $expert_id, $ticket_id);

        if ($stmt->execute()) {
            // Fetch the expert's name based on expert_id
            $expert_sql = "SELECT name FROM experts WHERE id = ?";
            $expert_stmt = $conn->prepare($expert_sql);
            $expert_stmt->bind_param("i", $expert_id);
            $expert_stmt->execute();
            $expert_stmt->bind_result($expert_name);
            $expert_stmt->fetch();
            $expert_stmt->close();

            // Return the success response with expert's name
            echo json_encode(['success' => true, 'expert_name' => $expert_name]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assigning expert']);
        }

        $stmt->close();
    }
}

$conn->close();
?>
