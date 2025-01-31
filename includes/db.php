<?php 
    $servername = 'localhost';
    $username = 'root';
    $password = 'rootpassword';
    $db_name = 'nonap_db';
    $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
    $port = 3306;

    $conn = new mysqli($servername, $username, $password, $db_name, $port, $socket);

    if ($conn -> connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>