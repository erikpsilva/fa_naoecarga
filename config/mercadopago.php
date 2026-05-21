<?php

// Credenciais de Teste
define('MP_ACCESS_TOKEN_TEST', 'TEST-3536024446802367-050810-70b3e6224610dce156cffe5b6883af8c-2964733367');
define('MP_PUBLIC_KEY_TEST',   'TEST-4a341595-9e1e-4b2f-97d0-bc7f7f73a03a');

// Credenciais de Produção (usar apenas no backend)
define('MP_ACCESS_TOKEN_PROD', 'APP_USR-3536024446802367-050810-eea621d7db59cacc1d2a64294f7310fa-2964733367');
define('MP_PUBLIC_KEY_PROD',   'APP_USR-e014d6ed-06ae-4ebf-8fe1-5cf91360f371');

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
