<?php
header("Content-Type: application/json");
require "db.php";

$de   = trim($_GET['de']   ?? '');
$para = trim($_GET['para'] ?? '');

if (!$de || !$para) {
    die(json_encode(["ok" => false, "msg" => "Faltan datos"]));
}

// Trae mensajes en AMBAS direcciones entre los dos usuarios
$stmt = $conn->prepare(
    "SELECT de, para, mensaje, fecha FROM mensajes
     WHERE (de = ? AND para = ?) OR (de = ? AND para = ?)
     ORDER BY fecha ASC"
);
$stmt->bind_param("ssss", $de, $para, $para, $de);
$stmt->execute();
$result = $stmt->get_result();

$lista = [];
while ($row = $result->fetch_assoc()) {
    $lista[] = [
        "de"      => $row['de'],
        "para"    => $row['para'],
        "mensaje" => $row['mensaje'],
        "fecha"   => $row['fecha']
    ];
}

echo json_encode($lista);
