<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$ce = [
    'pretitulo'    => null,
    'titulo'       => 'Título da seção extra 03',
    'texto'        => '<p>Texto da seção.</p>',
    'botao_texto'  => '',
    'botao_link'   => '',
    'botao_target' => '_self',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_extra03 WHERE id = 1")->fetch(); if ($r) $ce = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo Extra 03 — Admin Animal não é carga</title>
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
                    <h2>Conteúdo Extra 03</h2>
                    <p>Seção extra <strong>somente de texto</strong> (sem imagem). O botão de link é opcional.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Textos</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formExtra03">

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

    document.getElementById('formExtra03').addEventListener('submit', function (e) {
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
        fd.append('secao', 'extra03');

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
