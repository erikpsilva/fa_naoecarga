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

require_once ROOT . '/config/mercadopago.php';
require_once ROOT . '/config/database.php';

$map = [
    'approved'   => 'aprovado',
    'authorized' => 'aprovado',
    'active'     => 'aprovado',
    'rejected'   => 'recusado',
    'cancelled'  => 'cancelado',
    'paused'     => 'cancelado',
];

function mpGet(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . MP_ACCESS_TOKEN],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    return $raw ? json_decode($raw, true) : null;
}

try {
    $pdo  = getDbConnection();
    $stmt = $pdo->query("SELECT id, mp_id, tipo FROM doadores WHERE status = 'pendente' AND mp_id != ''");
    $pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar doadores']);
    exit;
}

$atualizados = 0;
$erros       = 0;

foreach ($pendentes as $doador) {
    $mpId    = $doador['mp_id'];
    $tipo    = $doador['tipo'];
    $mpStatus = null;

    if ($tipo === 'unica') {
        // Busca pagamentos associados à preferência
        $result = mpGet("https://api.mercadopago.com/v1/payments/search?preference_id={$mpId}&sort=date_created&criteria=desc&limit=1");
        $mpStatus = $result['results'][0]['status'] ?? null;
    } elseif ($tipo === 'mensal') {
        $result   = mpGet("https://api.mercadopago.com/preapproval/{$mpId}");
        $mpStatus = $result['status'] ?? null;
    }

    if (!$mpStatus) {
        $erros++;
        continue;
    }

    $novoStatus = $map[$mpStatus] ?? null;
    if (!$novoStatus || $novoStatus === 'pendente') {
        continue;
    }

    try {
        $upd = $pdo->prepare("UPDATE doadores SET status = :status WHERE id = :id");
        $upd->execute([':status' => $novoStatus, ':id' => $doador['id']]);
        $atualizados++;
    } catch (Exception $e) {
        $erros++;
    }
}

echo json_encode([
    'success'     => true,
    'atualizados' => $atualizados,
    'erros'       => $erros,
    'total'       => count($pendentes),
]);
