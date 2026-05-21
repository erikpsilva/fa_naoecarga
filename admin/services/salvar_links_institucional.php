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

if (!isset($body['links']) || !is_array($body['links'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$links = array_values($body['links']);

if (count($links) > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Máximo de 5 links permitidos']);
    exit;
}

// Filter out empty rows (no name and no url)
$linksValidos = [];
foreach ($links as $i => $link) {
    $nome   = trim($link['nome']   ?? '');
    $url    = trim($link['url']    ?? '');
    $target = ($link['target'] ?? '_self') === '_blank' ? '_blank' : '_self';

    if ($nome === '' && $url === '') continue;

    $linksValidos[] = ['nome' => $nome, 'url' => $url, 'target' => $target, 'ordem' => $i + 1];
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    $pdo->exec("DELETE FROM footer_links_institucional");

    if (!empty($linksValidos)) {
        $stmt = $pdo->prepare(
            "INSERT INTO footer_links_institucional (ordem, nome, url, target) VALUES (:ordem, :nome, :url, :target)"
        );
        foreach ($linksValidos as $link) {
            $stmt->execute([
                ':ordem'  => $link['ordem'],
                ':nome'   => $link['nome'],
                ':url'    => $link['url'],
                ':target' => $link['target'],
            ]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco']);
}
