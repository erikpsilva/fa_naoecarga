<?php

define('ROOT', dirname(__DIR__));

$host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isHttps     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL'])   && $_SERVER['HTTP_X_FORWARDED_SSL']   === 'on');
$isLocalhost = strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
$protocol    = ($isHttps || !$isLocalhost) ? 'https' : 'http';
$basePath    = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$basePath    = ($basePath === '/' || $basePath === '.') ? '' : $basePath;
define('BASE_URL', $protocol . '://' . $host . $basePath);

require_once ROOT . '/config/mercadopago.php';
require_once ROOT . '/config/database.php';

header('Content-Type: application/json; charset=utf-8');

// ─── Logging ──────────────────────────────────────────────────────────────────
function mpLog(string $label, $data): void
{
    $logDir = ROOT . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $label . ': ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    file_put_contents($logDir . '/pagamento_mp.log', $entry, FILE_APPEND | LOCK_EX);
}

// ─── Salva doador no banco ────────────────────────────────────────────────────
function salvarDoador(string $nome, $email, string $telefone, string $tipo, float $valor, string $mpId, string $lgpdTag): void
{
    try {
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO doadores (nome, email, telefone, tipo, valor, status, mp_id, lgpd_tag)
            VALUES (:nome, :email, :telefone, :tipo, :valor, 'pendente', :mp_id, :lgpd_tag)
        ");
        $stmt->execute([
            ':nome'     => $nome,
            ':email'    => $email ?: '',
            ':telefone' => $telefone,
            ':tipo'     => $tipo,
            ':valor'    => $valor,
            ':mp_id'    => $mpId,
            ':lgpd_tag' => $lgpdTag,
        ]);
    } catch (Exception $e) {
        mpLog('DB_ERROR salvarDoador', $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true);
$valor    = isset($body['valor'])    ? (float) $body['valor']                                    : 0;
$tipo     = isset($body['tipo'])     ? $body['tipo']                                             : '';
$nome     = isset($body['nome'])     ? trim($body['nome'])                                       : '';
$email    = isset($body['email'])    ? filter_var(trim($body['email']), FILTER_VALIDATE_EMAIL)   : false;
$telefone = isset($body['telefone']) ? preg_replace('/\D/', '', $body['telefone'])               : '';
$lgpdTag  = (isset($body['lgpd_tag']) && $body['lgpd_tag'] === 'LGPD_OK') ? 'LGPD_OK' : 'LGPD_NOK';

mpLog('REQUEST', ['valor' => $valor, 'tipo' => $tipo, 'base_url' => BASE_URL]);

if ($valor <= 0 || !in_array($tipo, ['unica', 'mensal'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Parâmetros inválidos']);
    exit;
}

$publicBase = MP_NGROK_URL !== '' ? MP_NGROK_URL : BASE_URL;

$headers = [
    'Authorization: Bearer ' . MP_ACCESS_TOKEN,
    'Content-Type: application/json',
    'X-Idempotency-Key: ' . uniqid('mp_', true),
];

// ─── DOAÇÃO ÚNICA — Payment Preference ───────────────────────────────────────
if ($tipo === 'unica') {
    $payload = [
        'items' => [[
            'title'       => 'Doação Animal não é carga',
            'quantity'    => 1,
            'unit_price'  => $valor,
            'currency_id' => 'BRL',
        ]],
        'back_urls' => [
            'success' => $publicBase . '/?url=doacao-sucesso',
            'failure' => $publicBase . '/?url=doacao-erro',
            'pending' => $publicBase . '/?url=doacao-pendente',
        ],
        'auto_return'          => 'approved',
        'notification_url'     => $publicBase . '/services/webhook_mp.php',
        'statement_descriptor' => 'Forum Animal',
        'payer'                => array_filter([
            'name'  => $nome,
            'email' => $email ?: null,
            'phone' => $telefone ? ['number' => $telefone] : null,
        ]),
    ];

    [$status, $response] = mpPost('https://api.mercadopago.com/checkout/preferences', $headers, $payload);
    mpLog('UNICA response HTTP ' . $status, $response);

    if ($status >= 400 || empty($response['init_point'])) {
        http_response_code(502);
        echo json_encode(['erro' => 'Falha ao criar preferência', 'detalhe' => $response]);
        exit;
    }

    salvarDoador($nome, $email, $telefone, 'unica', $valor, $response['id'] ?? '', $lgpdTag);

    $checkoutUrl = MP_IS_TEST
        ? ($response['sandbox_init_point'] ?? $response['init_point'])
        : $response['init_point'];

    echo json_encode(['checkout_url' => $checkoutUrl]);
    exit;
}

// ─── DOAÇÃO MENSAL — Preapproval (Assinatura) ────────────────────────────────
if ($tipo === 'mensal') {
    if (!$email) {
        http_response_code(400);
        echo json_encode(['erro' => 'E-mail inválido para assinatura mensal']);
        exit;
    }

    $payload = [
        'reason'           => 'Doação Mensal — Animal não é carga',
        'payer_email'      => $email,
        'back_url'         => $publicBase . '/?url=doacao-sucesso',
        'notification_url' => $publicBase . '/services/webhook_mp.php',
        'status'           => 'pending',
        'auto_recurring'   => [
            'frequency'          => 1,
            'frequency_type'     => 'months',
            'transaction_amount' => $valor,
            'currency_id'        => 'BRL',
        ],
    ];

    [$status, $response] = mpPost('https://api.mercadopago.com/preapproval', $headers, $payload);
    mpLog('MENSAL response HTTP ' . $status, $response);

    if ($status >= 400 || empty($response['init_point'])) {
        http_response_code(502);
        echo json_encode(['erro' => 'Falha ao criar assinatura', 'detalhe' => $response]);
        exit;
    }

    salvarDoador($nome, $email, $telefone, 'mensal', $valor, $response['id'] ?? '', $lgpdTag);

    echo json_encode(['checkout_url' => $response['init_point']]);
    exit;
}

// ─── Helper: POST via cURL — retorna [httpStatus, arrayResposta] ──────────────
function mpPost(string $url, array $headers, array $payload): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 15,
    ]);
    $raw    = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = $raw ? json_decode($raw, true) : [];
    return [$status, $data ?? []];
}
