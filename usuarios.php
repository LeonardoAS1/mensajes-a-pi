<?php
header("Content-Type: application/json");
require "db.php";

$result = $conn->query("SELECT usuario FROM usuarios ORDER BY usuario ASC");
$lista  = [];
while ($row = $result->fetch_assoc()) $lista[] = ["usuario" => $row['usuario']];
echo json_encode($lista);
