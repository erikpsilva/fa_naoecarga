<?php
require_once ROOT . '/config/database.php';
try {
    $stmtB = getDbConnection()->prepare("SELECT arquivo FROM banners WHERE pagina = 'doacao-erro'");
    $stmtB->execute();
    $bannerDoacao = $stmtB->fetchColumn() ?: 'images/bannerHome.jpg';
} catch (Exception $e) { $bannerDoacao = 'images/bannerHome.jpg'; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<?php include ROOT . '/includes/assets.php';?>
<title>Erro no pagamento — Animal não é carga</title>
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
        <div class="doacaoRetorno__box doacaoRetorno__box--erro">
            <svg class="doacaoRetorno__icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="32" cy="32" r="32" fill="#f8d7da"/>
                <path d="M22 22l20 20M42 22L22 42" stroke="#721c24" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <h1 class="doacaoRetorno__title">Não foi possível processar sua doação</h1>
            <p class="doacaoRetorno__text">Houve um problema com o seu pagamento. Por favor, tente novamente ou escolha outra forma de pagamento.</p>
            <a class="homeButton homeButton--primary" href="<?= BASE_URL ?>/?url=inicio#calculadora">Tentar novamente</a>
        </div>
    </div>
</main>

<?php include ROOT . '/includes/footer/footer.php';?>
<?php include ROOT . '/includes/scripts.php';?>
</body>
</html>
