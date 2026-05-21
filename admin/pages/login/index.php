<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['usuario'])) {
    header('Location: ' . BASE_URL . '/admin/inicio');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Animal não é carga - Admin - Login</title>

<?php include ROOT . '/admin/includes/assets.php';?>
<style>
body { background: #1a1a1a; }

.adminLogin .formGroup {
    background: #2a2a2a;
    border-color: #444;
}
.adminLogin .formGroup__divisor h3 {
    color: #fff;
    border-bottom-color: #444;
}
.adminLogin .formGroup__divisor h3 span {
    color: #fff;
}
.adminLogin .formGroup__item label {
    color: #ccc;
}
.adminLogin .formGroup__item input {
    background: #1a1a1a;
    border-color: #555;
    color: #fff;
}
.adminLogin .formGroup__item input::placeholder {
    color: #777;
}
.adminLogin .formGroup__item input:focus-visible {
    border-color: #a01f2e;
    outline: 0;
}
</style>
</head>

<body>


<!-- BANNER INTRODUTÓRIO -->
<section class="adminLogin">
    <div class="adminLogin__content">
        <div class="formGroup">
            <div class="row">

                <div class="col-md-12">
                    <img class="adminLogin__content__logo" src="<?= BASE_URL ?>/images/logoForumAnimal.png" alt="Fórum Animal" />
                </div>

                <div class="col-md-12 formGroup__divisor">
                    <h3>Área de <span>acesso</span></h3>
                </div>
                <div class="col-md-12">
                    <div class="formGroup__item">
                        <label>E-mail</label>
                        <input class="input" type="text" name="loginEmail" id="loginEmail" placeholder="Digite seu e-mail" />
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="formGroup__item">
                        <label>Senha</label>
                        <input class="input" type="password" name="loginPassword" id="loginPassword" placeholder="Digite sua senha" />
                    </div>
                </div>

                <div class="col-md-12">
                    <button class="btn btn--primary" id="enviarLogin">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</section>


<?php include ROOT . '/admin/includes/scripts.php';?>

<script>
    var ADMIN_BASE_URL = "<?= ADMIN_BASE_URL ?>";
    var BASE_URL = "<?= BASE_URL ?>";
</script>

<?php
$version = time();
echo '<script src="' . ADMIN_BASE_URL . '/pages/login/login.js?v' . $version . '"></script>';
?>

</body>
</html>
