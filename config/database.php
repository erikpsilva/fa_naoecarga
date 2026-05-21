<?php

if (!defined('DB_HOST')) {
    $isLocalhost = (
        isset($_SERVER['HTTP_HOST']) &&
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
         strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)
    );

    if ($isLocalhost) {
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'forum3858119_naoecarga');
        define('DB_USER', 'root');
        define('DB_PASS', '');
    } else {
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'forum3858119_naoecarga');
        define('DB_USER', 'forum3858119_user_naoecarga');
        define('DB_PASS', 'Theking!@389518');
    }
}

function getDbConnection() {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados.']);
        exit;
    }
}
