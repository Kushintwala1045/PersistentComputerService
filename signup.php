<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer autoload (If using Composer)

session_start(); // Start session to temporarily store user details
$error = ""; // Initialize error message variable

// Step 1: User enters details and receives OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['otp'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $company_name = $_POST['company_name'];
    $phone_no = $_POST['phone_no'];

    // Check if email already exists
    $check_email_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_email_result = $conn->query($check_email_sql);

    if ($check_email_result->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        // Generate OTP
        $otp = rand(1000, 9999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+1 minutes")); // OTP expiry time

        // Temporarily store user data and OTP in session
        $_SESSION['user_data'] = compact('name', 'email', 'password', 'company_name', 'phone_no', 'otp', 'otp_expiry');

        // Send OTP to the user's email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'pcs122003@gmail.com';  // Your Gmail address
            $mail->Password = 'gbmi slny cacm vacs';  // Your Gmail password or app-specific password

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('pcs122003@gmail.com', 'Animesh Mistry');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification OTP';
            $mail->Body    = "Your OTP for email verification is: $otp. This OTP will expire in 15 minutes.";

            // Send the email
            $mail->send();
            header("Location: signup.php?verify=1");
            exit;
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

// Step 2: OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $entered_otp = $_POST['otp'];

    // Check OTP and expiry
    if (isset($_SESSION['user_data'])) {
        $stored_otp = $_SESSION['user_data']['otp'];
        $otp_expiry = $_SESSION['user_data']['otp_expiry'];

        if ($entered_otp == $stored_otp && strtotime($otp_expiry) > time()) {
            // OTP is correct, insert user data into the database
            $user_data = $_SESSION['user_data'];
            $sql = "INSERT INTO users (name, email, password, role, company_name, phone_no, status) 
                    VALUES ('{$user_data['name']}', '{$user_data['email']}', '{$user_data['password']}', 'user', 
                    '{$user_data['company_name']}', '{$user_data['phone_no']}', 'active')";
            if ($conn->query($sql) === TRUE) {
                unset($_SESSION['user_data']); // Clear session data
                header("Location: index.html");
                exit;
            } else {
                $error = "Error: " . $conn->error;
            }
        } else {
            $error = "Invalid or expired OTP.";
        }
    } else {
        $error = "Session expired. Please sign up again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="style2.css">
    <script>
        // JavaScript function to show popup
        function showPopup(message) {
            alert(message);
        }
    </script>
</head>
<body>
    <img src="computer service.jpg" id="logo" alt="Logo">

    <?php if (isset($_GET['verify'])): ?>
        <!-- OTP Verification Form -->
        <form action="signup.php" method="POST">
            <h2>Enter OTP</h2>
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify OTP</button>
        </form>
    <?php else: ?>
        <!-- Signup Form -->
        <form action="signup.php" method="POST">
            <h2>Signup</h2>
            <?php if (!empty($error)) echo "<script>showPopup('$error');</script>"; ?>
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="company_name" placeholder="Company Name">
            <input type="tel" name="phone_no" placeholder="Phone Number">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Signup</button>
        </form>
    <?php endif; ?>
</body>
</html>
