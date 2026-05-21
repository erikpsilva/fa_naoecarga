<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';
$pdo = getDbConnection();
try {
    $testemunhos = $pdo->query("SELECT * FROM testemunhos ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $testemunhos = [];
}
$ok  = isset($_GET['ok']);
$err = isset($_GET['erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Testemunhos — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
.testHeader { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.testHeader h2 { margin: 0 0 4px; }
.testHeader p  { margin: 0; }
.btnNovo { padding: 9px 20px; background: #a01f2e; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; white-space: nowrap; }
.btnNovo:hover { background: #871a27; }

/* Form */
.testForm { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); padding: 28px; margin-bottom: 28px; display: none; }
.testForm.is-open { display: block; }
.testForm__title { font-size: 1rem; font-weight: 700; margin: 0 0 20px; color: #222; }
.testForm__row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.testForm__field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
.testForm__label { font-size: .82rem; font-weight: 600; color: #333; }
.testForm__label em { font-style: normal; font-weight: 400; color: #aaa; }
.testForm__input { padding: 9px 12px; border: 1px solid #ddd; border-radius: 7px; font-size: .92rem; box-sizing: border-box; }
.testForm__input:focus { outline: none; border-color: #a01f2e; }
.testForm__input.is-invalid { border-color: #dc3545; }
.testForm__editorWrap .ql-toolbar { border-radius: 7px 7px 0 0; background: #fafafa; }
.testForm__editorWrap .ql-container { border-radius: 0 0 7px 7px; font-size: .95rem; min-height: 150px; }
.testForm__editorWrap.is-invalid .ql-toolbar,
.testForm__editorWrap.is-invalid .ql-container { border-color: #dc3545; }
.testForm__hint { font-size: .78rem; color: #999; margin-top: 4px; }
.testForm__error { font-size: .8rem; color: #dc3545; display: none; margin-top: 3px; }
.testForm__actions { display: flex; gap: 10px; margin-top: 8px; align-items: center; }
.testForm__save { padding: 9px 24px; background: #a01f2e; color: #fff; border: none; border-radius: 7px; font-size: .9rem; font-weight: 600; cursor: pointer; }
.testForm__save:hover { background: #871a27; }
.testForm__save:disabled { opacity: .6; cursor: not-allowed; }
.testForm__cancel { padding: 9px 20px; background: #f1f1f1; color: #555; border: none; border-radius: 7px; font-size: .9rem; cursor: pointer; }
.testForm__cancel:hover { background: #e5e5e5; }
.testForm__feedback { font-size: .85rem; }
.testForm__feedback--ok  { color: #28a745; }
.testForm__feedback--err { color: #dc3545; }

/* Table */
.testWrap { background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.08); overflow: hidden; }
.testTable { width: 100%; border-collapse: collapse; }
.testTable th { background: #f5f5f5; font-size: .78rem; font-weight: 700; text-transform: uppercase; color: #888; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eee; }
.testTable td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; font-size: .88rem; color: #333; vertical-align: middle; }
.testTable tr:last-child td { border-bottom: none; }
.testTable__nome { font-weight: 600; display: block; }
.testTable__prof { font-size: .8rem; color: #888; }
.testTable__snippet { color: #666; font-size: .83rem; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.testTable__del { padding: 6px 14px; background: #dc3545; color: #fff; border: none; border-radius: 6px; font-size: .82rem; font-weight: 600; cursor: pointer; }
.testTable__del:hover { background: #b02a37; }
.testEmpty { text-align: center; padding: 60px 20px; color: #999; }

/* Alert */
.testAlert { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: .9rem; }
.testAlert--ok  { background: #d4edda; color: #155724; }
.testAlert--err { background: #f8d7da; color: #721c24; }

/* Delete modal */
.delModal { display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; }
.delModal.is-open { display: flex; }
.delModal__overlay { position: absolute; inset: 0; background: rgba(0,0,0,.5); }
.delModal__box { position: relative; z-index: 1; background: #fff; border-radius: 12px; padding: 36px 32px; width: 100%; max-width: 400px; margin: 16px; box-shadow: 0 8px 40px rgba(0,0,0,.18); text-align: center; }
.delModal__title { font-size: 1.1rem; font-weight: 700; margin: 0 0 8px; }
.delModal__text  { font-size: .9rem; color: #666; margin: 0 0 24px; }
.delModal__actions { display: flex; gap: 10px; justify-content: center; }
.delModal__confirm { padding: 10px 28px; background: #dc3545; color: #fff; border: none; border-radius: 7px; font-weight: 600; cursor: pointer; font-size: .9rem; }
.delModal__confirm:hover { background: #b02a37; }
.delModal__confirm:disabled { opacity: .6; cursor: not-allowed; }
.delModal__cancel  { padding: 10px 24px; background: #f1f1f1; color: #555; border: none; border-radius: 7px; font-size: .9rem; cursor: pointer; }
.delModal__cancel:hover { background: #e5e5e5; }

@media (max-width: 640px) {
    .testForm__row { grid-template-columns: 1fr; }
    .testTable th:nth-child(2), .testTable td:nth-child(2) { display: none; }
}
</style>
</head>
<body>

<?php include ROOT . '/admin/includes/header/header.php'; ?>

<div class="adminLayout">
    <?php include ROOT . '/admin/includes/sidebar/sidebar.php'; ?>
    <main class="adminLayout__content">
        <section class="adminInicio">

            <?php if ($ok): ?>
            <div class="testAlert testAlert--ok">Testemunho salvo com sucesso!</div>
            <?php elseif ($err): ?>
            <div class="testAlert testAlert--err">Erro ao salvar. Verifique os campos obrigatórios.</div>
            <?php endif; ?>

            <div class="testHeader">
                <div>
                    <h2>Testemunhos</h2>
                    <p>Gerencie os depoimentos exibidos na página inicial.</p>
                </div>
                <button class="btnNovo" id="btnNovo">+ Novo Testemunho</button>
            </div>

            <!-- Form -->
            <div class="testForm" id="testForm">
                <p class="testForm__title">Novo Testemunho</p>
                <form id="formTestemunho" novalidate>
                    <div class="testForm__row">
                        <div class="testForm__field">
                            <label class="testForm__label" for="tNome">Nome <em>*</em></label>
                            <input class="testForm__input" type="text" id="tNome" placeholder="Nome completo">
                            <span class="testForm__error" id="errNome">Campo obrigatório.</span>
                        </div>
                        <div class="testForm__field">
                            <label class="testForm__label" for="tProfissao">Profissão <em>(opcional)</em></label>
                            <input class="testForm__input" type="text" id="tProfissao" placeholder="Ex: Médica Veterinária, doutoranda na USP">
                        </div>
                    </div>
                    <div class="testForm__field">
                        <label class="testForm__label" for="tEdicao">Edição <em>*</em></label>
                        <input class="testForm__input" type="text" id="tEdicao" placeholder="Ex: Aluna da 1ª Edição do Curso">
                        <span class="testForm__error" id="errEdicao">Campo obrigatório.</span>
                    </div>
                    <div class="testForm__field">
                        <label class="testForm__label">Testemunho <em>*</em></label>
                        <div class="testForm__editorWrap" id="editorWrap">
                            <div id="quilleditor"></div>
                        </div>
                        <p class="testForm__hint">Use <strong>negrito</strong> para destaque — no site será exibido em semi-negrito.</p>
                        <span class="testForm__error" id="errTexto">O testemunho é obrigatório.</span>
                    </div>
                    <div class="testForm__actions">
                        <button class="testForm__save" type="submit" id="btnSalvar">Salvar</button>
                        <button class="testForm__cancel" type="button" id="btnCancelar">Cancelar</button>
                        <span class="testForm__feedback" id="formFeedback"></span>
                    </div>
                </form>
            </div>

            <!-- List -->
            <?php if (empty($testemunhos)): ?>
            <div class="testEmpty">Nenhum testemunho cadastrado. Clique em <strong>+ Novo Testemunho</strong> para adicionar.</div>
            <?php else: ?>
            <div class="testWrap">
                <table class="testTable">
                    <thead>
                        <tr>
                            <th>Nome / Profissão</th>
                            <th>Edição</th>
                            <th>Testemunho</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="testTableBody">
                        <?php foreach ($testemunhos as $t): ?>
                        <tr id="row<?= $t['id'] ?>">
                            <td>
                                <span class="testTable__nome"><?= htmlspecialchars($t['nome']) ?></span>
                                <?php if ($t['profissao']): ?>
                                <span class="testTable__prof"><?= htmlspecialchars($t['profissao']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['edicao']) ?></td>
                            <td class="testTable__snippet"><?= htmlspecialchars(strip_tags($t['texto'])) ?></td>
                            <td>
                                <button class="testTable__del"
                                    data-id="<?= $t['id'] ?>"
                                    data-nome="<?= htmlspecialchars($t['nome'], ENT_QUOTES) ?>">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </section>
    </main>
</div>

<!-- Delete modal -->
<div class="delModal" id="delModal">
    <div class="delModal__overlay"></div>
    <div class="delModal__box">
        <p class="delModal__title">Excluir testemunho?</p>
        <p class="delModal__text">Tem certeza que deseja excluir o testemunho de <strong id="delNome"></strong>? Esta ação não pode ser desfeita.</p>
        <div class="delModal__actions">
            <button class="delModal__cancel" id="delCancelar">Cancelar</button>
            <button class="delModal__confirm" id="delConfirmar">Sim, excluir</button>
        </div>
    </div>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var BASE = '<?= BASE_URL ?>';

    /* Quill */
    var quill = new Quill('#quilleditor', {
        theme: 'snow',
        modules: { toolbar: [['bold', 'italic', 'underline'], ['clean']] },
        placeholder: 'Escreva o depoimento aqui...'
    });

    /* Toggle form */
    var testForm = document.getElementById('testForm');
    document.getElementById('btnNovo').addEventListener('click', function () {
        testForm.classList.add('is-open');
        testForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.getElementById('btnCancelar').addEventListener('click', function () {
        testForm.classList.remove('is-open');
    });

    /* Form submit */
    document.getElementById('formTestemunho').addEventListener('submit', function (e) {
        e.preventDefault();
        var nome    = document.getElementById('tNome').value.trim();
        var profissao = document.getElementById('tProfissao').value.trim();
        var edicao  = document.getElementById('tEdicao').value.trim();
        var texto   = quill.root.innerHTML;
        var plain   = quill.getText().trim();
        var valid   = true;

        function setErr(fieldId, errId, show) {
            document.getElementById(fieldId).classList.toggle('is-invalid', show);
            document.getElementById(errId).style.display = show ? 'block' : 'none';
        }
        setErr('tNome',   'errNome',   !nome);
        setErr('tEdicao', 'errEdicao', !edicao);
        document.getElementById('editorWrap').classList.toggle('is-invalid', !plain);
        document.getElementById('errTexto').style.display = plain ? 'none' : 'block';
        if (!nome || !edicao || !plain) return;

        var btn = document.getElementById('btnSalvar');
        var feedback = document.getElementById('formFeedback');
        btn.disabled = true;
        btn.textContent = 'Salvando...';
        feedback.textContent = '';
        feedback.className = 'testForm__feedback';

        fetch(BASE + '/admin/services/salvar_testemunho.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome: nome, profissao: profissao, edicao: edicao, texto: texto })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.disabled = false;
            btn.textContent = 'Salvar';
            if (res.success) {
                feedback.textContent = 'Salvo com sucesso!';
                feedback.className = 'testForm__feedback testForm__feedback--ok';
                setTimeout(function () { location.reload(); }, 800);
            } else {
                feedback.textContent = res.message || 'Erro ao salvar.';
                feedback.className = 'testForm__feedback testForm__feedback--err';
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Salvar';
            feedback.textContent = 'Erro de comunicação.';
            feedback.className = 'testForm__feedback testForm__feedback--err';
        });
    });

    /* Delete modal */
    var delModal    = document.getElementById('delModal');
    var delNome     = document.getElementById('delNome');
    var delConfirmar = document.getElementById('delConfirmar');
    var pendingId   = null;

    document.querySelectorAll('.testTable__del').forEach(function (btn) {
        btn.addEventListener('click', function () {
            pendingId = this.dataset.id;
            delNome.textContent = this.dataset.nome;
            delModal.classList.add('is-open');
        });
    });

    function closeDelModal() { delModal.classList.remove('is-open'); pendingId = null; }
    document.getElementById('delCancelar').addEventListener('click', closeDelModal);
    delModal.querySelector('.delModal__overlay').addEventListener('click', closeDelModal);

    delConfirmar.addEventListener('click', function () {
        if (!pendingId) return;
        delConfirmar.disabled = true;
        delConfirmar.textContent = 'Excluindo...';

        fetch(BASE + '/admin/services/deletar_testemunho.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(pendingId) })
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            delConfirmar.disabled = false;
            delConfirmar.textContent = 'Sim, excluir';
            if (res.success) {
                var row = document.getElementById('row' + pendingId);
                if (row) row.remove();
                closeDelModal();
            } else {
                alert(res.message || 'Erro ao excluir.');
                closeDelModal();
            }
        })
        .catch(function () {
            delConfirmar.disabled = false;
            delConfirmar.textContent = 'Sim, excluir';
            alert('Erro de comunicação.');
            closeDelModal();
        });
    });
})();
</script>
</body>
</html>
