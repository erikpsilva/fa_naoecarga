<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
if ($_SESSION['usuario']['nivel_acesso'] !== 'admin') {
    header('Location: ' . BASE_URL . '/admin/inicio');
    exit;
}
require_once ROOT . '/config/database.php';

$pdo     = getDbConnection();
$stmt    = $pdo->query("SELECT pagina, arquivo FROM banners WHERE pagina IN ('home','doacao-sucesso','doacao-pendente','doacao-erro')");
$rows    = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$banners = [
    'home'            => $rows['home']            ?? 'images/bannerHome.jpg',
    'doacao-sucesso'  => $rows['doacao-sucesso']  ?? 'images/bannerHome.jpg',
    'doacao-pendente' => $rows['doacao-pendente'] ?? 'images/bannerHome.jpg',
    'doacao-erro'     => $rows['doacao-erro']     ?? 'images/bannerHome.jpg',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Banners — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
.bannersGrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; }

.bannerCard { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); overflow: hidden; }
.bannerCard__preview { position: relative; width: 100%; height: 180px; background: #111; overflow: hidden; }
.bannerCard__preview img { width: 100%; height: 100%; object-fit: cover; display: block; transition: opacity .3s; }
.bannerCard__body { padding: 20px 22px 22px; }
.bannerCard__title { font-size: .95rem; font-weight: 700; margin: 0 0 4px; color: #222; }
.bannerCard__hint { font-size: .8rem; color: #999; margin: 0 0 18px; }

.bannerUpload { display: flex; flex-direction: column; gap: 10px; }
.bannerUpload__dropzone {
    border: 2px dashed #ddd; border-radius: 8px; padding: 20px; text-align: center;
    cursor: pointer; transition: border-color .2s, background .2s; position: relative;
}
.bannerUpload__dropzone:hover,
.bannerUpload__dropzone.drag-over { border-color: #a01f2e; background: #fdf5f5; }
.bannerUpload__dropzone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.bannerUpload__dropzone p { margin: 0; font-size: .85rem; color: #888; pointer-events: none; }
.bannerUpload__dropzone strong { color: #a01f2e; }

.bannerUpload__btn { padding: 9px 20px; background: #a01f2e; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; align-self: flex-start; }
.bannerUpload__btn:hover { background: #871a27; }
.bannerUpload__btn:disabled { opacity: .6; cursor: not-allowed; }

.bannerFeedback { font-size: .82rem; min-height: 18px; }
.bannerFeedback--ok  { color: #28a745; }
.bannerFeedback--err { color: #dc3545; }

.bannerProgress { display: none; height: 4px; background: #eee; border-radius: 4px; overflow: hidden; margin-top: 4px; }
.bannerProgress__bar { height: 100%; width: 0; background: #a01f2e; transition: width .2s; }
</style>
</head>
<body>

<?php include ROOT . '/admin/includes/header/header.php'; ?>

<div class="adminLayout">
    <?php include ROOT . '/admin/includes/sidebar/sidebar.php'; ?>
    <main class="adminLayout__content">

        <section class="adminInicio">
            <div class="row adminInicio__header">
                <div class="col-md-12">
                    <h2>Banners</h2>
                    <p>Gerencie as imagens de banner do site. Formatos aceitos: JPG, PNG, WebP. Tamanho máximo: 5 MB.</p>
                </div>
            </div>

            <div class="bannersGrid">

                <?php
                $cards = [
                    'home'            => ['título' => 'Banner da Home',                'desc' => 'Exibido no topo da página inicial.'],
                    'doacao-sucesso'  => ['título' => 'Banner — Doação Confirmada',    'desc' => 'Exibido quando o pagamento é aprovado.'],
                    'doacao-pendente' => ['título' => 'Banner — Pagamento em Análise', 'desc' => 'Exibido quando o pagamento está sendo processado.'],
                    'doacao-erro'     => ['título' => 'Banner — Erro no Pagamento',    'desc' => 'Exibido quando o pagamento falha ou é recusado.'],
                ];
                foreach ($cards as $pagina => $info):
                    $arquivo = $banners[$pagina];
                    $previewUrl = BASE_URL . '/' . $arquivo;
                ?>
                <div class="bannerCard">
                    <div class="bannerCard__preview">
                        <img id="preview_<?= $pagina ?>" src="<?= htmlspecialchars($previewUrl) ?>" alt="Banner <?= $pagina ?>">
                    </div>
                    <div class="bannerCard__body">
                        <p class="bannerCard__title"><?= $info['título'] ?></p>
                        <p class="bannerCard__hint"><?= $info['desc'] ?></p>
                        <div class="bannerUpload">
                            <div class="bannerUpload__dropzone" id="drop_<?= $pagina ?>">
                                <input type="file" id="file_<?= $pagina ?>" accept="image/jpeg,image/png,image/webp">
                                <p>Arraste a imagem aqui ou <strong>clique para selecionar</strong></p>
                            </div>
                            <div class="bannerProgress" id="prog_<?= $pagina ?>">
                                <div class="bannerProgress__bar" id="progBar_<?= $pagina ?>"></div>
                            </div>
                            <p class="bannerFeedback" id="feedback_<?= $pagina ?>"></p>
                            <button class="bannerUpload__btn" id="btn_<?= $pagina ?>" data-pagina="<?= $pagina ?>">
                                Salvar banner
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </section>

    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
(function() {
    var BASE = '<?= BASE_URL ?>';

    ['home', 'doacao-sucesso', 'doacao-pendente', 'doacao-erro'].forEach(function(pagina) {
        var fileInput = document.getElementById('file_' + pagina);
        var dropzone  = document.getElementById('drop_' + pagina);
        var btn       = document.getElementById('btn_' + pagina);
        var feedback  = document.getElementById('feedback_' + pagina);
        var preview   = document.getElementById('preview_' + pagina);
        var prog      = document.getElementById('prog_' + pagina);
        var progBar   = document.getElementById('progBar_' + pagina);

        // Drag and drop visual
        dropzone.addEventListener('dragover', function(e) { e.preventDefault(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', function() { dropzone.classList.remove('drag-over'); });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showLocalPreview(fileInput.files[0]);
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files.length) showLocalPreview(this.files[0]);
        });

        function showLocalPreview(file) {
            var reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; };
            reader.readAsDataURL(file);
            dropzone.querySelector('p').innerHTML = '<strong>' + file.name + '</strong> selecionado';
            feedback.textContent = '';
            feedback.className = 'bannerFeedback';
        }

        btn.addEventListener('click', function() {
            if (!fileInput.files.length) {
                feedback.textContent = 'Selecione uma imagem primeiro.';
                feedback.className   = 'bannerFeedback bannerFeedback--err';
                return;
            }

            var formData = new FormData();
            formData.append('pagina', pagina);
            formData.append('banner', fileInput.files[0]);

            btn.disabled     = true;
            btn.textContent  = 'Enviando...';
            prog.style.display = 'block';
            feedback.textContent = '';
            feedback.className   = 'bannerFeedback';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', BASE + '/admin/services/upload_banner.php');

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    progBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
                }
            });

            xhr.addEventListener('load', function() {
                prog.style.display = 'none';
                btn.disabled    = false;
                btn.textContent = 'Salvar banner';
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        feedback.textContent = 'Banner atualizado com sucesso!';
                        feedback.className   = 'bannerFeedback bannerFeedback--ok';
                        preview.src = BASE + '/' + res.arquivo + '?t=' + Date.now();
                        dropzone.querySelector('p').innerHTML = 'Arraste a imagem aqui ou <strong>clique para selecionar</strong>';
                        fileInput.value = '';
                    } else {
                        feedback.textContent = res.message || 'Erro ao salvar.';
                        feedback.className   = 'bannerFeedback bannerFeedback--err';
                    }
                } catch(e) {
                    feedback.textContent = 'Erro inesperado.';
                    feedback.className   = 'bannerFeedback bannerFeedback--err';
                }
            });

            xhr.addEventListener('error', function() {
                prog.style.display = 'none';
                btn.disabled    = false;
                btn.textContent = 'Salvar banner';
                feedback.textContent = 'Erro de comunicação.';
                feedback.className   = 'bannerFeedback bannerFeedback--err';
            });

            xhr.send(formData);
        });
    });
})();
</script>
</body>
</html>
