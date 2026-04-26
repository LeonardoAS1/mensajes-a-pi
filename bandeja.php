<?php
header("Content-Type: application/json");
require "db.php";

$para = trim($_GET['para'] ?? '');
if (!$para) die(json_encode(["ok" => false, "msg" => "Falta para"]));

$stmt = $conn->prepare(
    "SELECT m.de, m.mensaje AS ultimo_mensaje, m.fecha
     FROM mensajes m
     INNER JOIN (
         SELECT de, MAX(fecha) AS max_fecha
         FROM mensajes
         WHERE para = ?
         GROUP BY de
     ) sub ON m.de = sub.de AND m.fecha = sub.max_fecha AND m.para = ?
     ORDER BY m.fecha DESC"
);
$stmt->bind_param("ss", $para, $para);
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
