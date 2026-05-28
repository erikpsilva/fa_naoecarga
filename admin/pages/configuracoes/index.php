<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mp_modo_teste'");
$stmt->execute();
$row = $stmt->fetch();
$mpModoTeste = $row ? ($row['valor'] === '1') : true;

$blocoKeys = ['bloco_banner','bloco_intro','bloco_apoiar','bloco_calculadora','bloco_apadrinhe','bloco_testemunhos',
              'bloco_extra01','bloco_extra02','bloco_extra03','bloco_extra04'];
$blocos = array_fill_keys($blocoKeys, true);
try {
    $stmt2 = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('" . implode("','", $blocoKeys) . "')");
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $blocos[$r['chave']] = ($r['valor'] === '1');
    }
} catch (Exception $e) {}

$blocoLabels = [
    'bloco_banner'      => ['Banner / Hero',        'Seção principal com imagem de fundo e chamada para doação.'],
    'bloco_intro'       => ['Introdução',            'Seção "O que é bioética" com imagem e pilares.'],
    'bloco_apoiar'      => ['Por que apoiar',        'Seção com cards de impacto e textos de apoio.'],
    'bloco_calculadora' => ['Calculadora de impacto','Calculadora interativa de animais e botões de doação.'],
    'bloco_apadrinhe'   => ['Apadrinhe',             'Seção de apadrinhamento com imagem e botão de doação.'],
    'bloco_testemunhos' => ['Testemunhos',            'Carrossel de depoimentos de alunos.'],
    'bloco_extra01'     => ['Conteúdo Extra 01',      'Seção extra com texto à esquerda e imagem à direita.'],
    'bloco_extra02'     => ['Conteúdo Extra 02',      'Seção extra com imagem à esquerda e texto à direita.'],
    'bloco_extra03'     => ['Conteúdo Extra 03',      'Seção extra somente de texto (sem imagem).'],
    'bloco_extra04'     => ['Conteúdo Extra 04',      'Seção extra com duas colunas de texto lado a lado.'],
];

// Ordem das seções (exceto banner que é sempre fixo no topo)
$secoesOrdenaveisDefault = ['bloco_intro','bloco_apoiar','bloco_calculadora','bloco_apadrinhe','bloco_testemunhos',
                             'bloco_extra01','bloco_extra02','bloco_extra03','bloco_extra04'];
