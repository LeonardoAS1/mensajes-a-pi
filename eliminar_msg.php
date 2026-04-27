<?php
header("Content-Type: application/json");
require "db.php";

$id = trim($_GET['id'] ?? '');
$de = trim($_GET['de'] ?? '');

if (!$id || !$de) die(json_encode(["ok"=>false,"msg"=>"Faltan datos"]));

// Solo el remitente puede eliminar su propio mensaje
$stmt = $conn->prepare("DELETE FROM mensajes WHERE id=? AND de=?");
$stmt->bind_param("is", $id, $de);

echo $stmt->execute() && $stmt->affected_rows > 0
    ? json_encode(["ok" => true])
    : json_encode(["ok" => false, "msg" => "No autorizado o no encontrado"]);
