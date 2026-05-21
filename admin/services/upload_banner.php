<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

define('ROOT', dirname(dirname(__DIR__)));
require_once ROOT . '/config/api_security.php';
validateApiAccess($ALLOWED_ORIGINS);

if (empty($_SESSION['usuario']) || $_SESSION['usuario']['nivel_acesso'] !== 'admin') {
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

$pagina  = isset($_POST['pagina']) ? trim($_POST['pagina']) : '';
$paginasPermitidas = ['home', 'doacao'];

if (!in_array($pagina, $paginasPermitidas)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Página inválida.']);
    exit;
}

if (empty($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload.']);
    exit;
}

$file     = $_FILES['banner'];
$maxSize  = 5 * 1024 * 1024; // 5 MB
$allowed  = ['image/jpeg', 'image/png', 'image/webp'];
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
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande. Máximo 5 MB.']);
    exit;
}

$ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
$filename = 'banner_' . $pagina . '_' . time() . '.' . $ext;
$uploadDir = ROOT . '/uploads/banners/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo.']);
    exit;
}

// Remove arquivo anterior se não for o padrão
try {
    $pdo  = getDbConnection();
    $stmt = $pdo->prepare("SELECT arquivo FROM banners WHERE pagina = ?");
    $stmt->execute([$pagina]);
    $row  = $stmt->fetch();
    if ($row && strpos($row['arquivo'], 'uploads/banners/') === 0) {
        $old = ROOT . '/' . $row['arquivo'];
        if (file_exists($old)) @unlink($old);
    }

    $pdo->prepare("INSERT INTO banners (pagina, arquivo) VALUES (?, ?) ON DUPLICATE KEY UPDATE arquivo = ?, updated_at = CURRENT_TIMESTAMP")
        ->execute([$pagina, 'uploads/banners/' . $filename, 'uploads/banners/' . $filename]);

    echo json_encode(['success' => true, 'arquivo' => 'uploads/banners/' . $filename]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
}
