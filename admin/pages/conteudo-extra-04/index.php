<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$ce = [
    'col1_pretitulo'    => null,
    'col1_titulo'       => 'Título coluna 1',
    'col1_texto'        => '<p>Texto da coluna 1.</p>',
    'col1_botao_texto'  => '',
    'col1_botao_link'   => '',
    'col1_botao_target' => '_self',
    'col2_pretitulo'    => null,
    'col2_titulo'       => 'Título coluna 2',
    'col2_texto'        => '<p>Texto da coluna 2.</p>',
    'col2_botao_texto'  => '',
    'col2_botao_link'   => '',
    'col2_botao_target' => '_self',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_extra04 WHERE id = 1")->fetch(); if ($r) $ce = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo Extra 04 — Admin Animal não é carga</title>
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
                    <h2>Conteúdo Extra 04</h2>
                    <p>Seção extra com <strong>duas colunas de texto</strong> lado a lado. O botão de cada coluna é opcional.</p>
                </div>
            </div>

            <form id="formExtra04">

            <div class="row">
                <!-- Coluna 1 -->
                <div class="col-md-6">
                    <div class="contCard">
                        <div class="contCard__head">
                            <h3>Coluna 1 (esquerda)</h3>
                        </div>
                        <div class="contCard__body">

                            <div class="contField">
                                <label for="iC1Pretitulo">Pré-título <em>(opcional)</em></label>
                                <input id="iC1Pretitulo" name="col1_pretitulo" type="text"
                                       value="<?= htmlspecialchars($ce['col1_pretitulo'] ?? '') ?>"
                                       placeholder="Ex: Saiba mais">
                            </div>

                            <div class="contField">
                                <label>Título <em>*</em></label>
                                <div class="contQuillWrap contQuillWrap--sm" id="wrapC1Titulo">
                                    <div id="edC1Titulo"></div>
                                </div>
                                <input type="hidden" name="col1_titulo" id="inpC1Titulo">
                            </div>

                            <div class="contField">
                                <label>Texto <em>*</em></label>
                                <div class="contQuillWrap contQuillWrap--lg" id="wrapC1Texto">
                                    <div id="edC1Texto"></div>
                                </div>
                                <input type="hidden" name="col1_texto" id="inpC1Texto">
                            </div>

                            <div class="contCard__head" style="margin-top:16px">
                                <h3>Botão <em style="font-weight:400;font-size:.85rem">(opcional)</em></h3>
                            </div>

                            <div class="contField">
                                <label for="iC1BotaoTexto">Texto do botão</label>
                                <input id="iC1BotaoTexto" name="col1_botao_texto" type="text"
                                       value="<?= htmlspecialchars($ce['col1_botao_texto'] ?? '') ?>"
                                       placeholder="Ex: Saiba mais">
                            </div>
                            <div class="contField">
                                <label for="iC1BotaoLink">URL do botão</label>
                                <input id="iC1BotaoLink" name="col1_botao_link" type="text"
                                       value="<?= htmlspecialchars($ce['col1_botao_link'] ?? '') ?>"
                                       placeholder="Ex: https://site.com">
                            </div>
                            <div class="contField">
                                <label>
                                    <input type="checkbox" name="col1_botao_nova_aba"
                                           <?= ($ce['col1_botao_target'] ?? '_self') === '_blank' ? 'checked' : '' ?>>
                                    Abrir link em nova aba
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Coluna 2 -->
                <div class="col-md-6">
                    <div class="contCard">
                        <div class="contCard__head">
                            <h3>Coluna 2 (direita)</h3>
                        </div>
                        <div class="contCard__body">

                            <div class="contField">
                                <label for="iC2Pretitulo">Pré-título <em>(opcional)</em></label>
                                <input id="iC2Pretitulo" name="col2_pretitulo" type="text"
                                       value="<?= htmlspecialchars($ce['col2_pretitulo'] ?? '') ?>"
                                       placeholder="Ex: Saiba mais">
                            </div>

                            <div class="contField">
                                <label>Título <em>*</em></label>
                                <div class="contQuillWrap contQuillWrap--sm" id="wrapC2Titulo">
                                    <div id="edC2Titulo"></div>
                                </div>
                                <input type="hidden" name="col2_titulo" id="inpC2Titulo">
                            </div>

                            <div class="contField">
                                <label>Texto <em>*</em></label>
                                <div class="contQuillWrap contQuillWrap--lg" id="wrapC2Texto">
                                    <div id="edC2Texto"></div>
                                </div>
                                <input type="hidden" name="col2_texto" id="inpC2Texto">
                            </div>

                            <div class="contCard__head" style="margin-top:16px">
                                <h3>Botão <em style="font-weight:400;font-size:.85rem">(opcional)</em></h3>
                            </div>

                            <div class="contField">
                                <label for="iC2BotaoTexto">Texto do botão</label>
                                <input id="iC2BotaoTexto" name="col2_botao_texto" type="text"
                                       value="<?= htmlspecialchars($ce['col2_botao_texto'] ?? '') ?>"
                                       placeholder="Ex: Saiba mais">
                            </div>
                            <div class="contField">
                                <label for="iC2BotaoLink">URL do botão</label>
                                <input id="iC2BotaoLink" name="col2_botao_link" type="text"
                                       value="<?= htmlspecialchars($ce['col2_botao_link'] ?? '') ?>"
                                       placeholder="Ex: https://site.com">
                            </div>
                            <div class="contField">
                                <label>
                                    <input type="checkbox" name="col2_botao_nova_aba"
                                           <?= ($ce['col2_botao_target'] ?? '_self') === '_blank' ? 'checked' : '' ?>>
                                    Abrir link em nova aba
                                </label>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="contActions" style="margin-top:8px">
                <button class="contSave" type="submit" id="btnSalvar">Salvar</button>
                <span class="contFeedback" id="feedback"></span>
            </div>

            </form>

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

    makeQuill('edC1Titulo', 'inpC1Titulo', <?= json_encode($ce['col1_titulo']) ?>);
    makeQuill('edC1Texto',  'inpC1Texto',  <?= json_encode($ce['col1_texto']) ?>);
    makeQuill('edC2Titulo', 'inpC2Titulo', <?= json_encode($ce['col2_titulo']) ?>);
    makeQuill('edC2Texto',  'inpC2Texto',  <?= json_encode($ce['col2_texto']) ?>);

    document.getElementById('formExtra04').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSalvar');
        var fb  = document.getElementById('feedback');
        var valid = true;

        ['C1Titulo','C1Texto','C2Titulo','C2Texto'].forEach(function (key) {
            var inp  = document.getElementById('inp' + key);
            var wrap = document.getElementById('wrap' + key);
            if (isEmpty(inp ? inp.value : '')) { if (wrap) wrap.classList.add('is-invalid'); valid = false; }
            else { if (wrap) wrap.classList.remove('is-invalid'); }
        });

        if (!valid) { fb.textContent = 'Preencha todos os campos obrigatórios.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData(this);
        fd.append('secao', 'extra04');

        btn.disabled = true; btn.textContent = 'Salvando...'; fb.textContent = ''; fb.className = 'contFeedback';

        fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false; btn.textContent = 'Salvar';
                if (res.success) {
                    fb.textContent = 'Salvo com sucesso!'; fb.className = 'contFeedback contFeedback--ok';
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
