<?php
header("Content-Type: application/json");
require "db.php";

$de      = trim($_GET['de']      ?? '');
$para    = trim($_GET['para']    ?? '');
$mensaje = trim($_GET['mensaje'] ?? '');

if (!$de || !$para || !$mensaje) {
    die(json_encode(["ok" => false, "msg" => "Faltan datos"]));
}

$stmt = $conn->prepare("INSERT INTO mensajes (de, para, mensaje) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $de, $para, $mensaje);

if ($stmt->execute()) {
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "msg" => "Error al guardar"]);
}
