<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] .'/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOrderEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elodkee91@gmail.com';
        $mail->Password   = 'ndnr fkxe jjmc lngo';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('elodkee91@gmail.com', 'Koncsag Elod');
        $mail->addAddress($to);
        $mail->Subject = $subject;

        $mail->CharSet = 'UTF-8';

        $mail->isHTML(true);
        $mail->Body    = $message;



        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Hiba: " . $mail->ErrorInfo;
    }
}


if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
    die("Hiba: diak_id hiányzik vagy invalid.");
}
$student_id = $_POST['student_id'];


$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Hiba: Ilyen diák nem létezik!");
}
$student = $result->fetch_assoc();

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

$package_orders = [];
if (isset($_POST['packages']) && is_array($_POST['packages'])) {
    foreach ($_POST['packages'] as $package_id => $details) {
        if ($details['quantity'] > 0) {
            $package_orders[] = [
                'package_id' => $package_id,
                'description' => isset($details['description']) ? $details['description'] : "Nincs elérhető leírás",
                'components' => $details,
                'quantity' => $details['quantity']
            ];
        }
    }
}

$email_subject = "Új rendelés a következő diáknak: " . htmlspecialchars($student['name']);
$email_message = "<h1>Rendelés részletei</h1>";
$email_message .= "<h2>Diák: " . htmlspecialchars($student['name']) . "</h2>";

if (!empty($individual_orders)) {
    $email_message .= "<h3>Különálló termékek:</h3>";
    foreach ($individual_orders as $order) {
        $email_message .= "<p>Termék kód: " . htmlspecialchars($order['product_code']) . "</p>";
        $email_message .= "<p>Választott kép: " . htmlspecialchars($order['photo']) . "</p>";
        $email_message .= "<p>Mennyiség: " . htmlspecialchars($order['quantity']) . "</p>";
        $email_message .= "<hr>";
    }
}

if (!empty($package_orders)) {
    $email_message .= "<h3>Csomagok:</h3>";
    foreach ($package_orders as $order) {
        $email_message .= "<p><strong>Csomag ID:</strong> " . htmlspecialchars($order['package_id']) . "</p>";
        $email_message .= "<p><strong>Leírás:</strong> " . htmlspecialchars($order['description']) . "</p>";

        $email_message .= "<h4>Komponensek:</h4>";
        $email_message .= "<ul>";
        foreach ($order['components'] as $component_code => $component_details) {
            if ($component_code !== 'quantity' && $component_code !== 'description') {
                $email_message .= "<li><strong>Termék:</strong> " . htmlspecialchars($component_code) . "<br>";
                $email_message .= "<strong>Választott kép:</strong> " . htmlspecialchars($component_details['photo']) . "</li>";
            }
        }
        $email_message .= "</ul>";
        $email_message .= "<p><strong>Mennyiség (hány darab csomag):</strong> " . htmlspecialchars($order['quantity']) . "</p>";
        $email_message .= "<hr>";
    }
}

$to_email = "norbert200476@gmail.com";
$email_result = sendOrderEmail($to_email, $email_subject, $email_message);

if ($email_result === true) {
    echo "Sikeres rendelés. További rendelésekért térjen vissza az előző lapra!";
} else {
    echo "Rendelés feldolgozva, de hiba történt a rendelés során: " . $email_result;
    echo "Kérem írjon egy email-t a következő email címre: technicallprint@gmail.com";
}
?>