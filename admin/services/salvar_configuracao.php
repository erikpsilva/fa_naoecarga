<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__, 3) . '/config/api_security.php';
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

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/database.php';

$body  = json_decode(file_get_contents('php://input'), true);
$chave = isset($body['chave']) ? trim($body['chave']) : '';
$valor = isset($body['valor']) ? trim($body['valor']) : '';

// Chaves permitidas para evitar gravações arbitrárias
$chavesPermitidas = [
    'mp_modo_teste',
    'bloco_banner', 'bloco_intro', 'bloco_apoiar',
    'bloco_calculadora', 'bloco_apadrinhe', 'bloco_testemunhos',
];

if (!in_array($chave, $chavesPermitidas) || $valor === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor)
                           ON DUPLICATE KEY UPDATE valor = :valor, updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([':chave' => $chave, ':valor' => $valor]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco']);
}
