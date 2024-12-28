<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'complaints_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Retrieve expert_id from the query string
$expert_id = isset($_GET['expert_id']) ? intval($_GET['expert_id']) : 0;

// Validate the expert_id
if ($expert_id < 1 || $expert_id > 7) {
    die("Invalid expert ID. Please log in again.");
}

// Fetch expert details (Replace with database logic)
$expert_name = "Expert " . $expert_id; // Example logic for name assignment

// Fetch tasks assigned to the expert
$sql = "SELECT ticket_number, customer_name, complaint_description, priority, category, created_at, status
        FROM complaints
        WHERE expert_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $expert_id);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white-100 min-h-screen">
<div class="relative">
        <!-- Logo -->
        <img src="computer service.jpg" id="logo" alt="Logo" class="h-20 m-5 block mx-auto sm:max-w-xs md:max-w-md lg:max-w-lg" />
    
        <!-- Top-right section -->
        <div id="top-right" class="absolute top-5 right-5 flex items-center justify-end gap-3">
            <!-- Welcome Text -->
            <span class="text-gray-700 font-medium"></span>
    
            <!-- Logout Button -->
            <button onclick="logout()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-red-600">
                Logout
            </button>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto text-center">
            <h1 class="text-2xl font-bold">Expert Dashboard</h1>
            <p>Welcome, <?= htmlspecialchars($expert_name) ?>!</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto py-6">
        <div class="bg-white shadow rounded p-6">
            <h2 class="text-xl font-bold mb-4">Assigned Tasks</h2>

            <?php if (count($tasks) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border px-4 py-2 text-left">#</th>
                                <th class="border px-4 py-2 text-left">Customer Name</th>
                                <th class="border px-4 py-2 text-left">Description</th>
                                <th class="border px-4 py-2 text-left">Priority</th>
                                <th class="border px-4 py-2 text-left">Category</th>
                                <th class="border px-4 py-2 text-left">Status</th>
                                <th class="border px-4 py-2 text-left">Date Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $index => $task): ?>
                                <tr class="<?= $index % 2 === 0 ? 'bg-gray-100' : 'bg-white' ?>">
                                    <td class="border px-4 py-2"><?= $index + 1 ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['customer_name']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['complaint_description']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['priority']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['category']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['status']) ?></td>
                                    <td class="border px-4 py-2"><?= htmlspecialchars($task['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No tasks assigned to this expert.</p>
            <?php endif; ?>
        </div>
    </main>
<script>
    function logout() {
            // Redirect to the logout page or handle logout logic
            window.location.href = 'logout.php';
        }
</script>
</body>
</html>
