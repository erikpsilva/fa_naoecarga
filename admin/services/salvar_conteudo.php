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

$secao = trim($_POST['secao'] ?? '');
$allowed = '<p><br><strong><em><u>';

function cleanHtml($v) {
    global $allowed;
    return strip_tags(trim($v), $allowed);
}

/* ── Banner Home ─────────────────────────────────────────────── */
if ($secao === 'banner_home') {
    $titulo    = cleanHtml($_POST['titulo']    ?? '');
    $subtitulo = cleanHtml($_POST['subtitulo'] ?? '');
    $texto     = cleanHtml($_POST['texto']     ?? '');

    if (!$titulo || !$subtitulo || !$texto) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        getDbConnection()
            ->prepare("INSERT INTO conteudo_banner_home (id, titulo, subtitulo, texto) VALUES (1,?,?,?)
                       ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), subtitulo=VALUES(subtitulo), texto=VALUES(texto)")
            ->execute([$titulo, $subtitulo, $texto]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
    }
    exit;
}

/* ── Conteúdo Introdução ─────────────────────────────────────── */
if ($secao === 'intro') {
    $pretitulo = trim($_POST['pretitulo'] ?? '') ?: null;
    $titulo    = cleanHtml($_POST['titulo']    ?? '');
    $texto     = cleanHtml($_POST['texto']     ?? '');
    $t1_titulo = cleanHtml($_POST['t1_titulo'] ?? '');
    $t1_texto  = cleanHtml($_POST['t1_texto']  ?? '');
    $t2_titulo = cleanHtml($_POST['t2_titulo'] ?? '');
    $t2_texto  = cleanHtml($_POST['t2_texto']  ?? '');
    $t3_titulo = cleanHtml($_POST['t3_titulo'] ?? '');
    $t3_texto  = cleanHtml($_POST['t3_texto']  ?? '');

    if (!$titulo || !$texto || !$t1_titulo || !$t1_texto || !$t2_titulo || !$t2_texto || !$t3_titulo || !$t3_texto) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        $pdo = getDbConnection();

        // Resolve imagem: usa nova se enviada, senão mantém a atual
        $imagemNova = null;
        if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['imagem'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset($allowed[$mime])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Formato inválido. Use JPG, PNG ou WebP.']);
                exit;
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Imagem muito grande (máx 5 MB).']);
                exit;
            }

            $dir = ROOT . '/uploads/conteudo/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'intro_' . time() . '.' . $allowed[$mime];

            if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                // Apaga imagem anterior se for de uploads
                $old = $pdo->query("SELECT imagem FROM conteudo_intro WHERE id = 1")->fetchColumn();
                if ($old && strpos($old, 'uploads/') === 0 && file_exists(ROOT . '/' . $old)) {
                    @unlink(ROOT . '/' . $old);
                }
                $imagemNova = 'uploads/conteudo/' . $filename;
            }
        }

        $imagemAtual = $imagemNova
            ?? ($pdo->query("SELECT imagem FROM conteudo_intro WHERE id = 1")->fetchColumn() ?: 'images/imgRato.jpg');

        $pdo->prepare(
            "INSERT INTO conteudo_intro (id, pretitulo, titulo, titulo_destaque, texto, imagem, t1_titulo, t1_texto, t2_titulo, t2_texto, t3_titulo, t3_texto)
             VALUES (1,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               pretitulo=VALUES(pretitulo), titulo=VALUES(titulo), titulo_destaque=VALUES(titulo_destaque),
               texto=VALUES(texto), imagem=VALUES(imagem),
               t1_titulo=VALUES(t1_titulo), t1_texto=VALUES(t1_texto),
               t2_titulo=VALUES(t2_titulo), t2_texto=VALUES(t2_texto),
               t3_titulo=VALUES(t3_titulo), t3_texto=VALUES(t3_texto)"
        )->execute([$pretitulo, $titulo, '', $texto, $imagemAtual,
                    $t1_titulo, $t1_texto, $t2_titulo, $t2_texto, $t3_titulo, $t3_texto]);

        echo json_encode(['success' => true, 'imagem' => $imagemNova]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
    }
    exit;
}

