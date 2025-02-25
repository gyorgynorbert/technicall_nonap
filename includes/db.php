<?php
    require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

    use Dotenv\Dotenv;

    $dotenv = Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
    $dotenv->load();

    $servername = $_ENV['DB_HOST'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];
    $db_name = $_ENV['DB_NAME'];
    $port = $_ENV['DB_PORT'];
    $socket = $_ENV['DB_SOCKET'];

    $conn = new mysqli($servername, $username, $password, $db_name, $port, $socket ?: null);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
