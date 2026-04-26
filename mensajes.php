<?php
header("Content-Type: application/json");
require "db.php";

$de   = trim($_GET['de']   ?? '');
$para = trim($_GET['para'] ?? '');

if (!$de || !$para) die(json_encode(["ok"=>false,"msg"=>"Faltan datos"]));

$stmt = $conn->prepare(
    "SELECT de, mensaje FROM mensajes
     WHERE (de=? AND para=?) OR (de=? AND para=?)
     ORDER BY fecha ASC"
);
$stmt->bind_param("ssss", $de, $para, $para, $de);
$stmt->execute();
$result = $stmt->get_result();

$lista = [];
while ($row = $result->fetch_assoc()) $lista[] = $row;
echo json_encode($lista);
