<?php
// Conexión usando variables de entorno de Railway
$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$db   = getenv('MYSQLDATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["ok" => false, "msg" => "DB error: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");
