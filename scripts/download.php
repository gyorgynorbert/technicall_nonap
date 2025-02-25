<?php 
    ob_start();
    if (isset($_GET['file'])) {
        $file = $_GET['file'];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        $filename = basename($file);
        $filepath = $_SERVER['DOCUMENT_ROOT'] . $file; 
        
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); 
        if (file_exists($filepath) && in_array($file_ext, $allowed_extensions)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragna: public');
            header('Content-Length: ' . filesize($filepath));

            readfile($filepath);
            exit;
        } else {
            die("Hiba: A fájl nem található vagy érvénytelen típusú");
        }
    } else {
        die("Érvénytelen kérés!");
    }
?>