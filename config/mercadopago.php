<?php

// Credenciais de Teste
define('MP_ACCESS_TOKEN_TEST', 'TEST-4992767604389942-052511-37ac60b5cbb878c9ba29c4b16601dcc5-2964733367');
define('MP_PUBLIC_KEY_TEST',   'TEST-ceb2cefe-dfcc-4e68-94c5-891ea72781d0');

// Credenciais de Produção (usar apenas no backend)
define('MP_ACCESS_TOKEN_PROD', 'APP_USR-4992767604389942-052511-f61d1008278dc1ddc355691e9149330e-2964733367');
define('MP_PUBLIC_KEY_PROD',   'APP_USR-25bc953e-a71f-4c05-bd4f-ab3d3b970e3d');

// URL pública para webhooks — em localhost preencher com URL do ngrok
define('MP_NGROK_URL', '');

// Lê o modo teste do banco de dados
// Se a tabela não existir ou falhar, mantém modo teste por segurança
function mpGetModoTeste(): bool {
    if (!function_exists('getDbConnection')) {
        require_once __DIR__ . '/database.php';
    }
    try {
        $pdo  = getDbConnection();
        $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mp_modo_teste'");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? ($row['valor'] === '1') : true;
    } catch (Exception $e) {
        return true; // fallback seguro: modo teste
    }
}

$mpIsTeste = mpGetModoTeste();

define('MP_IS_TEST',      $mpIsTeste);
define('MP_ACCESS_TOKEN', $mpIsTeste ? MP_ACCESS_TOKEN_TEST : MP_ACCESS_TOKEN_PROD);
define('MP_PUBLIC_KEY',   $mpIsTeste ? MP_PUBLIC_KEY_TEST   : MP_PUBLIC_KEY_PROD);