/* ── Conteúdo Por que Apoiar ─────────────────────────────────── */
if ($secao === 'apoiar') {
    $pretitulo    = trim($_POST['pretitulo'] ?? '') ?: null;
    $titulo       = cleanHtml($_POST['titulo']   ?? '');
    $texto1       = cleanHtml($_POST['texto1']   ?? '');
    $t1_titulo    = cleanHtml($_POST['t1_titulo'] ?? '');
    $t1_texto     = cleanHtml($_POST['t1_texto']  ?? '');
    $t2_titulo    = cleanHtml($_POST['t2_titulo'] ?? '');
    $t2_texto     = cleanHtml($_POST['t2_texto']  ?? '');
    $t3_titulo    = cleanHtml($_POST['t3_titulo'] ?? '');
    $t3_texto     = cleanHtml($_POST['t3_texto']  ?? '');
    $texto2       = cleanHtml($_POST['texto2']   ?? '');
    $botao_texto  = trim($_POST['botao_texto']  ?? '');
    $botao_link   = trim($_POST['botao_link']   ?? '');
    $botao_target = !empty($_POST['botao_nova_aba']) ? '_blank' : '_self';

    if (!$titulo || !$texto1 || !$t1_titulo || !$t1_texto ||
        !$t2_titulo || !$t2_texto || !$t3_titulo || !$t3_texto || !$texto2 || !$botao_texto || !$botao_link) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        getDbConnection()->prepare(
            "INSERT INTO conteudo_apoiar
               (id, pretitulo, titulo, titulo_destaque, texto1, t1_titulo, t1_texto, t2_titulo, t2_texto, t3_titulo, t3_texto, texto2, botao_texto, botao_link, botao_target)
             VALUES (1,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               pretitulo=VALUES(pretitulo), titulo=VALUES(titulo), titulo_destaque=VALUES(titulo_destaque),
               texto1=VALUES(texto1),
               t1_titulo=VALUES(t1_titulo), t1_texto=VALUES(t1_texto),
               t2_titulo=VALUES(t2_titulo), t2_texto=VALUES(t2_texto),
               t3_titulo=VALUES(t3_titulo), t3_texto=VALUES(t3_texto),
               texto2=VALUES(texto2), botao_texto=VALUES(botao_texto), botao_link=VALUES(botao_link),
               botao_target=VALUES(botao_target)"
        )->execute([$pretitulo, $titulo, '', $texto1,
                    $t1_titulo, $t1_texto, $t2_titulo, $t2_texto, $t3_titulo, $t3_texto,
                    $texto2, $botao_texto, $botao_link, $botao_target]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
    }
    exit;
}

/* ── Conteúdo Apadrinhe ──────────────────────────────────────── */
if ($secao === 'apadrinhe') {
    $pretitulo   = trim($_POST['pretitulo'] ?? '') ?: null;
    $titulo      = cleanHtml($_POST['titulo']      ?? '');
    $texto       = cleanHtml($_POST['texto']       ?? '');
    $botao_texto = trim($_POST['botao_texto'] ?? '');
    $botao_valor = (float)($_POST['botao_valor'] ?? 0);

    if (!$titulo || !$texto || !$botao_texto || $botao_valor <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        $pdo = getDbConnection();

        $imagemNova = null;
        if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $file    = $_FILES['imagem'];
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!isset($allowed[$mime])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Formato inválido. Use JPG, PNG ou WebP.']);
                exit;
            }
            if ($file['size'] > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Imagem muito grande (máx 5 MB).']);
                exit;
            }

            $dir = ROOT . '/uploads/conteudo/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $filename = 'apadrinhe_' . time() . '.' . $allowed[$mime];

            if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                $old = $pdo->query("SELECT imagem FROM conteudo_apadrinhe WHERE id = 1")->fetchColumn();
                if ($old && strpos($old, 'uploads/') === 0 && file_exists(ROOT . '/' . $old)) {
                    @unlink(ROOT . '/' . $old);
                }
                $imagemNova = 'uploads/conteudo/' . $filename;
            }
        }

        $imagemAtual = $imagemNova
            ?? ($pdo->query("SELECT imagem FROM conteudo_apadrinhe WHERE id = 1")->fetchColumn() ?: 'images/imgCientista.jpg');

        $pdo->prepare(
            "INSERT INTO conteudo_apadrinhe (id, pretitulo, titulo, titulo_destaque, texto, imagem, botao_texto, botao_valor)
             VALUES (1,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               pretitulo=VALUES(pretitulo), titulo=VALUES(titulo),
               texto=VALUES(texto), imagem=VALUES(imagem),
               botao_texto=VALUES(botao_texto), botao_valor=VALUES(botao_valor)"
        )->execute([$pretitulo, $titulo, '', $texto, $imagemAtual, $botao_texto, $botao_valor]);

        echo json_encode(['success' => true, 'imagem' => $imagemNova]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Seção inválida.']);
