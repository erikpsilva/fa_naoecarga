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

$body = json_decode(file_get_contents('php://input'), true);

$chavesPermitidas = [
    'link_instagram',
    'link_facebook',
    'link_youtube',
    'link_doe_agora',
    'link_doe_agora_target',
    'link_seja_voluntario',
    'link_seja_voluntario_target',
];

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Validate all keys before saving
foreach ($body as $chave => $valor) {
    if (!in_array($chave, $chavesPermitidas, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Chave não permitida: ' . htmlspecialchars($chave)]);
        exit;
    }
}

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO configuracoes (chave, valor) VALUES (:chave, :valor)
         ON DUPLICATE KEY UPDATE valor = :valor, updated_at = CURRENT_TIMESTAMP"
    );

    foreach ($body as $chave => $valor) {
        $stmt->execute([':chave' => $chave, ':valor' => trim($valor)]);
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco']);
}
