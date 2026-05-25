<?php

$ALLOWED_ORIGINS = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:3002',
    'http://127.0.0.1',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:3002',
    'https://doe.forumanimal.org',
];

function validateApiAccess(array $allowedOrigins): void {
    $origin  = $_SERVER['HTTP_ORIGIN']  ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host    = $_SERVER['HTTP_HOST']    ?? '';

    // Extrai apenas os hosts permitidos (ignora protocolo e porta padrão)
    $allowedHosts = array_map(fn($o) => parse_url($o, PHP_URL_HOST), $allowedOrigins);

    $originAllowed = false;

    if (!empty($origin)) {
        // Compara pelo host do Origin (ignora protocolo e porta)
        $originHost = parse_url($origin, PHP_URL_HOST) ?? '';
        if ($originHost && in_array($originHost, $allowedHosts, true)) {
            $originAllowed = true;
            header('Access-Control-Allow-Origin: ' . $origin);
        }
    } elseif (!empty($referer)) {
        // Sem Origin: valida pelo Referer
        $refererHost = parse_url($referer, PHP_URL_HOST) ?? '';
        if ($refererHost && in_array($refererHost, $allowedHosts, true)) {
            $originAllowed = true;
        }
    } else {
        // Sem Origin nem Referer: requisição direta — valida pelo HTTP_HOST
        $bareHost = explode(':', $host)[0]; // remove porta se houver
        if ($bareHost && in_array($bareHost, $allowedHosts, true)) {
            $originAllowed = true;
        }
    }

    if (!$originAllowed) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Acesso não autorizado.']);
        exit;
    }

    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // Responde preflight OPTIONS do CORS e encerra
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
