<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

// Redes sociais e botões (configuracoes)
$chaves = [
    'link_instagram', 'link_facebook', 'link_youtube',
    'link_doe_agora', 'link_doe_agora_target',
    'link_seja_voluntario', 'link_seja_voluntario_target',
];
$links = [];
$placeholders = implode(',', array_fill(0, count($chaves), '?'));
$stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ($placeholders)");
$stmt->execute($chaves);
foreach ($stmt->fetchAll() as $row) {
    $links[$row['chave']] = $row['valor'];
}
foreach ($chaves as $c) {
    if (!isset($links[$c])) $links[$c] = '';
}

// Links institucionais (tabela própria)
$stmtInst = $pdo->query("SELECT nome, url, target FROM footer_links_institucional ORDER BY ordem ASC LIMIT 5");
$instRows = $stmtInst->fetchAll();
// Preenche até 5 slots
while (count($instRows) < 5) {
    $instRows[] = ['nome' => '', 'url' => '', 'target' => '_self'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Controle de Links — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
.linksSection { background: #fff; border-radius: 8px; padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.linksSection__title { font-size: 1rem; font-weight: 700; color: #333; margin: 0 0 4px; }
.linksSection__desc  { font-size: .85rem; color: #666; margin: 0 0 24px; }

.linksGroup { margin-bottom: 28px; }
.linksGroup:last-child { margin-bottom: 0; }
.linksGroup__label { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: .9rem; color: #222; margin: 0 0 6px; }
.linksGroup__icon  { font-size: 1.1rem; width: 22px; text-align: center; }

.linksInput { display: flex; align-items: center; gap: 10px; }
.linksInput input[type="url"],
.linksInput input[type="text"] {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: .92rem;
    color: #333;
    transition: border-color .2s;
}
.linksInput input:focus { outline: none; border-color: #a01f2e; }

.linksHint { font-size: .78rem; color: #999; margin-top: 4px; }

.linksTarget { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
.linksTarget__text { font-size: .85rem; color: #555; }

.toggle { position: relative; display: inline-block; width: 52px; height: 28px; flex-shrink: 0; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle__slider { position: absolute; inset: 0; background: #ccc; border-radius: 28px; cursor: pointer; transition: .25s; }
.toggle__slider:before { content: ''; position: absolute; height: 20px; width: 20px; left: 4px; bottom: 4px; background: #fff; border-radius: 50%; transition: .25s; }
.toggle input:checked + .toggle__slider { background: #a01f2e; }
.toggle input:checked + .toggle__slider:before { transform: translateX(24px); }

/* Institucional rows */
.instRow { display: grid; grid-template-columns: 1fr 1.6fr auto; align-items: center; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f0f0f0; }
.instRow:last-child { border-bottom: none; padding-bottom: 0; }
.instRow__num { font-size: .78rem; color: #bbb; font-weight: 700; width: 18px; flex-shrink: 0; }
.instRow input {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 9px 12px;
    font-size: .88rem;
    color: #333;
    transition: border-color .2s;
}
.instRow input:focus { outline: none; border-color: #a01f2e; }
.instRow__toggle { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
.instRow__toggle span { font-size: .78rem; color: #888; }
.instHeader { display: grid; grid-template-columns: 1fr 1.6fr auto; gap: 12px; padding-bottom: 8px; border-bottom: 2px solid #f0f0f0; margin-bottom: 4px; }
.instHeader span { font-size: .75rem; font-weight: 700; color: #aaa; text-transform: uppercase; letter-spacing: .04em; }

.linksSaveBtn {
    background: #a01f2e;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 11px 28px;
    font-size: .92rem;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
}
.linksSaveBtn:hover { background: #831826; }
.linksSaveBtn:disabled { background: #ccc; cursor: not-allowed; }

.linksFeedback { margin-top: 16px; font-size: .85rem; min-height: 20px; }
.linksFeedback--ok  { color: #28a745; }
.linksFeedback--err { color: #dc3545; }

hr.linksDivider { border: none; border-top: 1px solid #f0f0f0; margin: 24px 0; }
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
                    <h2>Controle de Links</h2>
                    <p>Gerencie os links das redes sociais, botões e seção institucional do rodapé.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-9">

                    <!-- Redes Sociais -->
                    <div class="linksSection">
                        <p class="linksSection__title">Redes Sociais</p>
                        <p class="linksSection__desc">Cole a URL completa de cada perfil. Deixe em branco para ocultar o ícone.</p>

                        <div class="linksGroup">
                            <label class="linksGroup__label" for="link_instagram">
                                <i class="icon icon-instagram linksGroup__icon" aria-hidden="true"></i>
                                Instagram
                            </label>
                            <div class="linksInput">
                                <input type="url" id="link_instagram"
                                       placeholder="https://instagram.com/seu_perfil"
                                       value="<?= htmlspecialchars($links['link_instagram']) ?>">
                            </div>
                        </div>

                        <hr class="linksDivider">

                        <div class="linksGroup">
                            <label class="linksGroup__label" for="link_facebook">
                                <i class="icon icon-facebook linksGroup__icon" aria-hidden="true"></i>
                                Facebook
                            </label>
                            <div class="linksInput">
                                <input type="url" id="link_facebook"
                                       placeholder="https://facebook.com/sua_pagina"
                                       value="<?= htmlspecialchars($links['link_facebook']) ?>">
                            </div>
                        </div>

                        <hr class="linksDivider">

                        <div class="linksGroup">
                            <label class="linksGroup__label" for="link_youtube">
                                <i class="icon icon-youtube linksGroup__icon" aria-hidden="true"></i>
                                YouTube
                            </label>
                            <div class="linksInput">
                                <input type="url" id="link_youtube"
                                       placeholder="https://youtube.com/@seu_canal"
                                       value="<?= htmlspecialchars($links['link_youtube']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Botões do Rodapé -->
                    <div class="linksSection">
                        <p class="linksSection__title">Botões do Rodapé</p>
                        <p class="linksSection__desc">Destino de cada botão da faixa vermelha do rodapé.</p>

                        <div class="linksGroup">
                            <label class="linksGroup__label" for="link_doe_agora">
                                <i class="icon icon-maisciencia linksGroup__icon" aria-hidden="true"></i>
                                Botão "Doe Agora"
                            </label>
                            <div class="linksInput">
                                <input type="text" id="link_doe_agora"
                                       placeholder="#calculadora ou https://..."
                                       value="<?= htmlspecialchars($links['link_doe_agora']) ?>">
                            </div>
                            <p class="linksHint">Pode ser um link externo (https://...) ou âncora (#calculadora).</p>
                            <div class="linksTarget">
                                <label class="toggle">
                                    <input type="checkbox" id="link_doe_agora_target"
                                           <?= ($links['link_doe_agora_target'] === '_blank') ? 'checked' : '' ?>>
                                    <span class="toggle__slider"></span>
                                </label>
                                <span class="linksTarget__text">Abrir em nova aba</span>
                            </div>
                        </div>

                        <hr class="linksDivider">

                        <div class="linksGroup">
                            <label class="linksGroup__label" for="link_seja_voluntario">
                                Botão "Seja Voluntário"
                            </label>
                            <div class="linksInput">
                                <input type="text" id="link_seja_voluntario"
                                       placeholder="#apadrinhe ou https://..."
                                       value="<?= htmlspecialchars($links['link_seja_voluntario']) ?>">
                            </div>
                            <p class="linksHint">Pode ser um link externo (https://...) ou âncora (#apadrinhe).</p>
                            <div class="linksTarget">
                                <label class="toggle">
                                    <input type="checkbox" id="link_seja_voluntario_target"
                                           <?= ($links['link_seja_voluntario_target'] === '_blank') ? 'checked' : '' ?>>
                                    <span class="toggle__slider"></span>
                                </label>
                                <span class="linksTarget__text">Abrir em nova aba</span>
                            </div>
                        </div>
                    </div>

                    <!-- Institucional -->
                    <div class="linksSection">
                        <p class="linksSection__title">Institucional</p>
                        <p class="linksSection__desc">Até 5 links exibidos na coluna "Institucional" do rodapé. Preencha o nome e a URL. Linhas vazias são ignoradas.</p>

                        <div class="instHeader">
                            <span>Nome do link</span>
                            <span>URL</span>
                            <span>Nova aba</span>
                        </div>

                        <div id="instList">
                        <?php foreach ($instRows as $i => $row): ?>
                            <div class="instRow">
                                <input type="text" class="inst-nome"
                                       placeholder="Ex: Quem somos"
                                       value="<?= htmlspecialchars($row['nome']) ?>">
                                <input type="text" class="inst-url"
                                       placeholder="https://... ou #ancora"
                                       value="<?= htmlspecialchars($row['url']) ?>">
                                <div class="instRow__toggle">
                                    <label class="toggle">
                                        <input type="checkbox" class="inst-target"
                                               <?= ($row['target'] === '_blank') ? 'checked' : '' ?>>
                                        <span class="toggle__slider"></span>
                                    </label>
                                    <span>Nova aba</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <button class="linksSaveBtn" id="btnSalvarLinks">Salvar alterações</button>
                    <p class="linksFeedback" id="linksFeedback"></p>

                </div>
            </div>
        </section>

    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
document.getElementById('btnSalvarLinks').addEventListener('click', function() {
    var $btn      = this;
    var $feedback = document.getElementById('linksFeedback');

    // Payload redes sociais + botões
    var payloadLinks = {
        link_instagram:              document.getElementById('link_instagram').value.trim(),
        link_facebook:               document.getElementById('link_facebook').value.trim(),
        link_youtube:                document.getElementById('link_youtube').value.trim(),
        link_doe_agora:              document.getElementById('link_doe_agora').value.trim(),
        link_doe_agora_target:       document.getElementById('link_doe_agora_target').checked ? '_blank' : '_self',
        link_seja_voluntario:        document.getElementById('link_seja_voluntario').value.trim(),
        link_seja_voluntario_target: document.getElementById('link_seja_voluntario_target').checked ? '_blank' : '_self'
    };

    // Payload institucional
    var instLinks = [];
    document.querySelectorAll('#instList .instRow').forEach(function(row) {
        instLinks.push({
            nome:   row.querySelector('.inst-nome').value.trim(),
            url:    row.querySelector('.inst-url').value.trim(),
            target: row.querySelector('.inst-target').checked ? '_blank' : '_self'
        });
    });

    $btn.disabled         = true;
    $btn.textContent      = 'Salvando...';
    $feedback.textContent = '';
    $feedback.className   = 'linksFeedback';

    var baseUrl = '<?= BASE_URL ?>';

    Promise.all([
        fetch(baseUrl + '/admin/services/salvar_links.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payloadLinks)
        }).then(function(r) { return r.json(); }),

        fetch(baseUrl + '/admin/services/salvar_links_institucional.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ links: instLinks })
        }).then(function(r) { return r.json(); })
    ])
    .then(function(results) {
        var ok = results.every(function(r) { return r.success; });
        if (ok) {
            $feedback.textContent = 'Links salvos com sucesso!';
            $feedback.className   = 'linksFeedback linksFeedback--ok';
        } else {
            var msg = results.find(function(r) { return !r.success; });
            $feedback.textContent = (msg && msg.message) || 'Erro ao salvar. Tente novamente.';
            $feedback.className   = 'linksFeedback linksFeedback--err';
        }
    })
    .catch(function() {
        $feedback.textContent = 'Erro de comunicação. Verifique sua conexão.';
        $feedback.className   = 'linksFeedback linksFeedback--err';
    })
    .finally(function() {
        $btn.disabled    = false;
        $btn.textContent = 'Salvar alterações';
        setTimeout(function() { $feedback.textContent = ''; }, 4000);
    });
});
</script>
</body>
</html>
