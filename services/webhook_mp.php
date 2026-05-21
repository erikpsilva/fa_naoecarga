<?php

define('ROOT', dirname(__DIR__));
require_once ROOT . '/config/mercadopago.php';
require_once ROOT . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$logDir  = ROOT . '/logs';
$logFile = $logDir . '/webhook_mp.log';

if (!is_dir($logDir)) mkdir($logDir, 0755, true);

$entry = '[' . date('Y-m-d H:i:s') . '] RAW: ' . $raw . PHP_EOL;
file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

// ─── Atualiza status do doador no banco ───────────────────────────────────────
function atualizarStatusDoador(string $mpId, string $mpStatus): void
{
    $map = [
        'approved'      => 'aprovado',
        'authorized'    => 'aprovado',
        'active'        => 'aprovado',
        'rejected'      => 'recusado',
        'cancelled'     => 'cancelado',
        'paused'        => 'cancelado',
    ];
    $novoStatus = $map[$mpStatus] ?? null;
    if (!$novoStatus) return;

    try {
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare("UPDATE doadores SET status = :status WHERE mp_id = :mp_id");
        $stmt->execute([':status' => $novoStatus, ':mp_id' => $mpId]);
    } catch (Exception $e) {
        global $logFile;
        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] DB_ERROR: ' . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

$topic = $_GET['topic'] ?? ($data['type'] ?? '');

if ($topic === 'payment') {
    $paymentId = $_GET['id'] ?? ($data['data']['id'] ?? null);
    if ($paymentId) {
        $info   = mpGet("https://api.mercadopago.com/v1/payments/{$paymentId}");
        $status = $info['status'] ?? 'unknown';
        $prefId = $info['preference_id'] ?? null;

        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] Payment ' . $paymentId . ' status: ' . $status . ' pref: ' . $prefId . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($prefId) atualizarStatusDoador($prefId, $status);
    }
}

if ($topic === 'preapproval') {
    $preapprovalId = $_GET['id'] ?? ($data['data']['id'] ?? null);
    if ($preapprovalId) {
        $info   = mpGet("https://api.mercadopago.com/preapproval/{$preapprovalId}");
        $status = $info['status'] ?? 'unknown';

        file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] Preapproval ' . $preapprovalId . ' status: ' . $status . PHP_EOL, FILE_APPEND | LOCK_EX);

        atualizarStatusDoador($preapprovalId, $status);
    }
}

http_response_code(200);
echo json_encode(['ok' => true]);

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
