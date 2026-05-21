<?php
// O MP envia preference_id e collection_status direto na URL de retorno
$prefId           = isset($_GET['preference_id'])    ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['preference_id'])    : null;
$collectionStatus = isset($_GET['collection_status']) ? preg_replace('/[^a-z]/', '', $_GET['collection_status'])         : null;

require_once ROOT . '/config/database.php';

// Atualiza status do doador
if ($prefId && $collectionStatus) {
    $map = ['approved' => 'aprovado', 'rejected' => 'recusado', 'null' => 'cancelado'];
    $novoStatus = $map[$collectionStatus] ?? null;
    if ($novoStatus) {
        try {
            $pdo  = getDbConnection();
            $stmt = $pdo->prepare("UPDATE doadores SET status = :status WHERE mp_id = :mp_id");
            $stmt->execute([':status' => $novoStatus, ':mp_id' => $prefId]);
        } catch (Exception $e) { /* silencioso */ }
    }
}

// Banner
try {
    $stmtB  = getDbConnection()->prepare("SELECT arquivo FROM banners WHERE pagina = 'doacao-sucesso'");
    $stmtB->execute();
    $bannerDoacao = $stmtB->fetchColumn() ?: 'images/bannerHome.jpg';
} catch (Exception $e) {
    $bannerDoacao = 'images/bannerHome.jpg';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php include ROOT . '/includes/assets.php';?>
<title>Doação confirmada — Animal não é carga</title>
<style>
.footerCta { display: none; }
.doacaoHero { background: #050505 url("<?= BASE_URL . '/' . $bannerDoacao ?>") center top / cover no-repeat; min-height: 220px; padding-top: 136px; }
.doacaoRetorno { text-align: center; padding: 56px 0 50px; }
.doacaoRetorno__icon { width: 50px; height: 50px; display: block; margin: 0 auto 20px; }
</style>
</head>
<body>

<?php include ROOT . '/includes/header/header.php';?>

<div class="doacaoHero"></div>
<main class="doacaoRetorno">
    <div class="container">
        <div class="doacaoRetorno__box doacaoRetorno__box--sucesso">
            <svg class="doacaoRetorno__icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="32" cy="32" r="32" fill="#d4edda"/>
                <path d="M18 33l10 10 18-18" stroke="#28a745" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 class="doacaoRetorno__title">Obrigado pela sua doação!</h1>
            <p class="doacaoRetorno__text">Seu apoio é fundamental para avançarmos na proteção dos animais usados na ciência. Em breve você receberá a confirmação por e-mail.</p>
            <a class="homeButton homeButton--primary" href="<?= BASE_URL ?>/?url=inicio">Voltar ao início</a>
        </div>
    </div>
</main>

<?php include ROOT . '/includes/footer/footer.php';?>
<?php include ROOT . '/includes/scripts.php';?>
</body>
</html>
