<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = 'mp_modo_teste'");
$stmt->execute();
$row = $stmt->fetch();
$mpModoTeste = $row ? ($row['valor'] === '1') : true;

$blocoKeys = ['bloco_banner','bloco_intro','bloco_apoiar','bloco_calculadora','bloco_apadrinhe','bloco_testemunhos'];
$blocos = array_fill_keys($blocoKeys, true);
try {
    $stmt2 = $pdo->query("SELECT chave, valor FROM configuracoes WHERE chave IN ('" . implode("','", $blocoKeys) . "')");
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $blocos[$r['chave']] = ($r['valor'] === '1');
    }
} catch (Exception $e) {}

$blocoLabels = [
    'bloco_banner'      => ['Banner / Hero',       'Seção principal com imagem de fundo e chamada para doação.'],
    'bloco_intro'       => ['Introdução',           'Seção "O que é bioética" com imagem e pilares.'],
    'bloco_apoiar'      => ['Por que apoiar',       'Seção com cards de impacto e textos de apoio.'],
    'bloco_calculadora' => ['Calculadora de impacto','Calculadora interativa de animais e botões de doação.'],
    'bloco_apadrinhe'   => ['Apadrinhe',            'Seção de apadrinhamento com imagem e botão de doação.'],
    'bloco_testemunhos' => ['Testemunhos',           'Carrossel de depoimentos de alunos.'],
];
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
.configItem__info {}
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
                </div>

                    <!-- ── Blocos de conteúdo ── -->
                    <div class="configSection">
                        <p class="configSection__title">Blocos de conteúdo</p>
                        <p class="configSection__desc">Controle quais seções aparecem na página inicial do site. Desativar um bloco o remove completamente do HTML entregue ao visitante.</p>

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

            </div>
        </section>

    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
document.getElementById('toggleMpTeste').addEventListener('change', function() {
    var ativo    = this.checked ? '1' : '0';
    var $badge   = document.getElementById('mpStatusBadge');
    var $feedback = document.getElementById('mpFeedback');

    $feedback.textContent = 'Salvando...';
    $feedback.className   = 'configFeedback';

    fetch('<?= BASE_URL ?>/admin/services/salvar_configuracao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ chave: 'mp_modo_teste', valor: ativo })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            if (ativo === '1') {
                $badge.textContent  = 'TESTE ATIVO';
                $badge.className    = 'configBadge configBadge--teste';
            } else {
                $badge.textContent  = 'PRODUÇÃO';
                $badge.className    = 'configBadge configBadge--producao';
            }
            $feedback.textContent = 'Salvo com sucesso.';
            $feedback.className   = 'configFeedback configFeedback--ok';
        } else {
            $feedback.textContent = 'Erro ao salvar. Tente novamente.';
            $feedback.className   = 'configFeedback configFeedback--err';
        }
        setTimeout(function() { $feedback.textContent = ''; }, 3000);
    })
    .catch(function() {
        $feedback.textContent = 'Erro de comunicação.';
        $feedback.className   = 'configFeedback configFeedback--err';
    });
});
document.querySelectorAll('.toggleBloco').forEach(function(el) {
    el.addEventListener('change', function() {
        var chave    = this.dataset.chave;
        var valor    = this.checked ? '1' : '0';
        var feedback = document.getElementById('feedback_' + chave);

        feedback.textContent = 'Salvando...';
        feedback.className   = 'configFeedback';

        fetch('<?= BASE_URL ?>/admin/services/salvar_configuracao.php', {
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
        .catch(function() {
            feedback.textContent = 'Erro de comunicação.';
            feedback.className   = 'configFeedback configFeedback--err';
        });
    });
});
</script>
</body>
</html>
