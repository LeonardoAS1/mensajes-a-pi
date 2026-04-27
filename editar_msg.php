<?php
header("Content-Type: application/json");
require "db.php";

$id      = trim($_GET['id']      ?? '');
$de      = trim($_GET['de']      ?? '');
$mensaje = trim($_GET['mensaje'] ?? '');

if (!$id || !$de || !$mensaje) die(json_encode(["ok"=>false,"msg"=>"Faltan datos"]));

// Solo el remitente puede editar su propio mensaje
$stmt = $conn->prepare("UPDATE mensajes SET mensaje=? WHERE id=? AND de=?");
$stmt->bind_param("sis", $mensaje, $id, $de);

echo $stmt->execute() && $stmt->affected_rows > 0
    ? json_encode(["ok" => true])
    : json_encode(["ok" => false, "msg" => "No autorizado o no encontrado"]);
