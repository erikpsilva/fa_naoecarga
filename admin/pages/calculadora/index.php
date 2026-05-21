<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

$cfg = [
    'animal_1_nome' => 'Roedores', 'animal_1_pct' => 65.00, 'animal_1_imagem' => 'uploads/animais/imgRato.png',
    'animal_2_nome' => 'Peixes',   'animal_2_pct' => 20.00, 'animal_2_imagem' => 'uploads/animais/imgPeixe.png',
    'animal_3_nome' => 'Galinhas', 'animal_3_pct' =>  7.00, 'animal_3_imagem' => 'uploads/animais/imgGalinha.png',
    'animal_4_nome' => 'Outros',   'animal_4_pct' =>  8.00, 'animal_4_imagem' => 'uploads/animais/imgOutros.png',
    'valor_btn_1' => 30, 'valor_btn_2' => 60, 'valor_btn_3' => 120,
    'custo_por_animal' => 15.00,
];
try {
    $r = $pdo->query("SELECT * FROM calculadora_config WHERE id = 1")->fetch();
    if ($r) $cfg = $r;
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Calculadora de Animais — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
.calcSection { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 28px 32px; margin-bottom: 24px; }
.calcSection__title { font-size: 1rem; font-weight: 700; color: #222; margin: 0 0 6px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
.calcSection__sub { font-size: .83rem; color: #888; margin: 0 0 20px; }

/* ── Cards de animal ── */
.animalCards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
@media (max-width: 1100px) { .animalCards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px)  { .animalCards { grid-template-columns: 1fr; } }

.animalCard { border: 1px solid #e8e8e8; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; }
.animalCard__header { display: flex; align-items: center; gap: 8px; padding: 12px 14px 10px; border-bottom: 1px solid #f0f0f0; }
.animalCard__badge { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; color: #fff; flex-shrink: 0; }
.animalCard__badge--1 { background: #c72b35; }
.animalCard__badge--2 { background: #3b6ea8; }
.animalCard__badge--3 { background: #2e8b57; }
.animalCard__badge--4 { background: #8b6914; }
.animalCard__title { font-size: .82rem; font-weight: 700; color: #444; }

.animalCard__imgWrap { position: relative; background: #f9f9f9; padding: 16px; display: flex; align-items: center; justify-content: center; min-height: 110px; cursor: pointer; }
.animalCard__imgWrap:hover .animalCard__imgOverlay { opacity: 1; }
.animalCard__img { width: 80px; height: 80px; object-fit: contain; display: block; }
.animalCard__imgOverlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .2s; border-radius: 0; }
.animalCard__imgOverlay span { color: #fff; font-size: .78rem; font-weight: 600; text-align: center; padding: 0 8px; }
.animalCard__fileInput { display: none; }

.animalCard__body { padding: 14px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
.animalCard__fieldLabel { font-size: .75rem; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.animalCard__input { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: .88rem; box-sizing: border-box; }
.animalCard__input:focus { outline: none; border-color: #a01f2e; }

.pctInputWrap { display: flex; align-items: center; gap: 6px; }
.pctInputWrap input { width: 80px; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: .88rem; text-align: right; }
.pctInputWrap input:focus { outline: none; border-color: #a01f2e; }
.pctInputWrap span { font-size: .88rem; color: #555; font-weight: 600; }

.animalCard__imgStatus { font-size: .72rem; min-height: 16px; text-align: center; padding: 0 14px 10px; }
.animalCard__imgStatus--ok  { color: #155724; }
.animalCard__imgStatus--err { color: #721c24; }

/* ── Soma ── */
.pctSoma { display: flex; align-items: center; gap: 10px; margin-top: 18px; padding: 12px 16px; border-radius: 6px; font-size: .9rem; font-weight: 600; transition: background .2s; }
.pctSoma--ok  { background: #d4edda; color: #155724; }
.pctSoma--err { background: #f8d7da; color: #721c24; }
.pctSoma__bar { flex: 1; height: 6px; border-radius: 3px; background: #eee; overflow: hidden; }
.pctSoma__fill { height: 100%; border-radius: 3px; transition: width .25s, background .25s; }

/* ── Valores dos botões ── */
.btnValoresGrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.btnValoresGrid label { font-size: .8rem; font-weight: 600; color: #666; display: block; margin-bottom: 4px; }
.btnValoresGrid input[type=number] { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: .9rem; box-sizing: border-box; }
.btnValoresGrid input:focus { outline: none; border-color: #a01f2e; }

/* ── Custo por animal ── */
.custoWrap { display: flex; align-items: center; gap: 10px; }
.custoWrap input[type=number] { width: 140px; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: .9rem; }
.custoWrap input:focus { outline: none; border-color: #a01f2e; }
.custoWrap span { font-size: .85rem; color: #888; }

/* ── Save ── */
.btnSalvar { padding: 10px 28px; background: #a01f2e; color: #fff; border: none; border-radius: 7px; font-size: .95rem; font-weight: 600; cursor: pointer; margin-top: 8px; }
.btnSalvar:hover { background: #871a27; }
.btnSalvar:disabled { opacity: .6; cursor: not-allowed; }

.formAlert { padding: 10px 16px; border-radius: 6px; font-size: .88rem; margin-bottom: 16px; }
.formAlert--success { background: #d4edda; color: #155724; }
.formAlert--error   { background: #f8d7da; color: #721c24; }
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
                    <h2>Calculadora de Animais</h2>
                    <p>Configure os animais, porcentagens, imagens, valores dos botões e custo por animal.</p>
                </div>
            </div>

            <div id="calcAlert"></div>

            <!-- ── Animais ── -->
            <div class="calcSection">
                <p class="calcSection__title">Animais</p>
                <p class="calcSection__sub">Para cada animal defina a imagem, o nome exibido e a porcentagem da doação destinada a ele. A soma deve ser exatamente 100%.</p>

                <div class="animalCards">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="animalCard">
                        <div class="animalCard__header">
                            <span class="animalCard__badge animalCard__badge--<?= $i ?>"><?= $i ?></span>
                            <span class="animalCard__title">Animal <?= $i ?></span>
                        </div>

                        <div class="animalCard__imgWrap" id="imgWrap<?= $i ?>" title="Clique para trocar a imagem">
                            <img class="animalCard__img" id="imgPreview<?= $i ?>"
                                 src="<?= BASE_URL . '/' . htmlspecialchars($cfg['animal_' . $i . '_imagem']) ?>?v=<?= time() ?>"
                                 alt="<?= htmlspecialchars($cfg['animal_' . $i . '_nome']) ?>">
                            <div class="animalCard__imgOverlay"><span>Clique para<br>trocar imagem</span></div>
                            <input type="file" class="animalCard__fileInput" accept="image/jpeg,image/png,image/webp"
                                   id="fileInput<?= $i ?>" data-slot="<?= $i ?>">
                        </div>
                        <p class="animalCard__imgStatus" id="imgStatus<?= $i ?>"></p>

                        <div class="animalCard__body">
                            <div>
                                <p class="animalCard__fieldLabel">Nome do animal</p>
                                <input class="animalCard__input" type="text"
                                       id="animal_<?= $i ?>_nome"
                                       value="<?= htmlspecialchars($cfg['animal_' . $i . '_nome']) ?>"
                                       placeholder="Ex: Roedores" maxlength="50">
                            </div>
                            <div>
                                <p class="animalCard__fieldLabel">Porcentagem</p>
                                <div class="pctInputWrap">
                                    <input type="number" id="animal_<?= $i ?>_pct"
                                           value="<?= number_format((float)$cfg['animal_' . $i . '_pct'], 2, '.', '') ?>"
                                           min="0" max="100" step="0.01">
                                    <span>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="pctSoma pctSoma--ok" id="pctSoma">
                    <span id="pctSomaTexto">Soma: 100%</span>
                    <div class="pctSoma__bar"><div class="pctSoma__fill" id="pctSomaFill" style="width:100%;background:#28a745"></div></div>
                </div>
            </div>

            <!-- ── Valores dos botões ── -->
            <div class="calcSection">
                <p class="calcSection__title">Valores dos botões de doação</p>
                <p class="calcSection__sub">Três valores sugeridos exibidos como botões na calculadora. O botão 2 vem selecionado por padrão.</p>
                <div class="btnValoresGrid">
                    <div>
                        <label for="valor_btn_1">Botão 1 (R$)</label>
                        <input type="number" id="valor_btn_1" value="<?= (int)$cfg['valor_btn_1'] ?>" min="1" step="1">
                    </div>
                    <div>
                        <label for="valor_btn_2">Botão 2 — padrão ativo (R$)</label>
                        <input type="number" id="valor_btn_2" value="<?= (int)$cfg['valor_btn_2'] ?>" min="1" step="1">
                    </div>
                    <div>
                        <label for="valor_btn_3">Botão 3 (R$)</label>
                        <input type="number" id="valor_btn_3" value="<?= (int)$cfg['valor_btn_3'] ?>" min="1" step="1">
                    </div>
                </div>
            </div>

            <!-- ── Custo por animal ── -->
            <div class="calcSection">
                <p class="calcSection__title">Custo por animal</p>
                <p class="calcSection__sub">Valor em R$ usado para calcular quantos animais são impactados pela doação.</p>
                <div class="custoWrap">
                    <span>R$</span>
                    <input type="number" id="custo_por_animal" value="<?= number_format((float)$cfg['custo_por_animal'], 2, '.', '') ?>" min="0.01" step="0.01">
                    <span>por animal</span>
                </div>
            </div>

            <button class="btnSalvar" id="btnSalvar" type="button">Salvar configurações</button>

        </section>

    </main>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
(function () {
    var BASE_URL = '<?= BASE_URL ?>';

    // ── Soma de porcentagens ──────────────────────────────────────────────────
    function getPct(i) { return parseFloat(document.getElementById('animal_' + i + '_pct').value) || 0; }

    function updateSoma() {
        var soma = 0;
        for (var i = 1; i <= 4; i++) soma += getPct(i);
        soma = Math.round(soma * 100) / 100;

        var el   = document.getElementById('pctSoma');
        var txt  = document.getElementById('pctSomaTexto');
        var fill = document.getElementById('pctSomaFill');

        txt.textContent  = 'Soma: ' + soma.toFixed(2).replace('.', ',') + '%';
        fill.style.width = Math.min(soma, 100) + '%';

        if (soma === 100) {
            el.className         = 'pctSoma pctSoma--ok';
            fill.style.background = '#28a745';
        } else {
            el.className         = 'pctSoma pctSoma--err';
            fill.style.background = soma > 100 ? '#dc3545' : '#ffc107';
        }

        document.getElementById('btnSalvar').disabled = soma !== 100;
    }

    for (var i = 1; i <= 4; i++) {
        document.getElementById('animal_' + i + '_pct').addEventListener('input', updateSoma);
    }
    updateSoma();

    // ── Upload de imagem ──────────────────────────────────────────────────────
    for (var s = 1; s <= 4; s++) {
        (function(slot) {
            var wrap    = document.getElementById('imgWrap'    + slot);
            var input   = document.getElementById('fileInput'  + slot);
            var preview = document.getElementById('imgPreview' + slot);
            var status  = document.getElementById('imgStatus'  + slot);

            wrap.addEventListener('click', function() { input.click(); });

            input.addEventListener('change', function() {
                var file = this.files[0];
                if (!file) return;

                status.className   = 'animalCard__imgStatus';
                status.textContent = 'Enviando...';

                var formData = new FormData();
                formData.append('slot', slot);
                formData.append('imagem', file);

                fetch(BASE_URL + '/admin/services/upload_animal_imagem.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        preview.src          = BASE_URL + '/' + res.path + '?v=' + Date.now();
                        status.className     = 'animalCard__imgStatus animalCard__imgStatus--ok';
                        status.textContent   = 'Imagem atualizada!';
                    } else {
                        status.className     = 'animalCard__imgStatus animalCard__imgStatus--err';
                        status.textContent   = res.message || 'Erro ao enviar.';
                    }
                })
                .catch(function() {
                    status.className   = 'animalCard__imgStatus animalCard__imgStatus--err';
                    status.textContent = 'Erro de comunicação.';
                });

                input.value = '';
            });
        })(s);
    }

    // ── Salvar configurações ──────────────────────────────────────────────────
    document.getElementById('btnSalvar').addEventListener('click', function () {
        var btn = this;
        btn.disabled    = true;
        btn.textContent = 'Salvando...';

        var payload = {
            animal_1_nome: document.getElementById('animal_1_nome').value.trim(),
            animal_1_pct:  parseFloat(document.getElementById('animal_1_pct').value)  || 0,
            animal_2_nome: document.getElementById('animal_2_nome').value.trim(),
            animal_2_pct:  parseFloat(document.getElementById('animal_2_pct').value)  || 0,
            animal_3_nome: document.getElementById('animal_3_nome').value.trim(),
            animal_3_pct:  parseFloat(document.getElementById('animal_3_pct').value)  || 0,
            animal_4_nome: document.getElementById('animal_4_nome').value.trim(),
            animal_4_pct:  parseFloat(document.getElementById('animal_4_pct').value)  || 0,
            valor_btn_1:      parseInt(document.getElementById('valor_btn_1').value)      || 0,
            valor_btn_2:      parseInt(document.getElementById('valor_btn_2').value)      || 0,
            valor_btn_3:      parseInt(document.getElementById('valor_btn_3').value)      || 0,
            custo_por_animal: parseFloat(document.getElementById('custo_por_animal').value) || 0,
        };

        fetch(BASE_URL + '/admin/services/salvar_calculadora.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var alertEl = document.getElementById('calcAlert');
            alertEl.innerHTML = res.success
                ? '<div class="formAlert formAlert--success">Configurações salvas com sucesso!</div>'
                : '<div class="formAlert formAlert--error">' + (res.message || 'Erro ao salvar.') + '</div>';
            alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(function() {
            document.getElementById('calcAlert').innerHTML = '<div class="formAlert formAlert--error">Erro de comunicação.</div>';
        })
        .finally(function() {
            btn.textContent = 'Salvar configurações';
            updateSoma();
        });
    });
})();
</script>
</body>
</html>
