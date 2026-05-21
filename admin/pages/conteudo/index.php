<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$pdo = getDbConnection();

$bh = [
    'titulo'    => 'Bioética',
    'subtitulo' => 'Nos ajude a ajudar os animais usados na ciência.',
    'texto'     => 'Promovemos a substituição do uso prejudicial de animais em pesquisas, ensino e testes, por um futuro mais justo e consciente.',
];
try { $r = $pdo->query("SELECT * FROM conteudo_banner_home WHERE id = 1")->fetch(); if ($r) $bh = $r; } catch (Exception $e) {}

$ci = [
    'pretitulo'       => '',
    'titulo'          => 'O que é',
    'titulo_destaque' => 'bioética.',
    'texto'           => "É uma ponte que conecta Ciência e Ética\nA Bioética ajuda na construção de futuro um onde o avanço do conhecimento caminhe junto com o avanço moral da sociedade",
    'imagem'          => 'images/imgRato.jpg',
    't1_titulo' => 'Menos sofrimento',  't1_texto' => 'Redução do uso de animais na ciência.',
    't2_titulo' => 'Mais ciência',      't2_texto' => 'Fomento a métodos modernos e eficazes.',
    't3_titulo' => 'Mais consciência',  't3_texto' => 'Formação de pessoas críticas e preparadas.',
];
try { $r = $pdo->query("SELECT * FROM conteudo_intro WHERE id = 1")->fetch(); if ($r) $ci = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
.contCard { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 32px; overflow: hidden; }
.contCard__head { padding: 20px 24px 0; border-bottom: 1px solid #f0f0f0; padding-bottom: 16px; margin-bottom: 24px; }
.contCard__head h3 { font-size: 1rem; font-weight: 700; margin: 0 0 3px; color: #222; }
.contCard__head p  { font-size: .82rem; color: #999; margin: 0; }
.contCard__body { padding: 0 24px 24px; }

.contField { display: flex; flex-direction: column; gap: 5px; margin-bottom: 18px; }
.contField label { font-size: .82rem; font-weight: 600; color: #333; }
.contField label em { font-style: normal; font-weight: 400; color: #bbb; }
.contField input,
.contField textarea { padding: 9px 12px; border: 1px solid #ddd; border-radius: 7px; font-size: .92rem; font-family: inherit; box-sizing: border-box; width: 100%; }
.contField input:focus,
.contField textarea:focus { outline: none; border-color: #a01f2e; }
.contField input.is-invalid,
.contField textarea.is-invalid { border-color: #dc3545; }
.contField textarea { min-height: 90px; resize: vertical; }

.contRow { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.contActions { display: flex; align-items: center; gap: 12px; padding-top: 8px; border-top: 1px solid #f0f0f0; }
.contSave { padding: 9px 24px; background: #a01f2e; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; }
.contSave:hover { background: #871a27; }
.contSave:disabled { opacity: .6; cursor: not-allowed; }
.contFeedback { font-size: .85rem; }
.contFeedback--ok  { color: #28a745; }
.contFeedback--err { color: #dc3545; }

/* Image preview */
.contImgWrap { display: flex; gap: 16px; align-items: flex-start; flex-wrap: wrap; }
.contImgPreview { width: 160px; height: 110px; object-fit: cover; border-radius: 7px; border: 1px solid #ddd; flex-shrink: 0; }
.contImgUpload { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 180px; }
.contImgUpload input[type=file] { font-size: .85rem; }
.contImgUpload p { font-size: .78rem; color: #aaa; margin: 0; }

/* Topics */
.contTopics { background: #fafafa; border-radius: 8px; padding: 18px; margin-bottom: 18px; }
.contTopics__title { font-size: .82rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 14px; }
.contTopicRow { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; margin-bottom: 12px; }
.contTopicRow:last-child { margin-bottom: 0; }

@media (max-width: 640px) {
    .contRow { grid-template-columns: 1fr; }
    .contTopicRow { grid-template-columns: 1fr; }
}
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
                    <h2>Conteúdo</h2>
                    <p>Gerencie o conteúdo exibido na página inicial.</p>
                </div>
            </div>

            <!-- Seção 1: Banner Home -->
            <div class="contCard">
                <div class="contCard__head">
                    <h3>Texto do Banner Home</h3>
                    <p>Exibido na seção hero (topo) da página inicial, sobre o banner de imagem.</p>
                </div>
                <div class="contCard__body">
                    <form id="formBanner">
                        <div class="contField">
                            <label for="bTitulo">Título <em>*</em></label>
                            <input id="bTitulo" name="titulo" type="text" value="<?= htmlspecialchars($bh['titulo']) ?>">
                        </div>
                        <div class="contField">
                            <label for="bSubtitulo">Subtítulo <em>*</em></label>
                            <input id="bSubtitulo" name="subtitulo" type="text" value="<?= htmlspecialchars($bh['subtitulo']) ?>">
                        </div>
                        <div class="contField">
                            <label for="bTexto">Texto <em>*</em></label>
                            <textarea id="bTexto" name="texto"><?= htmlspecialchars($bh['texto']) ?></textarea>
                        </div>
                        <div class="contActions">
                            <button class="contSave" type="submit" id="btnBanner">Salvar</button>
                            <span class="contFeedback" id="fbBanner"></span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Seção 2: Introdução -->
            <div class="contCard">
                <div class="contCard__head">
                    <h3>Conteúdo Introdução</h3>
                    <p>Seção "O que é Bioética" exibida logo abaixo do banner.</p>
                </div>
                <div class="contCard__body">
                    <form id="formIntro" enctype="multipart/form-data">
                        <div class="contField">
                            <label for="iPretitulo">Pré-título <em>(opcional — texto vermelho acima do título)</em></label>
                            <input id="iPretitulo" name="pretitulo" type="text" value="<?= htmlspecialchars($ci['pretitulo'] ?? '') ?>" placeholder="Ex: O que é">
                        </div>
                        <div class="contRow">
                            <div class="contField">
                                <label for="iTitulo">Título <em>*</em></label>
                                <input id="iTitulo" name="titulo" type="text" value="<?= htmlspecialchars($ci['titulo']) ?>" placeholder="Ex: O que é">
                            </div>
                            <div class="contField">
                                <label for="iTituloD">Complemento em destaque <em>* (aparece em negrito após o título)</em></label>
                                <input id="iTituloD" name="titulo_destaque" type="text" value="<?= htmlspecialchars($ci['titulo_destaque']) ?>" placeholder="Ex: bioética.">
                            </div>
                        </div>
                        <div class="contField">
                            <label for="iTexto">Texto <em>*</em></label>
                            <textarea id="iTexto" name="texto"><?= htmlspecialchars($ci['texto']) ?></textarea>
                        </div>
                        <div class="contField">
                            <label>Imagem lateral <em>* (JPG, PNG ou WebP — máx 5 MB)</em></label>
                            <div class="contImgWrap">
                                <img id="introImgPreview" class="contImgPreview" src="<?= BASE_URL . '/' . htmlspecialchars($ci['imagem']) ?>" alt="Preview">
                                <div class="contImgUpload">
                                    <input type="file" name="imagem" id="introImgInput" accept="image/jpeg,image/png,image/webp">
                                    <p>Deixe em branco para manter a imagem atual.</p>
                                </div>
                            </div>
                        </div>

                        <div class="contTopics">
                            <p class="contTopics__title">Tópicos</p>
                            <?php
                            $topics = [
                                1 => ['titulo' => $ci['t1_titulo'], 'texto' => $ci['t1_texto']],
                                2 => ['titulo' => $ci['t2_titulo'], 'texto' => $ci['t2_texto']],
                                3 => ['titulo' => $ci['t3_titulo'], 'texto' => $ci['t3_texto']],
                            ];
                            foreach ($topics as $n => $t):
                            ?>
                            <div class="contTopicRow">
                                <div class="contField" style="margin-bottom:0">
                                    <label>Tópico <?= $n ?> — Título <em>*</em></label>
                                    <input name="t<?= $n ?>_titulo" type="text" value="<?= htmlspecialchars($t['titulo']) ?>">
                                </div>
                                <div class="contField" style="margin-bottom:0">
                                    <label>Tópico <?= $n ?> — Texto <em>*</em></label>
                                    <input name="t<?= $n ?>_texto" type="text" value="<?= htmlspecialchars($t['texto']) ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="contActions">
                            <button class="contSave" type="submit" id="btnIntro">Salvar</button>
                            <span class="contFeedback" id="fbIntro"></span>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
(function () {
    var BASE = '<?= BASE_URL ?>';

    function submitForm(formId, btnId, fbId, secao) {
        var form = document.getElementById(formId);
        var btn  = document.getElementById(btnId);
        var fb   = document.getElementById(fbId);

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Basic validation
            var inputs = form.querySelectorAll('input[name]:not([name="imagem"]):not([name="pretitulo"]), textarea[name]');
            var valid = true;
            inputs.forEach(function (el) {
                if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
                else el.classList.remove('is-invalid');
            });
            if (!valid) {
                fb.textContent = 'Preencha todos os campos obrigatórios.';
                fb.className = 'contFeedback contFeedback--err';
                return;
            }

            var fd = new FormData(form);
            fd.append('secao', secao);

            btn.disabled = true;
            btn.textContent = 'Salvando...';
            fb.textContent = '';
            fb.className = 'contFeedback';

            fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    btn.textContent = 'Salvar';
                    if (res.success) {
                        fb.textContent = 'Salvo com sucesso!';
                        fb.className = 'contFeedback contFeedback--ok';
                        if (res.imagem) {
                            document.getElementById('introImgPreview').src = BASE + '/' + res.imagem + '?t=' + Date.now();
                        }
                    } else {
                        fb.textContent = res.message || 'Erro ao salvar.';
                        fb.className = 'contFeedback contFeedback--err';
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = 'Salvar';
                    fb.textContent = 'Erro de comunicação.';
                    fb.className = 'contFeedback contFeedback--err';
                });
        });
    }

    submitForm('formBanner', 'btnBanner', 'fbBanner', 'banner_home');
    submitForm('formIntro',  'btnIntro',  'fbIntro',  'intro');

    // Image local preview
    document.getElementById('introImgInput').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) { document.getElementById('introImgPreview').src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        }
    });
})();
</script>
</body>
</html>