$secoesOrdem = $secoesOrdenaveisDefault;
try {
    $r = $pdo->query("SELECT valor FROM configuracoes WHERE chave = 'secoes_ordem'")->fetchColumn();
    if ($r) {
        $partes = array_filter(array_map('trim', explode(',', $r)));
        // Só usa se contiver exatamente as mesmas chaves (sem a mais, sem a menos)
        if (!array_diff($partes, $secoesOrdenaveisDefault) && !array_diff($secoesOrdenaveisDefault, $partes)) {
            $secoesOrdem = array_values($partes);
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Configurações — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
.configSection { background: #fff; border-radius: 8px; padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.configSection__title { font-size: 1rem; font-weight: 700; color: #333; margin: 0 0 4px; }
.configSection__desc { font-size: .85rem; color: #666; margin: 0 0 24px; }

.configItem { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #f0f0f0; }
.configItem:last-child { border-bottom: none; padding-bottom: 0; }
.configItem__label { font-weight: 600; font-size: .95rem; color: #222; margin: 0 0 2px; }
.configItem__hint  { font-size: .82rem; color: #888; margin: 0; }

.toggle { position: relative; display: inline-block; width: 52px; height: 28px; flex-shrink: 0; }
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle__slider { position: absolute; inset: 0; background: #ccc; border-radius: 28px; cursor: pointer; transition: .25s; }
.toggle__slider:before { content: ''; position: absolute; height: 20px; width: 20px; left: 4px; bottom: 4px; background: #fff; border-radius: 50%; transition: .25s; }
.toggle input:checked + .toggle__slider { background: #a01f2e; }
.toggle input:checked + .toggle__slider:before { transform: translateX(24px); }

.configBadge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .75rem; font-weight: 700; letter-spacing: .03em; }
.configBadge--teste { background: #fff3cd; color: #856404; }
.configBadge--producao { background: #d4edda; color: #155724; }

.configFeedback { margin-top: 8px; font-size: .82rem; min-height: 18px; }
.configFeedback--ok  { color: #28a745; }
.configFeedback--err { color: #dc3545; }

/* Ordenação */
.sortList { list-style: none; margin: 0; padding: 0; }
.sortItem { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid #e8e8e8; border-radius: 8px; background: #fff; margin-bottom: 8px; cursor: grab; user-select: none; transition: box-shadow .15s, background .15s; }
.sortItem:active { cursor: grabbing; }
.sortItem.sortable-ghost { opacity: .4; background: #f5f5f5; }
.sortItem.sortable-drag  { box-shadow: 0 4px 20px rgba(0,0,0,.14); background: #fff; }
.sortItem__handle { color: #bbb; font-size: 1.1rem; flex-shrink: 0; line-height: 1; }
.sortItem__num { font-size: .75rem; font-weight: 700; color: #aaa; min-width: 18px; text-align: center; flex-shrink: 0; }
.sortItem__label { font-weight: 600; font-size: .9rem; color: #333; flex: 1; }
.sortItem__hint { font-size: .78rem; color: #999; }
.sortSave { margin-top: 16px; }
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
                    <h2>Configurações</h2>
                    <p>Gerencie as configurações do sistema.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">

                    <!-- ── Método de Pagamento ── -->
                    <div class="configSection">
                        <p class="configSection__title">Método de Pagamento</p>
                        <p class="configSection__desc">Controle o ambiente do Mercado Pago. No modo teste, nenhum pagamento real é processado.</p>

                        <div class="configItem">
                            <div class="configItem__info">
                                <p class="configItem__label">
                                    Modo de Teste
                                    <span class="configBadge <?= $mpModoTeste ? 'configBadge--teste' : 'configBadge--producao' ?>" id="mpStatusBadge">
                                        <?= $mpModoTeste ? 'TESTE ATIVO' : 'PRODUÇÃO' ?>
                                    </span>
                                </p>
                                <p class="configItem__hint">Quando ativado, usa credenciais de teste do Mercado Pago. Pagamentos não são reais.</p>
                                <p class="configFeedback" id="mpFeedback"></p>
                            </div>
                            <label class="toggle" title="Ligar/desligar modo de teste">
                                <input type="checkbox" id="toggleMpTeste" <?= $mpModoTeste ? 'checked' : '' ?>>
                                <span class="toggle__slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- ── Blocos de conteúdo ── -->
                    <div class="configSection">
                        <p class="configSection__title">Blocos de conteúdo</p>
                        <p class="configSection__desc">Controle quais seções aparecem na página inicial. Desativar um bloco o remove completamente do HTML entregue ao visitante.</p>

                        <?php foreach ($blocoKeys as $chave): ?>
                        <?php [$label, $hint] = $blocoLabels[$chave]; ?>
                        <div class="configItem">
                            <div class="configItem__info">
                                <p class="configItem__label"><?= $label ?></p>
                                <p class="configItem__hint"><?= $hint ?></p>
                                <p class="configFeedback" id="feedback_<?= $chave ?>"></p>
                            </div>
                            <label class="toggle" title="Exibir / ocultar bloco">
                                <input type="checkbox" class="toggleBloco" data-chave="<?= $chave ?>"
                                       <?= $blocos[$chave] ? 'checked' : '' ?>>
                                <span class="toggle__slider"></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- ── Ordenação das seções ── -->
                    <div class="configSection">
                        <p class="configSection__title">Ordenação das seções</p>
                        <p class="configSection__desc">Arraste as seções para definir a ordem em que aparecem na página inicial. O <strong>Banner</strong> é sempre fixo no topo e não pode ser movido.</p>

                        <!-- Banner fixo (não ordenável) -->
                        <div class="sortItem" style="cursor:default; opacity:.55; margin-bottom:8px;">
                            <span class="sortItem__handle">&#9632;</span>
                            <span class="sortItem__num">1</span>
                            <span class="sortItem__label">Banner / Hero <span style="font-size:.75rem;font-weight:400;color:#aaa">(fixo)</span></span>
                        </div>

                        <ul class="sortList" id="sortList">
                            <?php foreach ($secoesOrdem as $i => $chave): ?>
                            <?php [$label, $hint] = $blocoLabels[$chave]; ?>
                            <li class="sortItem" data-chave="<?= $chave ?>">
                                <span class="sortItem__handle">&#9776;</span>
                                <span class="sortItem__num"><?= $i + 2 ?></span>
                                <div style="flex:1">
                                    <div class="sortItem__label"><?= $label ?></div>
                                    <div class="sortItem__hint"><?= $hint ?></div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="sortSave">
                            <button class="contSave" type="button" id="btnSalvarOrdem">Salvar ordem</button>
                            <span class="configFeedback" id="feedbackOrdem"></span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
var BASE = '<?= BASE_URL ?>';

// ── Toggle MP Teste ──────────────────────────────────────────────
document.getElementById('toggleMpTeste').addEventListener('change', function() {
    var ativo     = this.checked ? '1' : '0';
    var $badge    = document.getElementById('mpStatusBadge');
    var $feedback = document.getElementById('mpFeedback');
    $feedback.textContent = 'Salvando...';
    $feedback.className   = 'configFeedback';
    fetch(BASE + '/admin/services/salvar_configuracao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chave: 'mp_modo_teste', valor: ativo })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            $badge.textContent = ativo === '1' ? 'TESTE ATIVO' : 'PRODUÇÃO';
            $badge.className   = 'configBadge ' + (ativo === '1' ? 'configBadge--teste' : 'configBadge--producao');
            $feedback.textContent = 'Salvo com sucesso.';
            $feedback.className   = 'configFeedback configFeedback--ok';
        } else {
            $feedback.textContent = 'Erro ao salvar. Tente novamente.';
            $feedback.className   = 'configFeedback configFeedback--err';
        }
        setTimeout(function() { $feedback.textContent = ''; }, 3000);
    })
    .catch(function() { $feedback.textContent = 'Erro de comunicação.'; $feedback.className = 'configFeedback configFeedback--err'; });
});

// ── Toggle blocos ────────────────────────────────────────────────
document.querySelectorAll('.toggleBloco').forEach(function(el) {
    el.addEventListener('change', function() {
        var chave    = this.dataset.chave;
        var valor    = this.checked ? '1' : '0';
        var feedback = document.getElementById('feedback_' + chave);
        feedback.textContent = 'Salvando...';
        feedback.className   = 'configFeedback';
        fetch(BASE + '/admin/services/salvar_configuracao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chave: chave, valor: valor })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            feedback.textContent = res.success ? 'Salvo.' : 'Erro ao salvar.';
            feedback.className   = 'configFeedback ' + (res.success ? 'configFeedback--ok' : 'configFeedback--err');
            setTimeout(function() { feedback.textContent = ''; }, 2500);
        })
        .catch(function() { feedback.textContent = 'Erro de comunicação.'; feedback.className = 'configFeedback configFeedback--err'; });
    });
});

// ── Sortable ─────────────────────────────────────────────────────
var sortList = document.getElementById('sortList');
Sortable.create(sortList, {
    animation: 150,
    handle: '.sortItem__handle',
    ghostClass: 'sortable-ghost',
    dragClass: 'sortable-drag',
    onUpdate: function() {
        // Atualiza os números de posição
        var items = sortList.querySelectorAll('.sortItem');
        items.forEach(function(item, idx) {
            item.querySelector('.sortItem__num').textContent = idx + 2;
        });
    }
});

document.getElementById('btnSalvarOrdem').addEventListener('click', function() {
    var btn      = this;
    var feedback = document.getElementById('feedbackOrdem');
    var items    = sortList.querySelectorAll('.sortItem');
    var ordem    = [];
    items.forEach(function(item) { ordem.push(item.dataset.chave); });

    btn.disabled = true; btn.textContent = 'Salvando...';
    feedback.textContent = ''; feedback.className = 'configFeedback';

    fetch(BASE + '/admin/services/salvar_configuracao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chave: 'secoes_ordem', valor: ordem.join(',') })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        btn.disabled = false; btn.textContent = 'Salvar ordem';
        feedback.textContent = res.success ? 'Ordem salva com sucesso!' : (res.message || 'Erro ao salvar.');
        feedback.className   = 'configFeedback ' + (res.success ? 'configFeedback--ok' : 'configFeedback--err');
        setTimeout(function() { feedback.textContent = ''; }, 3000);
    })
    .catch(function() {
        btn.disabled = false; btn.textContent = 'Salvar ordem';
        feedback.textContent = 'Erro de comunicação.'; feedback.className = 'configFeedback configFeedback--err';
    });
});
</script>
</body>
</html>
