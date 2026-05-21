<?php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__FILE__, 3) . '/config/api_security.php';
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

require_once dirname(__FILE__, 3) . '/config/database.php';

$body      = json_decode(file_get_contents('php://input'), true);
$id        = isset($body['id'])        ? (int) $body['id']                                         : 0;
$nome      = isset($body['nome'])      ? trim($body['nome'])                                        : '';
$sobrenome = isset($body['sobrenome']) ? trim($body['sobrenome'])                                   : '';
$email     = isset($body['email'])     ? trim($body['email'])                                       : '';
$cpf       = isset($body['cpf'])       ? preg_replace('/[^\d]/', '', $body['cpf'])                  : '';
$nivel     = isset($body['nivel'])     ? trim($body['nivel'])                                       : '';
$senha     = $body['senha']            ?? '';
$confirmar = $body['confirmar']        ?? '';

if (!$id || mb_strlen($nome) < 2 || mb_strlen($sobrenome) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nome e sobrenome devem ter ao menos 2 caracteres.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
    exit;
}
if (strlen($cpf) !== 11) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'CPF inválido.']);
    exit;
}
if (!in_array($nivel, ['admin', 'editor', 'leitor'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nível de acesso inválido.']);
    exit;
}
if ($senha !== '') {
    if (strlen($senha) < 6 || strlen($senha) > 20) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A senha deve ter entre 6 e 20 caracteres.']);
        exit;
    }
    if ($senha !== $confirmar) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'As senhas não coincidem.']);
        exit;
    }
}

$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT id FROM admin_usuarios WHERE (email = ? OR cpf = ?) AND id != ? LIMIT 1");
$stmt->execute([$email, $cpf, $id]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'E-mail ou CPF já usado por outro usuário.']);
    exit;
}

$nomeCompleto = $nome . ' ' . $sobrenome;

if ($senha !== '') {
    $hash = password_hash($senha, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE admin_usuarios SET nome_completo=?, email=?, cpf=?, nivel_acesso=?, senha=? WHERE id=?")
        ->execute([$nomeCompleto, $email, $cpf, $nivel, $hash, $id]);
} else {
    $pdo->prepare("UPDATE admin_usuarios SET nome_completo=?, email=?, cpf=?, nivel_acesso=? WHERE id=?")
        ->execute([$nomeCompleto, $email, $cpf, $nivel, $id]);
}

// Atualiza sessão se o admin editou a si mesmo
if ((int)$_SESSION['usuario']['id'] === $id) {
    $_SESSION['usuario']['nome_completo'] = $nomeCompleto;
    $_SESSION['usuario']['email']         = $email;
    $_SESSION['usuario']['nivel_acesso']  = $nivel;
}

echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso.']);
