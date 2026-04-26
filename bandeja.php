<?php
header("Content-Type: application/json");
require "db.php";

$para = trim($_GET['para'] ?? '');
if (!$para) die(json_encode(["ok" => false, "msg" => "Falta para"]));

// Trae el último mensaje recibido de cada remitente
$stmt = $conn->prepare(
    "SELECT de, mensaje AS ultimo_mensaje, MAX(fecha) AS fecha
     FROM mensajes
     WHERE para = ?
     GROUP BY de
     ORDER BY fecha DESC"
);
$stmt->bind_param("s", $para);
$stmt->execute();
$result = $stmt->get_result();

$lista = [];
while ($row = $result->fetch_assoc()) {
    $lista[] = [
        "de"             => $row['de'],
        "ultimo_mensaje" => $row['ultimo_mensaje'],
        "fecha"          => $row['fecha']
    ];
}

echo json_encode($lista);
