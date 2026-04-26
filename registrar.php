<?php
header("Content-Type: application/json");
require "db.php";

$usuario  = trim($_GET['usuario']  ?? '');
$password = trim($_GET['password'] ?? '');

if (!$usuario || !$password) die(json_encode(["ok"=>false,"msg"=>"Faltan datos"]));

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO usuarios (usuario, password) VALUES (?,?)");
$stmt->bind_param("ss", $usuario, $hash);

echo $stmt->execute()
    ? json_encode(["ok" => true])
    : json_encode(["ok" => false, "msg" => "Usuario ya existe"]);
