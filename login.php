<?php
header("Content-Type: application/json");
require "db.php";

$usuario  = trim($_GET['usuario']  ?? '');
$password = trim($_GET['password'] ?? '');

if (!$usuario || !$password) die(json_encode(["ok"=>false,"msg"=>"Faltan datos"]));

$stmt = $conn->prepare("SELECT password FROM usuarios WHERE usuario=?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row && password_verify($password, $row['password'])) {
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "msg" => "Credenciales incorrectas"]);
}
