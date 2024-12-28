<?php
session_start();
include 'db.php';

// Initialize the error message
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check credentials
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;

        // Check if the user is an expert and get their expert_id
        $expert_id = $user['expert_id'];

        // Check if the user is an admin (Assuming 'role' is a column in the users table)
        $role = $user['role']; // For example: 'admin' or 'user'

        // Redirect based on role
        if ($role === 'admin') {
            header('Location: dashboard.html');
        } elseif ($expert_id > 0) {
            header('Location: dashboard.php?expert_id=' . $expert_id);
        } else {
            header('Location: complaint.html');
        }
        exit;
    } else {
        // Pass the error message via query parameter
        header('Location: login.php?error=Invalid credentials.');
        exit;
    }
}
?>