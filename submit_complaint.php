<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Database connection
$host = 'localhost'; // Replace with your database host
$dbname = 'complaints_db'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required fields are present in the request
    if (isset($_POST['email'], $_POST['name'], $_POST['description'], $_POST['priority'], $_POST['category'], $_POST['phone_number'])) {
        
        // Sanitize and validate inputs
        $customerEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $customerName = htmlspecialchars($_POST['name']);
        $complaintDescription = htmlspecialchars($_POST['description']);
        $priority = htmlspecialchars($_POST['priority']);
        $category = htmlspecialchars($_POST['category']);
        $phoneNumber = htmlspecialchars($_POST['phone_number']);

        // Validate email
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400); // Bad Request
            echo 'Error: Invalid email address.';
            exit;
        }

        // Generate ticket number
        $ticketNumber = 'PCS' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Insert complaint into database
        try {
            $sql = "INSERT INTO complaints (ticket_number, customer_email, customer_name, complaint_description, priority, category, phone_number) 
                    VALUES (:ticket_number, :customer_email, :customer_name, :complaint_description, :priority, :category, :phone_number)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':ticket_number' => $ticketNumber,
                ':customer_email' => $customerEmail,
                ':customer_name' => $customerName,
                ':complaint_description' => $complaintDescription,
                ':priority' => $priority,
                ':category' => $category,
                ':phone_number' => $phoneNumber
            ]);
        } catch (PDOException $e) {
            http_response_code(500); // Server error
            echo 'Error: ' . $e->getMessage();
            exit;
        }

        // Owner details
        $ownerEmail = 'kushintwala05@gmail.com'; // Replace with owner's email
        $ownerName = 'Animesh Mistry'; // Replace with owner's name

        // Your Gmail credentials
        $gmailUsername = 'pcs122003@gmail.com'; // Replace with your Gmail address
        $gmailAppPassword = 'gbmi slny cacm vacs'; // Replace with your Gmail App Password

        // Send email notification
        try {
            $mail = new PHPMailer(true);

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $gmailUsername;
            $mail->Password = $gmailAppPassword;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email to Customer
            $mail->setFrom($gmailUsername, 'Complaint System');
            $mail->addAddress($customerEmail, $customerName);
            $mail->Subject = 'Your Complaint Received';
            $mail->Body = "Dear $customerName,\n\nYour complaint has been registered.\n\nTicket Number: $ticketNumber\nCategories: $category\nDescription: $complaintDescription\nPriority: $priority\n\nWe will get back to you shortly.\n\nRegards,\nPersistent Computer Service";
            $mail->send();

            // Email to Owner
            $mail->clearAddresses();
            $mail->addAddress($ownerEmail, $ownerName);
            $mail->Subject = 'New Complaint Submitted';
            $mail->Body = "Dear $ownerName,\n\nA new complaint has been submitted:\n\nTicket Number: $ticketNumber\nCustomer: $customerName\nEmail: $customerEmail\nCategories: $category\nDescription: $complaintDescription\nPriority: $priority\n\nPlease address this complaint promptly.\n\nRegards,\nPersistent Computer Service";
            $mail->send();

            // Return ticket number as plain text
            echo $ticketNumber;
        } catch (Exception $e) {
            http_response_code(500); // Server error
            echo 'Error: ' . $mail->ErrorInfo;
        }
    } else {
        http_response_code(400); // Bad request
        echo 'Error: Missing required fields.';
    }
} else {
    http_response_code(405); // Method not allowed
    echo 'Error: Invalid request method.';
}
?>
