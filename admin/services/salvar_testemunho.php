<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/api_security.php';
validateApiAccess($ALLOWED_ORIGINS);

if (empty($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

require_once ROOT . '/config/database.php';

$body     = json_decode(file_get_contents('php://input'), true);
$nome     = trim($body['nome']     ?? '');
$profissao = trim($body['profissao'] ?? '');
$edicao   = trim($body['edicao']   ?? '');
$texto    = trim($body['texto']    ?? '');

if (!$nome || !$edicao || !strip_tags($texto)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

$textoSanitizado = strip_tags($texto, '<p><br><strong><em><u>');

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO testemunhos (nome, profissao, edicao, texto) VALUES (:nome, :profissao, :edicao, :texto)");
    $stmt->execute([
        ':nome'      => $nome,
        ':profissao' => $profissao ?: null,
        ':edicao'    => $edicao,
        ':texto'     => $textoSanitizado,
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados.']);
}
