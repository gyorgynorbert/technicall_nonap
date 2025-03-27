<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/phpmailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/phpmailer/phpmailer/src/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
$dotenv->load();

function sendOrderEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('example@gmail.com', 'John Doe');
        $mail->addAddress($to);
        $mail->Subject = htmlspecialchars($subject);

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

if (!isset($_POST['phone_number']) || !preg_match('/^0\d{9}$/', $_POST['phone_number'])) {
    die("Hiba: Érvénytelen telefonszám! Adj meg egy 10 számjegyű román telefonszámot.");
}

$student_id = $_POST['student_id']; 
$phone_number = $_POST['phone_number'];

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
        if (isset($details['photo']) && is_array($details['photo']) && isset($details['quantity']) && is_array($details['quantity'])) {
            foreach ($details['photo'] as $index => $photo_path) {
                if (!empty($details['quantity'][$index]) && is_numeric($details['quantity'][$index])) {
                    $individual_orders[] = [
                        'product_code' => $product_code,
                        'photo' => $photo_path,
                        'quantity' => (int) $details['quantity'][$index]
                    ];
                }
            }
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

if (empty($individual_orders) && empty($package_orders)) {
    die("Hiba: Legalább egy terméket vagy csomagot ki kell választani!");
}

$email_subject = "Új rendelés a következő diáknak: " . htmlspecialchars($student['name']);
$email_message = "<h1 style='color: #2c3e50; margin-bottom: 5px;'>Rendelés részletei</h1>";
$email_message .= "<p style='font-size:18px;'><strong>Diák:</strong> " . htmlspecialchars($student['name']) . "</p>";
$email_message .= "<p style='font-size:18px;'><strong>Telefonszám:</strong> " . htmlspecialchars($phone_number) . "</p>";

if (!empty($individual_orders)) {
    $email_message .= "<h3 style='font-size: 20px; color: #333; margin-bottom: 10px;'>Különálló termékek:</h3>";
    foreach ($individual_orders as $order) {
        $email_message .= "<div style='border: 1px solid #ccc; border-radius: 8px; padding: 10px; margin-bottom: 10px;'>";
        $email_message .= "<p><strong>Termék kód:</strong> " . htmlspecialchars($order['product_code']) . "</p>";
        $email_message .= "<p><strong>Választott kép:</strong> " . basename($order['photo']) . "</p>";
        $email_message .= "<p><strong>Mennyiség:</strong> " . htmlspecialchars($order['quantity']) . "</p>";
        $email_message .= "</div>";
    }
}


if (!empty($package_orders)) {
    $email_message .= "<h3 style='font-size: 20px; color: #333; margin-bottom: 10px;'>Csomagok:</h3>";
    foreach ($package_orders as $order) {
        $email_message .= "<div style='border: 1px solid #ccc; border-radius: 8px; padding: 10px; margin-bottom: 10px;'>"; // Adjusted padding and removed background color
        $email_message .= "<p><strong>Csomag ID:</strong> " . $order['package_id'] . "</p>";
        $email_message .= "<p><strong>Leírás:</strong> " . $order['description'] . "</p>";
        
        // Removed padding and margin from Komponensek list for closer items
        $email_message .= "<h4 style='font-size: 18px; color: #333; margin-bottom: 5px;'>Komponensek:</h4><ul style='margin-left: 0; padding-left: 0; list-style-type: none;'>";
        foreach ($order['components'] as $component_code => $component_details) {
            if ($component_code !== 'quantity' && $component_code !== 'description') {
                $email_message .= "<li style='margin-bottom: 5px; padding: 0;'>"; // Removed extra margin and padding
                $email_message .= "<strong>Termék:</strong> " . htmlspecialchars($component_code) . "<br>";
                $email_message .= "<strong>Választott kép:</strong> " . htmlspecialchars(basename($component_details['photo'])) . "</li>";
            }
        }
        $email_message .= "</ul>";
        $email_message .= "<p><strong>Mennyiség:</strong> " . $order['quantity'] . "</p>";
        $email_message .= "</div>";
    }
}

$to_email = "example@gmail.com";
$email_result = sendOrderEmail($to_email, $email_subject, $email_message);

if ($email_result === true) {
    echo "Sikeres rendelés. További rendelésekért térjen vissza az előző lapra!";
} else {
    echo "Rendelés feldolgozva, de hiba történt a rendelés során: " . $email_result;
    echo "Kérem írjon egy email-t a következő email címre: technicallprint@gmail.com";
}
?>
