<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/api_security.php';
validateApiAccess($ALLOWED_ORIGINS);

if (empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo 'Não autorizado';
    exit;
}

if (!in_array($_SESSION['usuario']['nivel_acesso'], ['admin', 'editor'])) {
    http_response_code(403);
    echo 'Acesso negado';
    exit;
}

require_once ROOT . '/config/database.php';

$pdo  = getDbConnection();
$stmt = $pdo->prepare("
    SELECT nome, email, telefone, lgpd_tag, created_at
    FROM doadores
    WHERE lgpd_tag = 'LGPD_OK'
    ORDER BY created_at DESC
");
$stmt->execute();
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'leads_lgpd_ok_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// BOM UTF-8 para Excel abrir corretamente com acentos
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($out, ['Nome', 'E-mail', 'Telefone', 'LGPD', 'Data de cadastro'], ';');

foreach ($leads as $l) {
    fputcsv($out, [
        $l['nome'],
        $l['email'],
        $l['telefone'],
        $l['lgpd_tag'],
        date('d/m/Y H:i', strtotime($l['created_at'])),
    ], ';');
}

fclose($out);
exit;
