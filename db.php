<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'jersey_shop';
$port = (int) (getenv('DB_PORT') ?: 3306);

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection is unavailable. Please try again later.');
}

$conn->set_charset('utf8mb4');
?>
