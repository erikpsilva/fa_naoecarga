<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$ce = [
    'pretitulo'    => null,
    'titulo'       => 'Título da seção extra 02',
    'texto'        => '<p>Texto da seção.</p>',
    'imagem'       => 'images/imgCientista.jpg',
    'imagem_col'   => 5,
    'botao_texto'  => '',
    'botao_link'   => '',
    'botao_target' => '_self',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_extra02 WHERE id = 1")->fetch(); if ($r) $ce = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo Extra 02 — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
.gridPicker { display: flex; gap: 4px; margin-top: 8px; }
.gridPicker__col { flex: 1; padding: 6px 2px; border: 2px solid #ddd; border-radius: 4px; background: #f8f8f8; font-size: .8rem; font-weight: 600; color: #999; cursor: pointer; text-align: center; transition: .15s; }
.gridPicker__col:hover { border-color: #a01f2e; color: #a01f2e; }
.gridPicker__col.is-active { border-color: #a01f2e; background: #a01f2e; color: #fff; }
.gridPreview { display: flex; height: 36px; border-radius: 6px; overflow: hidden; margin-top: 10px; border: 1px solid #e0e0e0; }
.gridPreview__text { background: #e8f0fe; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; color: #3a5bba; transition: width .2s; }
.gridPreview__img  { background: #a01f2e; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; color: #fff; transition: width .2s; }
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
                    <h2>Conteúdo Extra 02</h2>
                    <p>Seção extra com <strong>imagem à esquerda e texto à direita</strong>. O botão de link é opcional.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Textos e Imagem</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formExtra02" enctype="multipart/form-data">

                        <div class="contField">
                            <label for="iPretitulo">Pré-título <em>(opcional — texto em vermelho acima do título)</em></label>
                            <input id="iPretitulo" name="pretitulo" type="text"
                                   value="<?= htmlspecialchars($ce['pretitulo'] ?? '') ?>"
                                   placeholder="Ex: Saiba mais">
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

                        <div class="contField">
                            <label>Imagem lateral <em>* (JPG, PNG ou WebP — máx 5 MB)</em></label>
                            <div class="contImgWrap">
                                <img id="imgPreview" class="contImgPreview"
                                     src="<?= BASE_URL . '/' . htmlspecialchars($ce['imagem']) ?>" alt="Preview">
                                <div>
                                    <input type="file" name="imagem" id="imgInput"
                                           accept="image/jpeg,image/png,image/webp">
                                    <p class="contImgHint">Deixe em branco para manter a imagem atual.</p>
                                </div>
                            </div>
                        </div>

                        <div class="contField">
                            <label>Tamanho da imagem no grid <em>(colunas ocupadas pela imagem — o texto preenche o restante)</em></label>
                            <input type="hidden" name="imagem_col" id="inpImagemCol" value="<?= (int)($ce['imagem_col'] ?? 5) ?>">
                            <div class="gridPicker" id="gridPicker">
                                <?php for ($c = 1; $c <= 12; $c++): ?>
                                <button type="button" class="gridPicker__col <?= ($c == ($ce['imagem_col'] ?? 5)) ? 'is-active' : '' ?>" data-col="<?= $c ?>"><?= $c ?></button>
                                <?php endfor; ?>
                            </div>
                            <div class="gridPreview" id="gridPreview">
                                <div class="gridPreview__img" id="gpImg">Imagem</div>
                                <div class="gridPreview__text" id="gpText">Texto</div>
                            </div>
                            <p class="contImgHint" id="gridHint"></p>
                        </div>

                        <div class="contCard__head" style="margin-top:24px">
                            <h3>Botão de link <em style="font-weight:400;font-size:.85rem">(opcional — deixe em branco para ocultar o botão)</em></h3>
                        </div>

                        <div class="contRow">
                            <div class="contField">
                                <label for="iBotaoTexto">Texto do botão</label>
                                <input id="iBotaoTexto" name="botao_texto" type="text"
                                       value="<?= htmlspecialchars($ce['botao_texto'] ?? '') ?>"
                                       placeholder="Ex: Saiba mais">
                            </div>
                            <div class="contField">
                                <label for="iBotaoLink">URL do botão</label>
                                <input id="iBotaoLink" name="botao_link" type="text"
                                       value="<?= htmlspecialchars($ce['botao_link'] ?? '') ?>"
                                       placeholder="Ex: https://site.com ou #ancora">
                            </div>
                        </div>

                        <div class="contField">
                            <label>
                                <input type="checkbox" name="botao_nova_aba" id="iBotaoNovaAba"
                                       <?= ($ce['botao_target'] ?? '_self') === '_blank' ? 'checked' : '' ?>>
                                Abrir link em nova aba
                            </label>
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

    makeQuill('edTitulo', 'inpTitulo', <?= json_encode($ce['titulo']) ?>);
    makeQuill('edTexto',  'inpTexto',  <?= json_encode($ce['texto']) ?>);

    // Grid picker — no Extra 02 a imagem fica à esquerda
    (function () {
        var inp     = document.getElementById('inpImagemCol');
        var buttons = document.querySelectorAll('.gridPicker__col');
        var gpText  = document.getElementById('gpText');
        var gpImg   = document.getElementById('gpImg');
        var hint    = document.getElementById('gridHint');

        function updatePreview(col) {
            var textCol = 12 - col;
            gpImg.style.width  = (col / 12 * 100).toFixed(2) + '%';
            gpText.style.width = (textCol / 12 * 100).toFixed(2) + '%';
            gpImg.textContent  = 'Imagem (col-' + col + ')';
            gpText.textContent = 'Texto (col-' + textCol + ')';
            hint.textContent   = 'Imagem: col-lg-' + col + ' | Texto: col-lg-' + textCol;
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var col = parseInt(this.dataset.col);
                buttons.forEach(function (b) { b.classList.remove('is-active'); });
                this.classList.add('is-active');
                inp.value = col;
                updatePreview(col);
            });
        });

        updatePreview(parseInt(inp.value) || 5);
    })();

    document.getElementById('imgInput').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) { document.getElementById('imgPreview').src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('formExtra02').addEventListener('submit', function (e) {
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

        if (!valid) { fb.textContent = 'Preencha todos os campos obrigatórios.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData(this);
        fd.append('secao', 'extra02');

        btn.disabled = true; btn.textContent = 'Salvando...'; fb.textContent = ''; fb.className = 'contFeedback';

        fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false; btn.textContent = 'Salvar';
                if (res.success) {
                    fb.textContent = 'Salvo com sucesso!'; fb.className = 'contFeedback contFeedback--ok';
                    if (res.imagem) document.getElementById('imgPreview').src = BASE + '/' + res.imagem + '?t=' + Date.now();
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
