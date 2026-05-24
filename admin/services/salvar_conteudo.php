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

        // Resolve ícones dos tópicos
        $iconeDir = ROOT . '/uploads/icones/';
        if (!is_dir($iconeDir)) mkdir($iconeDir, 0755, true);
        $iconeMimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
        $iconeNovos = [];

        foreach (['t1_icone', 't2_icone', 't3_icone'] as $campo) {
            if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) continue;
            $file = $_FILES[$campo];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!isset($iconeMimeMap[$mime])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Formato inválido para ícone. Use PNG, SVG, JPG ou WebP.']);
                exit;
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ícone muito grande (máx 2 MB).']);
                exit;
            }
            $filename = $campo . '_' . time() . '.' . $iconeMimeMap[$mime];
            if (move_uploaded_file($file['tmp_name'], $iconeDir . $filename)) {
                $originals = ['icone01.png', 'icone02.png', 'icone03.png'];
                $old = $pdo->query("SELECT `$campo` FROM conteudo_intro WHERE id = 1")->fetchColumn();
                if ($old && strpos($old, 'uploads/') === 0 && !in_array(basename($old), $originals) && file_exists(ROOT . '/' . $old)) {
                    @unlink(ROOT . '/' . $old);
                }
                $iconeNovos[$campo] = 'uploads/icones/' . $filename;
            }
        }

        $existRow = $pdo->query("SELECT t1_icone, t2_icone, t3_icone FROM conteudo_intro WHERE id = 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        $t1_icone = $iconeNovos['t1_icone'] ?? ($existRow['t1_icone'] ?: 'uploads/icones/icone01.png');
        $t2_icone = $iconeNovos['t2_icone'] ?? ($existRow['t2_icone'] ?: 'uploads/icones/icone02.png');
        $t3_icone = $iconeNovos['t3_icone'] ?? ($existRow['t3_icone'] ?: 'uploads/icones/icone03.png');

        $pdo->prepare(
            "INSERT INTO conteudo_intro (id, pretitulo, titulo, titulo_destaque, texto, imagem, t1_titulo, t1_texto, t1_icone, t2_titulo, t2_texto, t2_icone, t3_titulo, t3_texto, t3_icone)
             VALUES (1,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               pretitulo=VALUES(pretitulo), titulo=VALUES(titulo), titulo_destaque=VALUES(titulo_destaque),
               texto=VALUES(texto), imagem=VALUES(imagem),
               t1_titulo=VALUES(t1_titulo), t1_texto=VALUES(t1_texto), t1_icone=VALUES(t1_icone),
               t2_titulo=VALUES(t2_titulo), t2_texto=VALUES(t2_texto), t2_icone=VALUES(t2_icone),
               t3_titulo=VALUES(t3_titulo), t3_texto=VALUES(t3_texto), t3_icone=VALUES(t3_icone)"
        )->execute([$pretitulo, $titulo, '', $texto, $imagemAtual,
                    $t1_titulo, $t1_texto, $t1_icone, $t2_titulo, $t2_texto, $t2_icone, $t3_titulo, $t3_texto, $t3_icone]);

        echo json_encode(['success' => true, 'imagem' => $imagemNova, 'icones' => $iconeNovos ?: null]);
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
        $pdo = getDbConnection();

        // Resolve ícones dos tópicos
        $iconeDir = ROOT . '/uploads/icones/';
        if (!is_dir($iconeDir)) mkdir($iconeDir, 0755, true);
        $iconeMimeMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
        $iconeNovos = [];

        foreach (['t1_icone', 't2_icone', 't3_icone'] as $campo) {
            if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] !== UPLOAD_ERR_OK) continue;
            $file = $_FILES[$campo];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!isset($iconeMimeMap[$mime])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Formato inválido para ícone. Use PNG, SVG, JPG ou WebP.']);
                exit;
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Ícone muito grande (máx 2 MB).']);
                exit;
            }
            $filename = $campo . '_ap_' . time() . '.' . $iconeMimeMap[$mime];
            if (move_uploaded_file($file['tmp_name'], $iconeDir . $filename)) {
                $originals = ['icone04.png', 'icone05.png', 'icone06.png'];
                $old = $pdo->query("SELECT `$campo` FROM conteudo_apoiar WHERE id = 1")->fetchColumn();
                if ($old && strpos($old, 'uploads/') === 0 && !in_array(basename($old), $originals) && file_exists(ROOT . '/' . $old)) {
                    @unlink(ROOT . '/' . $old);
                }
                $iconeNovos[$campo] = 'uploads/icones/' . $filename;
            }
        }

        $existRow = $pdo->query("SELECT t1_icone, t2_icone, t3_icone FROM conteudo_apoiar WHERE id = 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        $t1_icone = $iconeNovos['t1_icone'] ?? ($existRow['t1_icone'] ?: 'uploads/icones/icone04.png');
        $t2_icone = $iconeNovos['t2_icone'] ?? ($existRow['t2_icone'] ?: 'uploads/icones/icone05.png');
        $t3_icone = $iconeNovos['t3_icone'] ?? ($existRow['t3_icone'] ?: 'uploads/icones/icone06.png');

        $pdo->prepare(
            "INSERT INTO conteudo_apoiar
               (id, pretitulo, titulo, titulo_destaque, texto1, t1_titulo, t1_texto, t1_icone, t2_titulo, t2_texto, t2_icone, t3_titulo, t3_texto, t3_icone, texto2, botao_texto, botao_link, botao_target)
             VALUES (1,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE
               pretitulo=VALUES(pretitulo), titulo=VALUES(titulo), titulo_destaque=VALUES(titulo_destaque),
               texto1=VALUES(texto1),
               t1_titulo=VALUES(t1_titulo), t1_texto=VALUES(t1_texto), t1_icone=VALUES(t1_icone),
               t2_titulo=VALUES(t2_titulo), t2_texto=VALUES(t2_texto), t2_icone=VALUES(t2_icone),
               t3_titulo=VALUES(t3_titulo), t3_texto=VALUES(t3_texto), t3_icone=VALUES(t3_icone),
               texto2=VALUES(texto2), botao_texto=VALUES(botao_texto), botao_link=VALUES(botao_link),
               botao_target=VALUES(botao_target)"
        )->execute([$pretitulo, $titulo, '', $texto1,
                    $t1_titulo, $t1_texto, $t1_icone, $t2_titulo, $t2_texto, $t2_icone, $t3_titulo, $t3_texto, $t3_icone,
                    $texto2, $botao_texto, $botao_link, $botao_target]);
        echo json_encode(['success' => true, 'icones' => $iconeNovos ?: null]);
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

/* ── Texto da Calculadora ────────────────────────────────────── */
if ($secao === 'calculadora_texto') {
    $pretitulo = trim($_POST['pretitulo'] ?? '');
    $titulo    = cleanHtml($_POST['titulo'] ?? '');
    $texto     = cleanHtml($_POST['texto']  ?? '');

    if (!$titulo || !$texto) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    try {
        getDbConnection()
            ->prepare("UPDATE calculadora_config
                       SET calc_pretitulo = :pre, calc_titulo = :tit, calc_texto = :txt
                       WHERE id = 1")
            ->execute([':pre' => $pretitulo, ':tit' => $titulo, ':txt' => $texto]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log('[salvar_conteudo:calculadora_texto] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco.']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Seção inválida.']);
