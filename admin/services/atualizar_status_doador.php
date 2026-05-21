<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/api_security.php';
validateApiAccess($ALLOWED_ORIGINS);

if (empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SESSION['usuario']['nivel_acesso'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

require_once ROOT . '/config/database.php';

$body   = json_decode(file_get_contents('php://input'), true);
$id     = isset($body['id'])     ? (int) $body['id']        : 0;
$status = isset($body['status']) ? trim($body['status'])     : '';

$permitidos = ['pendente', 'aprovado', 'recusado', 'cancelado'];

if (!$id || !in_array($status, $permitidos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare("UPDATE doadores SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $status, ':id' => $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar']);
}
