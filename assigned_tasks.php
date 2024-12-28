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

// Fetching all experts and their tasks using a JOIN
function fetchExpertsWithTasks() {
    global $conn;
    $sql = "
        SELECT 
            e.id AS expert_id, 
            e.name AS expert_name, 
            c.ticket_number, 
            c.complaint_description 
        FROM 
            experts e
        LEFT JOIN 
            complaints c ON e.id = c.expert_id
        ORDER BY e.name;
    ";

    $result = $conn->query($sql);

    $expertsWithTasks = [];
    while ($row = $result->fetch_assoc()) {
        $expertId = $row['expert_id'];
        
        // Group tasks by expert
        if (!isset($expertsWithTasks[$expertId])) {
            $expertsWithTasks[$expertId] = [
                'name' => $row['expert_name'],
                'tasks' => []
            ];
        }
        
        if (!empty($row['ticket_number']) && !empty($row['complaint_description'])) {
            $task = "Ticket : - " . $row['ticket_number'] . " : " . $row['complaint_description'];
            $expertsWithTasks[$expertId]['tasks'][] = $task;
        }
        
    }

    return array_values($expertsWithTasks);
}

// Handle AJAX requests
if (isset($_GET['fetch_tasks'])) {
    $expertsWithTasks = fetchExpertsWithTasks();
    echo json_encode($expertsWithTasks);
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experts and Complaints Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white-100 p-8">
<div class="relative">
        <!-- Logo -->
        <img src="computer service.jpg" id="logo" alt="Logo" class="h-20 m-5 block" />
    
        <!-- Top-right section -->
        <div id="top-right" class="absolute top-5 right-5 flex items-center justify-end gap-3">
            <!-- Welcome Text -->
            <span class="text-gray-700 font-medium">Welcome, Admin</span>
    
            <button onclick="goToHome()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
    Back
</button>

<script>
function goToHome() {
    window.location.href = 'dashboard.html'; // Replace 'index.php' with your desired page URL
}
</script>

        </div>
    </div>

    <!-- Header -->
    <header class="mb-8 text-center">
        <h1 class="text-3xl font-bold">Experts and Complaints Table</h1>
    </header>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table id="experts-table" class="table-auto w-full border-collapse border border-gray-300">
            <!-- Table Content will be inserted dynamically -->
        </table>
    </div>

    <script>
        // Fetch data from the PHP backend and render into a table
        function fetchExpertsData() {
            fetch('?fetch_tasks=1', { method: 'GET' })
                .then(response => response.json())
                .then(experts => {
                    renderTable(experts);
                })
                .catch(error => console.error('Error fetching experts and tasks:', error));
        }

        // Render the table with expert names as columns and complaints as rows
        function renderTable(experts) {
            const table = document.getElementById('experts-table');

            // Generate table headers
            let headerRow = '<tr class="bg-gray-200 text-gray-700">';
            experts.forEach(expert => {
                headerRow += `<th class="border p-2">${expert.name}</th>`;
            });
            headerRow += '</tr>';

            // Find the max number of tasks to determine table rows
            const maxTasks = Math.max(...experts.map(expert => expert.tasks.length));

            // Generate table rows
            let tableRows = '';
            for (let i = 0; i < maxTasks; i++) {
                tableRows += '<tr>';
                experts.forEach(expert => {
                    const task = expert.tasks[i] || ''; // Display empty cell if no task
                    tableRows += `<td class="border p-2 ">${task}</td>`;
                });
                tableRows += '</tr>';
            }

            // Combine headers and rows into the table
            table.innerHTML = headerRow + tableRows;
        }

        // Fetch data on page load
        fetchExpertsData();
    </script>

</body>
</html>
