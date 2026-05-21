<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$ci = [
    'pretitulo'       => '',
    'titulo'          => 'O que é <strong>bioética.</strong>',
    'texto'           => "É uma ponte que conecta Ciência e Ética\nA Bioética ajuda na construção de futuro um onde o avanço do conhecimento caminhe junto com o avanço moral da sociedade",
    'imagem'          => 'images/imgRato.jpg',
    't1_titulo' => 'Menos sofrimento',  't1_texto' => 'Redução do uso de animais na ciência.',
    't2_titulo' => 'Mais ciência',      't2_texto' => 'Fomento a métodos modernos e eficazes.',
    't3_titulo' => 'Mais consciência',  't3_texto' => 'Formação de pessoas críticas e preparadas.',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_intro WHERE id = 1")->fetch(); if ($r) $ci = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Conteúdo Introdução — Admin Animal não é carga</title>
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
                    <h2>Conteúdo Introdução</h2>
                    <p>Seção "O que é Bioética" da página inicial. Use <strong>negrito</strong> para destaque — no site aparece em semi-negrito.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Textos e Imagem</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formIntro" enctype="multipart/form-data">

                        <div class="contField">
                            <label for="iPretitulo">Pré-título <em>(opcional — texto em vermelho acima do título)</em></label>
                            <input id="iPretitulo" name="pretitulo" type="text"
                                   value="<?= htmlspecialchars($ci['pretitulo'] ?? '') ?>"
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
                                <img id="introImgPreview" class="contImgPreview"
                                     src="<?= BASE_URL . '/' . htmlspecialchars($ci['imagem']) ?>" alt="Preview">
                                <div>
                                    <input type="file" name="imagem" id="introImgInput"
                                           accept="image/jpeg,image/png,image/webp">
                                    <p class="contImgHint">Deixe em branco para manter a imagem atual.</p>
                                </div>
                            </div>
                        </div>

                        <div class="contTopics">
                            <p class="contTopics__title">Tópicos</p>
                            <?php
                            $tops = [
                                1 => [$ci['t1_titulo'], $ci['t1_texto']],
                                2 => [$ci['t2_titulo'], $ci['t2_texto']],
                                3 => [$ci['t3_titulo'], $ci['t3_texto']],
                            ];
                            foreach ($tops as $n => [$tt, $tx]):
                            ?>
                            <div class="contTopicRow">
                                <div class="contField">
                                    <label>Tópico <?= $n ?> — Título <em>*</em></label>
                                    <div class="contQuillWrap contQuillWrap--sm" id="wrapT<?= $n ?>titulo">
                                        <div id="edT<?= $n ?>titulo"></div>
                                    </div>
                                    <input type="hidden" name="t<?= $n ?>_titulo" id="inpT<?= $n ?>titulo">
                                </div>
                                <div class="contField">
                                    <label>Tópico <?= $n ?> — Texto <em>*</em></label>
                                    <div class="contQuillWrap contQuillWrap--sm" id="wrapT<?= $n ?>texto">
                                        <div id="edT<?= $n ?>texto"></div>
                                    </div>
                                    <input type="hidden" name="t<?= $n ?>_texto" id="inpT<?= $n ?>texto">
                                </div>
                            </div>
                            <?php endforeach; ?>
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

    // Initialize all Quill editors
    var fields = {
        Titulo:  makeQuill('edTitulo',  'inpTitulo',  <?= json_encode($ci['titulo']) ?>),
        Texto:   makeQuill('edTexto',   'inpTexto',   <?= json_encode($ci['texto']) ?>),
        T1titulo: makeQuill('edT1titulo', 'inpT1titulo', <?= json_encode($ci['t1_titulo']) ?>),
        T1texto:  makeQuill('edT1texto',  'inpT1texto',  <?= json_encode($ci['t1_texto']) ?>),
        T2titulo: makeQuill('edT2titulo', 'inpT2titulo', <?= json_encode($ci['t2_titulo']) ?>),
        T2texto:  makeQuill('edT2texto',  'inpT2texto',  <?= json_encode($ci['t2_texto']) ?>),
        T3titulo: makeQuill('edT3titulo', 'inpT3titulo', <?= json_encode($ci['t3_titulo']) ?>),
        T3texto:  makeQuill('edT3texto',  'inpT3texto',  <?= json_encode($ci['t3_texto']) ?>),
    };

    // Image preview
    document.getElementById('introImgInput').addEventListener('change', function () {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) { document.getElementById('introImgPreview').src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Form submit
    document.getElementById('formIntro').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSalvar');
        var fb  = document.getElementById('feedback');
        var valid = true;

        ['Titulo', 'Texto', 'T1titulo', 'T1texto', 'T2titulo', 'T2texto', 'T3titulo', 'T3texto'].forEach(function (key) {
            var inp  = document.getElementById('inp' + key);
            var wrap = document.getElementById('wrap' + key);
            if (isEmpty(inp ? inp.value : '')) { if (wrap) wrap.classList.add('is-invalid'); valid = false; }
            else { if (wrap) wrap.classList.remove('is-invalid'); }
        });

        if (!valid) { fb.textContent = 'Preencha todos os campos obrigatórios.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData(this);
        fd.append('secao', 'intro');

        btn.disabled = true; btn.textContent = 'Salvando...'; fb.textContent = ''; fb.className = 'contFeedback';

        fetch(BASE + '/admin/services/salvar_conteudo.php', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false; btn.textContent = 'Salvar';
                if (res.success) {
                    fb.textContent = 'Salvo com sucesso!'; fb.className = 'contFeedback contFeedback--ok';
                    if (res.imagem) document.getElementById('introImgPreview').src = BASE + '/' + res.imagem + '?t=' + Date.now();
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
