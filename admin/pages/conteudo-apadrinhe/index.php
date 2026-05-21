<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$ca = [
    'pretitulo'   => 'Apadrinhe',
    'titulo'      => 'Veja porque sua doação <strong>pode mudar vidas.</strong>',
    'texto'       => '<p>Somos financiados exclusivamente por pessoas que acreditam na ciência sem animais. Atuamos onde as decisões realmente acontecem: nas comissões de ética, regulações e normas científicas.</p><p>Nossa missão é reduzir e substituir o uso de animais em pesquisa e ensino. É dentro das <strong>CEUAs (Comissões de Ética no Uso de Animais)</strong> que esse impacto começa.</p><p>Nosso principal instrumento é o <em>Curso de Formação em Proteção dos Animais nas CEUAs</em>, em parceria com a UFPR. Ele prepara representantes da sociedade civil para atuar nessas comissões, influenciando diretamente a aprovação de projetos com animais.</p><p>Cada representante pode impactar centenas ou até <strong>milhares de animais por ano</strong> em uma única instituição.</p>',
    'imagem'      => 'images/imgCientista.jpg',
    'botao_texto' => 'QUERO APADRINHAR',
    'botao_valor' => 120.00,
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_apadrinhe WHERE id = 1")->fetch(); if ($r) $ca = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo Apadrinhe — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
</head>
<body>

<?php include ROOT . '/admin/includes/header/header.php'; ?>

<div class="adminLayout">
    <?php include ROOT . '/admin/includes/sidebar/sidebar.php'; ?>
    <main class="adminLayout__content">
        <section class="adminInicio">

            <div class="row adminInicio__header">
                <div class="col-md-12">
                    <h2>Conteúdo Apadrinhe</h2>
                    <p>Seção "Apadrinhe" da página inicial. Use <strong>negrito</strong> para destacar partes do título e do texto.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Textos e Imagem</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formApadrinhe" enctype="multipart/form-data">

                        <div class="contField">
                            <label for="iPretitulo">Pré-título <em>(opcional — texto em vermelho acima do título)</em></label>
                            <input id="iPretitulo" name="pretitulo" type="text"
                                   value="<?= htmlspecialchars($ca['pretitulo'] ?? '') ?>"
                                   placeholder="Ex: Apadrinhe">
                        </div>

                        <div class="contField">
                            <label>Título <em>* (use negrito para destacar partes do texto)</em></label>
                            <div class="contQuillWrap contQuillWrap--sm" id="wrapTitulo">
                                <div id="edTitulo"></div>
                            </div>
                            <input type="hidden" name="titulo" id="inpTitulo">
                        </div>

                        <div class="contField">
                            <label>Texto <em>*</em></label>
                            <div class="contQuillWrap contQuillWrap--lg" id="wrapTexto">
                                <div id="edTexto"></div>
                            </div>
                            <input type="hidden" name="texto" id="inpTexto">
                        </div>

                        <div class="contRow">
                            <div class="contField">
                                <label for="iBotaoTexto">Texto do botão <em>*</em></label>
                                <input id="iBotaoTexto" name="botao_texto" type="text"
                                       value="<?= htmlspecialchars($ca['botao_texto']) ?>"
                                       placeholder="Ex: QUERO APADRINHAR">
                            </div>
                            <div class="contField">
                                <label for="iBotaoValor">Valor cobrado pelo botão <em>* (em reais)</em></label>
                                <input id="iBotaoValor" name="botao_valor" type="number" min="1" step="0.01"
                                       value="<?= htmlspecialchars(number_format((float)($ca['botao_valor'] ?? 120), 2, '.', '')) ?>"
                                       placeholder="Ex: 120">
                            </div>
                        </div>

                        <div class="contField">
                            <label>Imagem lateral <em>* (JPG, PNG ou WebP — máx 5 MB)</em></label>
                            <div class="contImgWrap">
                                <img id="apadrImgPreview" class="contImgPreview"
                                     src="<?= BASE_URL . '/' . htmlspecialchars($ca['imagem']) ?>" alt="Preview">
                                <div>
                                    <input type="file" name="imagem" id="apadrImgInput"
                                           accept="image/jpeg,image/png,image/webp">
                                    <p class="contImgHint">Deixe em branco para manter a imagem atual.</p>
                                </div>
                            </div>
                        </div>

                        <div class="contActions">
                            <button class="contSave" type="submit" id="btnSalvar">Salvar</button>
                            <span class="contFeedback" id="feedback"></span>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var BASE    = '<?= BASE_URL ?>';
    var toolbar = [['bold', 'italic', 'underline'], ['clean']];

    function makeQuill(edId, inpId, initial) {
        var q = new Quill('#' + edId, { theme: 'snow', modules: { toolbar: toolbar } });
        if (initial && initial.trim()) q.clipboard.dangerouslyPasteHTML(initial);
        var inp = document.getElementById(inpId);
        inp.value = q.root.innerHTML;
        q.on('text-change', function () { inp.value = q.root.innerHTML; });
        return q;
    }

    function isEmpty(html) { return html.replace(/<[^>]*>/g, '').trim() === ''; }

    makeQuill('edTitulo', 'inpTitulo', <?= json_encode($ca['titulo']) ?>);
    makeQuill('edTexto',  'inpTexto',  <?= json_encode($ca['texto']) ?>);

    document.getElementById('apadrImgInput').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) { document.getElementById('apadrImgPreview').src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('formApadrinhe').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSalvar');
        var fb  = document.getElementById('feedback');
        var valid = true;

        ['Titulo', 'Texto'].forEach(function (key) {
            var inp  = document.getElementById('inp' + key);
            var wrap = document.getElementById('wrap' + key);
            if (isEmpty(inp ? inp.value : '')) { if (wrap) wrap.classList.add('is-invalid'); valid = false; }
            else { if (wrap) wrap.classList.remove('is-invalid'); }
        });

        ['iBotaoTexto', 'iBotaoValor'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el || !el.value.trim() || (id === 'iBotaoValor' && parseFloat(el.value) <= 0)) {
                el && el.classList.add('is-invalid'); valid = false;
            } else { el && el.classList.remove('is-invalid'); }
        });

        if (!valid) { fb.textContent = 'Preencha todos os campos obrigatórios.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData(this);
        fd.append('secao', 'apadrinhe');

        btn.disabled = true; btn.textContent = 'Salvando...'; fb.textContent = ''; fb.className = 'contFeedback';

        fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false; btn.textContent = 'Salvar';
                if (res.success) {
                    fb.textContent = 'Salvo com sucesso!'; fb.className = 'contFeedback contFeedback--ok';
                    if (res.imagem) document.getElementById('apadrImgPreview').src = BASE + '/' + res.imagem + '?t=' + Date.now();
                } else {
                    fb.textContent = res.message || 'Erro ao salvar.'; fb.className = 'contFeedback contFeedback--err';
                }
            })
            .catch(function () { btn.disabled = false; btn.textContent = 'Salvar'; fb.textContent = 'Erro de comunicação.'; fb.className = 'contFeedback contFeedback--err'; });
    });
})();
</script>
</body>
</html>
