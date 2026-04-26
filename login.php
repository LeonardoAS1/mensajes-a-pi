<?php
header("Content-Type: application/json");
require "db.php";

$usuario  = trim($_GET['usuario']  ?? '');
$password = trim($_GET['password'] ?? '');

if (!$usuario || !$password) {
    die(json_encode(["ok" => false, "msg" => "Faltan datos"]));
}

$stmt = $conn->prepare("SELECT password FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password'])) {
        echo json_encode(["ok" => true]);
    } else {
        echo json_encode(["ok" => false, "msg" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["ok" => false, "msg" => "Usuario no encontrado"]);
}
