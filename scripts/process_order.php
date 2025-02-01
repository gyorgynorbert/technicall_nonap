<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to send email
function sendOrderEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'norbert200476@gmail.com';
        $mail->Password   = 'gojd eoho wdam ctqa';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('norbert200476@gmail.com', 'György Norbert');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Error: " . $mail->ErrorInfo;
    }
}

// Validate student_id
if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
    die("Error: student_id is missing or invalid.");
}
$student_id = $_POST['student_id'];

// Fetch student info
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Error: No student found with this ID.");
}
$student = $result->fetch_assoc();

// Process individual products
$individual_orders = [];
if (isset($_POST['individual']) && is_array($_POST['individual'])) {
    foreach ($_POST['individual'] as $product_code => $details) {
        if ($details['quantity'] > 0) {
            $individual_orders[] = [
                'product_code' => $product_code,
                'photo' => $details['photo'],
                'quantity' => $details['quantity']
            ];
        }
    }
}

// Process packages
$package_orders = [];
if (isset($_POST['packages']) && is_array($_POST['packages'])) {
    foreach ($_POST['packages'] as $package_id => $details) {
        if ($details['quantity'] > 0) {
            $package_orders[] = [
                'package_id' => $package_id,
                'components' => $details,
                'quantity' => $details['quantity']
            ];
        }
    }
}

// Prepare email content
$email_subject = "New Order for Student: " . htmlspecialchars($student['name']);
$email_message = "<h1>Order Details</h1>";
$email_message .= "<h2>Student: " . htmlspecialchars($student['name']) . "</h2>";

if (!empty($individual_orders)) {
    $email_message .= "<h3>Individual Products:</h3>";
    foreach ($individual_orders as $order) {
        $email_message .= "<p>Product Code: " . htmlspecialchars($order['product_code']) . "</p>";
        $email_message .= "<p>Photo: " . htmlspecialchars($order['photo']) . "</p>";
        $email_message .= "<p>Quantity: " . htmlspecialchars($order['quantity']) . "</p>";
        $email_message .= "<hr>";
    }
}

if (!empty($package_orders)) {
    $email_message .= "<h3>Packages:</h3>";
    foreach ($package_orders as $order) {
        $email_message .= "<p>Package ID: " . htmlspecialchars($order['package_id']) . "</p>";
        foreach ($order['components'] as $component_code => $component_details) {
            if ($component_code !== 'quantity') {
                $email_message .= "<p>Component: " . htmlspecialchars($component_code) . "</p>";
                $email_message .= "<p>Photo: " . htmlspecialchars($component_details['photo']) . "</p>";
            }
        }
        $email_message .= "<p>Quantity: " . htmlspecialchars($order['quantity']) . "</p>";
        $email_message .= "<hr>";
    }
}

// Send email
$to_email = "redditelteta@gmail.com"; // Replace with the recipient's email
$email_result = sendOrderEmail($to_email, $email_subject, $email_message);

if ($email_result === true) {
    echo "Order processed successfully! An email has been sent.";
} else {
    echo "Order processed, but there was an error sending the email: " . $email_result;
}
?>