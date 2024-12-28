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
function getExperts() {
    global $conn;
    $sql = "SELECT id, name FROM experts";
    $result = $conn->query($sql);

    $experts = [];
    while ($row = $result->fetch_assoc()) {
        $experts[] = $row;
    }

    return $experts;
}

// Fetch all tickets (including expert name)
function getTickets() {
    global $conn;
    $sql = "SELECT complaints.*, experts.name AS expert_name
            FROM complaints
            LEFT JOIN experts ON complaints.expert_id = experts.id";
    $result = $conn->query($sql);

    $tickets = [];
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }

    return $tickets;
}

// Check the request method to determine if it's a GET or POST request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch experts for the assignment dropdown
    if (isset($_GET['experts'])) {
        $experts = getExperts();
        echo json_encode($experts); // Return all experts as a JSON response
    } elseif (isset($_GET['ticket_id'])) {
        // Fetch a specific ticket by ID
        $ticket_id = $_GET['ticket_id'];
        $sql = "SELECT * FROM complaints WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $ticket = $result->fetch_assoc();
            echo json_encode($ticket); // Return the ticket details
        } else {
            echo json_encode(['error' => 'Ticket not found']);
        }

        $stmt->close();
    } else {
        // Fetch all tickets
        $tickets = getTickets();
        if (count($tickets) > 0) {
            echo json_encode($tickets); // Return all tickets with expert names as a JSON response
        } else {
            echo json_encode(['error' => 'No tickets found']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ticket_id']) && isset($_POST['status'])) {
        // Update ticket status
        $ticket_id = $_POST['ticket_id'];
        $status = $_POST['status'];

        // Update ticket status in the database
        $sql = "UPDATE complaints SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $ticket_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating status']);
        }

        $stmt->close();
    } elseif (isset($_POST['ticket_id']) && isset($_POST['expert_id'])) {
        // Assign expert to the ticket
        $ticket_id = $_POST['ticket_id'];
        $expert_id = $_POST['expert_id'];

        $sql = "UPDATE complaints SET expert_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $expert_id, $ticket_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assigning expert']);
        }

        $stmt->close();
    }
}

$conn->close();
?>
