<?php
require 'includes/db.php';

// Function to send email
function sendOrderEmail($to, $subject, $message) {
    $headers = "From: redditelteta@gmail.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $individual_products = $_POST['individual'] ?? [];
    $packages = $_POST['packages'] ?? [];

    // Fetch student info
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // Prepare email content
    $email_content = "<h2>Order for Student: " . htmlspecialchars($student['name']) . "</h2>";

    // Process individual products
    $email_content .= "<h3>Individual Products:</h3>";
    $has_individual = false;
    foreach ($individual_products as $code => $details) {
        if ($details['quantity'] > 0) {
            $has_individual = true;
            $email_content .= "<p><strong>" . htmlspecialchars($details['photo']) . "</strong><br>";
            $email_content .= "Product Code: $code<br>";
            $email_content .= "Quantity: " . $details['quantity'] . "</p>";
        }
    }
    if (!$has_individual) {
        $email_content .= "<p>No individual products ordered.</p>";
    }

    // Process packages
    $email_content .= "<h3>Packages:</h3>";
    $has_packages = false;
    foreach ($packages as $package_id => $details) {
        if ($details['quantity'] > 0) {
            $has_packages = true;
            $email_content .= "<p><strong>Package ID: $package_id</strong><br>";
            $email_content .= "Quantity: " . $details['quantity'] . "<br>";
            $email_content .= "Components:<ul>";
            foreach ($details as $code => $component) {
                if ($code !== 'quantity') {
                    $email_content .= "<li>" . htmlspecialchars($component['photo']) . " ($code)</li>";
                }
            }
            $email_content .= "</ul></p>";
        }
    }
    if (!$has_packages) {
        $email_content .= "<p>No packages ordered.</p>";
    }

    // Send email
    $to = "norbert200476@gmail.com";
    $subject = "New Order for Student: " . htmlspecialchars($student['name']);
    if (sendOrderEmail($to, $subject, $email_content)) {
        echo "Order submitted successfully!";
    } else {
        echo "Error sending order email.";
    }
} else {
    header("Location: order_form.php");
    exit();
}