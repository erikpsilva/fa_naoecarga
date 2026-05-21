<?php
require_once ROOT . '/config/database.php';
try {
    $stmtB = getDbConnection()->prepare("SELECT arquivo FROM banners WHERE pagina = 'doacao-pendente'");
    $stmtB->execute();
    $bannerDoacao = $stmtB->fetchColumn() ?: 'images/bannerHome.jpg';
} catch (Exception $e) { $bannerDoacao = 'images/bannerHome.jpg'; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php include ROOT . '/includes/assets.php';?>
<title>Doação em análise — Animal não é carga</title>
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
        <div class="doacaoRetorno__box doacaoRetorno__box--pendente">
            <svg class="doacaoRetorno__icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="32" cy="32" r="32" fill="#fff3cd"/>
                <path d="M32 20v14l8 4" stroke="#856404" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 class="doacaoRetorno__title">Pagamento em análise</h1>
            <p class="doacaoRetorno__text">Seu pagamento está sendo processado. Assim que for confirmado, você receberá uma notificação. Isso pode levar alguns instantes.</p>
            <a class="homeButton homeButton--primary" href="<?= BASE_URL ?>/?url=inicio">Voltar ao início</a>
        </div>
    </div>
</main>

<?php include ROOT . '/includes/footer/footer.php';?>
<?php include ROOT . '/includes/scripts.php';?>
</body>
</html>
