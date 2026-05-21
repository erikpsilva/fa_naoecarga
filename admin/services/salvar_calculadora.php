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

if (!in_array($_SESSION['usuario']['nivel_acesso'], ['admin', 'editor'])) {
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

$body = json_decode(file_get_contents('php://input'), true);

$a1nome = isset($body['animal_1_nome']) ? trim($body['animal_1_nome']) : '';
$a1pct  = isset($body['animal_1_pct'])  ? (float)$body['animal_1_pct'] : 0;
$a2nome = isset($body['animal_2_nome']) ? trim($body['animal_2_nome']) : '';
$a2pct  = isset($body['animal_2_pct'])  ? (float)$body['animal_2_pct'] : 0;
$a3nome = isset($body['animal_3_nome']) ? trim($body['animal_3_nome']) : '';
$a3pct  = isset($body['animal_3_pct'])  ? (float)$body['animal_3_pct'] : 0;
$a4nome = isset($body['animal_4_nome']) ? trim($body['animal_4_nome']) : '';
$a4pct  = isset($body['animal_4_pct'])  ? (float)$body['animal_4_pct'] : 0;
$btn1   = isset($body['valor_btn_1'])   ? (int)$body['valor_btn_1']   : 0;
$btn2   = isset($body['valor_btn_2'])   ? (int)$body['valor_btn_2']   : 0;
$btn3   = isset($body['valor_btn_3'])   ? (int)$body['valor_btn_3']   : 0;
$custo  = isset($body['custo_por_animal']) ? (float)$body['custo_por_animal'] : 0;

// Valida soma das porcentagens
$soma = round($a1pct + $a2pct + $a3pct + $a4pct, 2);
if ($soma !== 100.0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'A soma das porcentagens deve ser exatamente 100% (atual: ' . $soma . '%)']);
    exit;
}

if (!$a1nome || !$a2nome || !$a3nome || !$a4nome) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos os nomes de animais são obrigatórios']);
    exit;
}

if ($btn1 <= 0 || $btn2 <= 0 || $btn3 <= 0 || $custo <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valores dos botões e custo por animal devem ser maiores que zero']);
    exit;
}

try {
    $pdo = getDbConnection();

    $exists = $pdo->query("SELECT COUNT(*) FROM calculadora_config WHERE id = 1")->fetchColumn();

    if ($exists) {
        $stmt = $pdo->prepare("
            UPDATE calculadora_config SET
                animal_1_nome = :a1n, animal_1_pct = :a1p,
                animal_2_nome = :a2n, animal_2_pct = :a2p,
                animal_3_nome = :a3n, animal_3_pct = :a3p,
                animal_4_nome = :a4n, animal_4_pct = :a4p,
                valor_btn_1 = :b1, valor_btn_2 = :b2, valor_btn_3 = :b3,
                custo_por_animal = :custo
            WHERE id = 1
        ");
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO calculadora_config
                (id, animal_1_nome, animal_1_pct, animal_2_nome, animal_2_pct,
                 animal_3_nome, animal_3_pct, animal_4_nome, animal_4_pct,
                 valor_btn_1, valor_btn_2, valor_btn_3, custo_por_animal)
            VALUES
                (1, :a1n, :a1p, :a2n, :a2p, :a3n, :a3p, :a4n, :a4p, :b1, :b2, :b3, :custo)
        ");
    }

    $stmt->execute([
        ':a1n'   => $a1nome, ':a1p' => $a1pct,
        ':a2n'   => $a2nome, ':a2p' => $a2pct,
        ':a3n'   => $a3nome, ':a3p' => $a3pct,
        ':a4n'   => $a4nome, ':a4p' => $a4pct,
        ':b1'    => $btn1,   ':b2'  => $btn2, ':b3' => $btn3,
        ':custo' => $custo,
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados']);
}
