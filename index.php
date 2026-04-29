<?php
ini_set('display_errors', 0);
error_reporting(0);
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
});
set_error_handler(function($errno, $errstr) {
    http_response_code(500);
    echo json_encode(['error' => $errstr]);
    exit;
});
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$db = new mysqli(
    getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASSWORD'),
    getenv('DB_NAME'),
    (int)(getenv('DB_PORT') ?: 3306)
);

if ($db->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'DB: ' . $db->connect_error]);
    exit;
}

$db->query("CREATE TABLE IF NOT EXISTS games (
    id VARCHAR(10) PRIMARY KEY,
    board TEXT NOT NULL,
    turn VARCHAR(1) NOT NULL DEFAULT 'X',
    status VARCHAR(10) NOT NULL DEFAULT 'waiting'
)");

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$body = json_decode(file_get_contents('php://input'), true) ?? [];

function checkWinner(array $board): ?string {
    $wins = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
    foreach ($wins as [$a, $b, $c]) {
        if ($board[$a] !== '' && $board[$a] === $board[$b] && $board[$a] === $board[$c]) {
            return $board[$a];
        }
    }
    foreach ($board as $cell) {
        if ($cell === '') return null;
    }
    return 'draw';
}

if ($method === 'POST' && $path === '/create_game') {
    $id = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
    $board = json_encode(array_fill(0, 9, ''));
    $stmt = $db->prepare("INSERT INTO games (id, board, turn, status) VALUES (?, ?, 'X', 'waiting')");
    $stmt->bind_param('ss', $id, $board);
    $stmt->execute();
    echo json_encode(['id' => $id]);

} elseif ($method === 'POST' && $path === '/join_game') {
    $id = strtoupper($body['id'] ?? '');
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $game = $stmt->get_result()->fetch_assoc();
    if (!$game) {
        http_response_code(404);
        echo json_encode(['error' => 'Game not found']);
        exit;
    }
    if ($game['status'] !== 'waiting') {
        http_response_code(400);
        echo json_encode(['error' => 'Game already full']);
        exit;
    }
    $stmt2 = $db->prepare("UPDATE games SET status = 'playing' WHERE id = ?");
    $stmt2->bind_param('s', $id);
    $stmt2->execute();
    echo json_encode(['id' => $id]);

} elseif ($method === 'GET' && $path === '/game_state') {
    $id = strtoupper($_GET['id'] ?? '');
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $game = $stmt->get_result()->fetch_assoc();
    if (!$game) {
        http_response_code(404);
        echo json_encode(['error' => 'Game not found']);
        exit;
    }
    echo json_encode([
        'id' => $game['id'],
        'board' => json_decode($game['board']),
        'turn' => $game['turn'],
        'status' => $game['status']
    ]);

} elseif ($method === 'POST' && $path === '/move') {
    $id = strtoupper($body['id'] ?? '');
    $index = (int)($body['index'] ?? -1);
    $player = $body['player'] ?? '';
    $stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $game = $stmt->get_result()->fetch_assoc();
    if (!$game) {
        http_response_code(404);
        echo json_encode(['error' => 'Game not found']);
        exit;
    }
    if ($game['status'] !== 'playing') {
        http_response_code(400);
        echo json_encode(['error' => 'Game not active']);
        exit;
    }
    if ($game['turn'] !== $player) {
        http_response_code(400);
        echo json_encode(['error' => 'Not your turn']);
        exit;
    }
    $board = json_decode($game['board'], true);
    if ($board[$index] !== '') {
        http_response_code(400);
        echo json_encode(['error' => 'Cell taken']);
        exit;
    }
    $board[$index] = $player;
    $winner = checkWinner($board);
    $nextTurn = $player === 'X' ? 'O' : 'X';
    $status = $winner ? ($winner === 'draw' ? 'draw' : 'finished') : 'playing';
    $newBoard = json_encode($board);
    $stmt2 = $db->prepare("UPDATE games SET board = ?, turn = ?, status = ? WHERE id = ?");
    $stmt2->bind_param('ssss', $newBoard, $nextTurn, $status, $id);
    $stmt2->execute();
    echo json_encode(['board' => $board, 'turn' => $nextTurn, 'status' => $status, 'winner' => $winner]);

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}

$db->close();
