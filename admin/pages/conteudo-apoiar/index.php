<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$cp = [
    'pretitulo'       => 'Por que nos apoiar?',
    'titulo'          => 'Três frentes <strong>um só propósito</strong>',
    'texto1'          => '<p>Animais ainda sofrem todos os dias em nome da ciência, mesmo quando isso já poderia ser evitado.</p><p>Ao apoiar essa causa, você ajuda a mudar essa realidade de dentro para fora: formando profissionais, influenciando decisões e reduzindo o uso de animais de forma efetiva.</p><p>Cada contribuição gera impacto real. Menos sofrimento. Mais ciência. Mais consciência.</p>',
    't1_titulo'       => '+500 mil',
    't1_texto'        => 'Animais impactados diretamente por ano.',
    't2_titulo'       => 'Atuação',
    't2_texto'        => 'em comissões de éticas e políticas públicas.',
    't3_titulo'       => 'Formação',
    't3_texto'        => 'que transforma e multiplica o impacto',
    'texto2'          => '<p>O Fórum Animal trabalha para transformar a ciência, reduzindo e substituindo o uso de animais em pesquisas e ensino.</p><p>Em vez de atuar apenas nas consequências, atuamos na origem do problema: nas decisões que autorizam o uso de animais.</p><p>Por meio da atuação em comissões de ética (CEUAs), da formação de representantes da sociedade e da promoção de métodos alternativos, conseguimos gerar mudanças reais dentro de universidades, laboratórios e políticas públicas.</p><p>Esse trabalho já contribuiu para avanços importantes, como a proibição de testes em cosméticos e a adoção de métodos mais éticos e eficazes na ciência.</p><p>Ao apoiar essa causa, você não está apenas ajudando animais individualmente, você está ajudando a transformar todo o sistema que impacta milhões deles.</p>',
    'botao_texto'     => 'Saiba mais sobre bioética',
    'botao_link'      => '#bioetica',
];
try { $r = getDbConnection()->query("SELECT * FROM conteudo_apoiar WHERE id = 1")->fetch(); if ($r) $cp = $r; } catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Por que Apoiar — Admin Animal não é carga</title>
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
                    <h2>Por que Apoiar</h2>
                    <p>Seção "Por que nos apoiar?" da página inicial. Use <strong>negrito</strong> para destaque — no site aparece em semi-negrito.</p>
                </div>
            </div>

            <div class="contCard">
                <div class="contCard__head">
                    <h3>Cabeçalho e Textos</h3>
                    <p>As alterações são refletidas imediatamente após salvar.</p>
                </div>
                <div class="contCard__body">
                    <form id="formApoiar">

                        <div class="contField">
                            <label for="iPretitulo">Pré-título <em>(opcional — texto em vermelho acima do título)</em></label>
                            <input id="iPretitulo" name="pretitulo" type="text"
                                   value="<?= htmlspecialchars($cp['pretitulo'] ?? '') ?>"
                                   placeholder="Ex: Por que nos apoiar?">
                        </div>

                        <div class="contField">
                            <label>Título <em>* (use negrito para destacar partes do texto)</em></label>
                            <div class="contQuillWrap contQuillWrap--sm" id="wrapTitulo">
                                <div id="edTitulo"></div>
                            </div>
                            <input type="hidden" name="titulo" id="inpTitulo">
                        </div>

                        <div class="contField">
                            <label>Texto 1 <em>* (parágrafos acima dos tópicos)</em></label>
                            <div class="contQuillWrap contQuillWrap--lg" id="wrapTexto1">
                                <div id="edTexto1"></div>
                            </div>
                            <input type="hidden" name="texto1" id="inpTexto1">
                        </div>

                        <div class="contTopics">
                            <p class="contTopics__title">Tópicos</p>
                            <?php
                            $tops = [
                                1 => [$cp['t1_titulo'], $cp['t1_texto']],
                                2 => [$cp['t2_titulo'], $cp['t2_texto']],
                                3 => [$cp['t3_titulo'], $cp['t3_texto']],
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

                        <div class="contField">
                            <label>Texto 2 <em>* (parágrafos abaixo dos tópicos)</em></label>
                            <div class="contQuillWrap contQuillWrap--lg" id="wrapTexto2">
                                <div id="edTexto2"></div>
                            </div>
                            <input type="hidden" name="texto2" id="inpTexto2">
                        </div>

                        <div class="contRow">
                            <div class="contField">
                                <label for="iBotaoTexto">Texto do botão <em>*</em></label>
                                <input id="iBotaoTexto" name="botao_texto" type="text"
                                       value="<?= htmlspecialchars($cp['botao_texto']) ?>"
                                       placeholder="Ex: Saiba mais sobre bioética">
                            </div>
                            <div class="contField">
                                <label for="iBotaoLink">Link do botão <em>*</em></label>
                                <input id="iBotaoLink" name="botao_link" type="text"
                                       value="<?= htmlspecialchars($cp['botao_link']) ?>"
                                       placeholder="Ex: #bioetica ou /pagina">
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                            <input type="checkbox" id="iBotaoNova" name="botao_nova_aba" value="1"
                                   <?= (($cp['botao_target'] ?? '') === '_blank') ? 'checked' : '' ?>>
                            <label for="iBotaoNova" style="margin:0;font-size:.85rem;font-weight:500;color:#555;cursor:pointer;">Abrir link em nova aba</label>
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

    makeQuill('edTitulo',  'inpTitulo',  <?= json_encode($cp['titulo']) ?>);
    makeQuill('edTexto1',  'inpTexto1',  <?= json_encode($cp['texto1']) ?>);
    makeQuill('edT1titulo','inpT1titulo',<?= json_encode($cp['t1_titulo']) ?>);
    makeQuill('edT1texto', 'inpT1texto', <?= json_encode($cp['t1_texto']) ?>);
    makeQuill('edT2titulo','inpT2titulo',<?= json_encode($cp['t2_titulo']) ?>);
    makeQuill('edT2texto', 'inpT2texto', <?= json_encode($cp['t2_texto']) ?>);
    makeQuill('edT3titulo','inpT3titulo',<?= json_encode($cp['t3_titulo']) ?>);
    makeQuill('edT3texto', 'inpT3texto', <?= json_encode($cp['t3_texto']) ?>);
    makeQuill('edTexto2',  'inpTexto2',  <?= json_encode($cp['texto2']) ?>);

    var quillFields = ['Titulo','Texto1','T1titulo','T1texto','T2titulo','T2texto','T3titulo','T3texto','Texto2'];

    document.getElementById('formApoiar').addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btnSalvar');
        var fb  = document.getElementById('feedback');
        var valid = true;

        quillFields.forEach(function (key) {
            var inp  = document.getElementById('inp' + key);
            var wrap = document.getElementById('wrap' + key);
            if (isEmpty(inp ? inp.value : '')) { if (wrap) wrap.classList.add('is-invalid'); valid = false; }
            else { if (wrap) wrap.classList.remove('is-invalid'); }
        });

        var btTxt  = document.getElementById('iBotaoTexto');
        var btLink = document.getElementById('iBotaoLink');
        if (!btTxt.value.trim())  { btTxt.classList.add('is-invalid');  valid = false; } else { btTxt.classList.remove('is-invalid'); }
        if (!btLink.value.trim()) { btLink.classList.add('is-invalid'); valid = false; } else { btLink.classList.remove('is-invalid'); }

        if (!valid) { fb.textContent = 'Preencha todos os campos obrigatórios.'; fb.className = 'contFeedback contFeedback--err'; return; }

        var fd = new FormData(this);
        fd.append('secao', 'apoiar');

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
