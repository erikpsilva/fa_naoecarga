<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/api_security.php';
validateApiAccess($ALLOWED_ORIGINS);

if (empty($_SESSION['usuario']) || !in_array($_SESSION['usuario']['nivel_acesso'], ['admin', 'editor'])) {
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

$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : 0;
if ($slot < 1 || $slot > 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Slot inválido.']);
    exit;
}

if (empty($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
    exit;
}

$file    = $_FILES['imagem'];
$maxSize = 3 * 1024 * 1024; // 3 MB
$allowed = ['image/jpeg', 'image/png', 'image/webp'];

$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato inválido. Use JPG, PNG ou WebP.']);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 3 MB.']);
    exit;
}

$ext       = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
$filename  = 'animal_' . $slot . '_' . time() . '.' . $ext;
$uploadDir = ROOT . '/uploads/animais/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo.']);
    exit;
}

$coluna = 'animal_' . $slot . '_imagem';
$novoPath = 'uploads/animais/' . $filename;

try {
    $pdo = getDbConnection();

    // Remove imagem anterior se não for uma das originais
    $row = $pdo->query("SELECT {$coluna} FROM calculadora_config WHERE id = 1")->fetch();
    if ($row && strpos($row[$coluna], 'uploads/animais/animal_') === 0) {
        $old = ROOT . '/' . $row[$coluna];
        if (file_exists($old)) @unlink($old);
    }

    $pdo->prepare("UPDATE calculadora_config SET {$coluna} = :path WHERE id = 1")
        ->execute([':path' => $novoPath]);

    echo json_encode(['success' => true, 'path' => $novoPath]);
} catch (Exception $e) {
    error_log('[upload_animal_imagem] slot=' . $slot . ' | ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
}
