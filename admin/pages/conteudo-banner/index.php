<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$bh = [
    'titulo'    => 'Bioética',
    'subtitulo' => 'Nos ajude a ajudar os animais usados na ciência.',
    'texto'     => 'Promovemos a substituição do uso prejudicial de animais em pesquisas, ensino e testes, por um futuro mais justo e consciente.',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_banner_home WHERE id = 1")->fetch(); if ($r) $bh = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Banner Home — Admin Animal não é carga</title>
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
                    <h2>Texto do Banner Home</h2>
                    <p>Título, subtítulo e texto exibidos na seção hero da página inicial. Use <strong>negrito</strong> para destaque — no site aparece em semi-negrito.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Conteúdo do Banner</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formBanner">

                        <div class="contField">
                            <label>Título <em>*</em></label>
                            <div class="contQuillWrap contQuillWrap--sm" id="wrapTitulo">
                                <div id="edTitulo"></div>
                            </div>
                            <input type="hidden" name="titulo" id="inpTitulo">
                        </div>

                        <div class="contField">
                            <label>Subtítulo <em>*</em></label>
                            <div class="contQuillWrap contQuillWrap--sm" id="wrapSubtitulo">
                                <div id="edSubtitulo"></div>
                            </div>
                            <input type="hidden" name="subtitulo" id="inpSubtitulo">
                        </div>

                        <div class="contField">
                            <label>Texto <em>*</em></label>
                            <div class="contQuillWrap contQuillWrap--md" id="wrapTexto">
                                <div id="edTexto"></div>
                            </div>
                            <input type="hidden" name="texto" id="inpTexto">
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
    var BASE = '<?= BASE_URL ?>';
    var toolbar = [['bold', 'italic', 'underline'], ['clean']];

    function makeQuill(edId, inpId, initial) {
        var q = new Quill('#' + edId, { theme: 'snow', modules: { toolbar: toolbar } });
        if (initial && initial.trim()) q.clipboard.dangerouslyPasteHTML(initial);
        q.on('text-change', function () {
            document.getElementById(inpId).value = q.root.innerHTML;
        });
        // init hidden input
        document.getElementById(inpId).value = q.root.innerHTML;
        return q;
    }

    var editors = {
        titulo:    makeQuill('edTitulo',    'inpTitulo',    <?= json_encode($bh['titulo']) ?>),
        subtitulo: makeQuill('edSubtitulo', 'inpSubtitulo', <?= json_encode($bh['subtitulo']) ?>),
        texto:     makeQuill('edTexto',     'inpTexto',     <?= json_encode($bh['texto']) ?>),
    };

    function isEmpty(html) { return html.replace(/<[^>]*>/g, '').trim() === ''; }

    document.getElementById('formBanner').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSalvar');
        var fb  = document.getElementById('feedback');
        var valid = true;

        ['titulo', 'subtitulo', 'texto'].forEach(function (name) {
            var wrap = document.getElementById('wrap' + name.charAt(0).toUpperCase() + name.slice(1));
            if (isEmpty(document.getElementById('inp' + name.charAt(0).toUpperCase() + name.slice(1)).value)) {
                wrap.classList.add('is-invalid'); valid = false;
            } else {
                wrap.classList.remove('is-invalid');
            }
        });

        if (!valid) { fb.textContent = 'Preencha todos os campos.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData();
        fd.append('secao',     'banner_home');
        fd.append('titulo',    document.getElementById('inpTitulo').value);
        fd.append('subtitulo', document.getElementById('inpSubtitulo').value);
        fd.append('texto',     document.getElementById('inpTexto').value);

        btn.disabled = true; btn.textContent = 'Salvando...'; fb.textContent = ''; fb.className = 'contFeedback';

        fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false; btn.textContent = 'Salvar';
                if (res.success) { fb.textContent = 'Salvo com sucesso!'; fb.className = 'contFeedback contFeedback--ok'; }
                else { fb.textContent = res.message || 'Erro ao salvar.'; fb.className = 'contFeedback contFeedback--err'; }
            })
            .catch(function () { btn.disabled = false; btn.textContent = 'Salvar'; fb.textContent = 'Erro de comunicação.'; fb.className = 'contFeedback contFeedback--err'; });
    });
})();
</script>
</body>
</html>
