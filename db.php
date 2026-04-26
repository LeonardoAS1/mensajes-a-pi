<?php
// ── VARIABLES DE RAILWAY ────────────────────────────────
// Ve a Railway → tu servicio MySQL → pestaña Variables
// Copia cada valor y pégalo aquí entre las comillas
// ────────────────────────────────────────────────────────

$host = getenv('MYSQLHOST')     ?: 'PEGA_AQUI_MYSQLHOST';
$port = getenv('MYSQLPORT')     ?: '3306';
$db   = getenv('MYSQLDATABASE') ?: 'PEGA_AQUI_MYSQLDATABASE';
$user = getenv('MYSQLUSER')     ?: 'PEGA_AQUI_MYSQLUSER';
$pass = getenv('MYSQLPASSWORD') ?: 'PEGA_AQUI_MYSQLPASSWORD';

// ────────────────────────────────────────────────────────
$conn = new mysqli($host, $user, $pass, $db, (int)$port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["ok" => false, "msg" => $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
