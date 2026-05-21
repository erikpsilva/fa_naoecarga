<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
if ($_SESSION['usuario']['nivel_acesso'] !== 'admin') {
    header('Location: ' . BASE_URL . '/admin/inicio');
    exit;
}
require_once ROOT . '/config/database.php';
$pdo      = getDbConnection();
$usuarios = $pdo->query("SELECT id, nome_completo, email, cpf, nivel_acesso FROM admin_usuarios ORDER BY nome_completo ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Administrar Usuários — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
/* ── Table ── */
.usuariosWrap { background:#fff; border-radius:8px; box-shadow:0 1px 4px rgba(0,0,0,.08); overflow:hidden; margin-top:8px; }
.usuariosTable { width:100%; border-collapse:collapse; font-size:.9rem; }
.usuariosTable thead { background:#f8f8f8; }
.usuariosTable th { text-align:left; padding:12px 16px; font-size:.73rem; text-transform:uppercase; letter-spacing:.05em; color:#888; border-bottom:1px solid #eee; white-space:nowrap; font-weight:600; }
.usuariosTable td { padding:13px 16px; border-bottom:1px solid #f0f0f0; vertical-align:middle; color:#333; }
.usuariosTable tbody tr:last-child td { border-bottom:none; }
.usuariosTable tbody tr:hover td { background:#fafafa; }
.usuariosTable td.td--nome { font-weight:600; }

.nivelBadge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.73rem; font-weight:700; }
.nivelBadge--admin  { background:#fde8ea; color:#a01f2e; }
.nivelBadge--editor { background:#cce5ff; color:#004085; }
.nivelBadge--leitor { background:#e2e3e5; color:#383d41; }

.btnAcao { padding:5px 14px; border:none; border-radius:6px; font-size:.82rem; cursor:pointer; font-weight:600; }
.btnAcao--editar  { background:#e8f0fe; color:#1a56db; margin-right:6px; }
.btnAcao--editar:hover  { background:#c7d9fc; }
.btnAcao--deletar { background:#fde8ea; color:#a01f2e; }
.btnAcao--deletar:hover { background:#fbc8cc; }

/* ── Modal ── */
.uModal { display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; }
.uModal.is-open { display:flex; }
.uModal__overlay { position:absolute; inset:0; background:rgba(0,0,0,.5); }
.uModal__box { position:relative; z-index:1; background:#fff; border-radius:12px; padding:36px 32px; width:100%; max-width:540px; margin:16px; box-shadow:0 8px 40px rgba(0,0,0,.18); max-height:90vh; overflow-y:auto; }
.uModal__close { position:absolute; top:14px; right:18px; background:none; border:none; font-size:26px; cursor:pointer; color:#888; line-height:1; }
.uModal__close:hover { color:#333; }
.uModal__title { font-size:1.1rem; font-weight:700; margin:0 0 24px; }
.uModal__grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.uModal__field { display:flex; flex-direction:column; gap:5px; }
.uModal__field--full { grid-column:1/-1; }
.uModal__label { font-size:.78rem; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.03em; }
.uModal__input, .uModal__select { padding:9px 12px; border:1px solid #ddd; border-radius:7px; font-size:.92rem; width:100%; box-sizing:border-box; }
.uModal__input:focus, .uModal__select:focus { outline:none; border-color:#a01f2e; }
.uModal__input.is-invalid { border-color:#dc3545; }
.uModal__divider { grid-column:1/-1; border:none; border-top:1px dashed #eee; margin:6px 0; }
.uModal__hint { grid-column:1/-1; font-size:.8rem; color:#999; margin:0; }
.uModal__actions { display:flex; gap:10px; justify-content:flex-end; margin-top:24px; }
.uModal__btn { padding:9px 22px; border:none; border-radius:7px; font-size:.9rem; font-weight:600; cursor:pointer; }
.uModal__btn--salvar  { background:#a01f2e; color:#fff; }
.uModal__btn--salvar:hover  { background:#871a27; }
.uModal__btn--cancelar { background:#f0f0f0; color:#555; }
.uModal__btn--cancelar:hover { background:#e0e0e0; }
.uModal__feedback { font-size:.83rem; min-height:18px; margin-top:10px; text-align:center; }
.uModal__feedback--ok  { color:#28a745; }
.uModal__feedback--err { color:#dc3545; }

/* ── Confirm dialog ── */
.uConfirm { display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center; }
.uConfirm.is-open { display:flex; }
.uConfirm__overlay { position:absolute; inset:0; background:rgba(0,0,0,.55); }
.uConfirm__box { position:relative; z-index:1; background:#fff; border-radius:12px; padding:32px 28px; width:100%; max-width:380px; margin:16px; text-align:center; box-shadow:0 8px 40px rgba(0,0,0,.2); }
.uConfirm__icon { font-size:2.2rem; margin-bottom:12px; }
.uConfirm__text { font-size:.97rem; color:#333; margin:0 0 24px; line-height:1.5; }
.uConfirm__actions { display:flex; gap:10px; justify-content:center; }
.uConfirm__btn { padding:9px 24px; border:none; border-radius:7px; font-size:.9rem; font-weight:600; cursor:pointer; }
.uConfirm__btn--sim    { background:#a01f2e; color:#fff; }
.uConfirm__btn--sim:hover    { background:#871a27; }
.uConfirm__btn--nao { background:#f0f0f0; color:#555; }
.uConfirm__btn--nao:hover { background:#e0e0e0; }

/* ── Responsive ── */
@media (max-width:700px) {
    .usuariosWrap { background:transparent; box-shadow:none; border-radius:0; overflow:visible; }
    .usuariosTable thead { display:none; }
    .usuariosTable tbody tr { display:block; background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.08); margin-bottom:12px; padding:4px 0; }
    .usuariosTable td { display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid #f5f5f5; font-size:.88rem; }
    .usuariosTable td::before { content:attr(data-label); font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#999; flex-shrink:0; margin-right:12px; }
    .usuariosTable tbody tr td:last-child { border-bottom:none; }
    .uModal__grid { grid-template-columns:1fr; }
}
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
                    <h2>Administrar Usuários</h2>
                    <p>Gerencie todos os usuários cadastrados na plataforma.</p>
                </div>
            </div>

            <div class="usuariosWrap">
                <table class="usuariosTable">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="4" style="text-align:center;padding:40px;color:#aaa;">Nenhum usuário encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                        <?php
                            $partes    = explode(' ', $u['nome_completo'], 2);
                            $primeiroN = $partes[0] ?? '';
                            $ultimoN   = $partes[1] ?? '';
                        ?>
                        <tr data-id="<?= $u['id'] ?>"
                            data-nome="<?= htmlspecialchars($primeiroN) ?>"
                            data-sobrenome="<?= htmlspecialchars($ultimoN) ?>"
                            data-email="<?= htmlspecialchars($u['email']) ?>"
                            data-cpf="<?= htmlspecialchars($u['cpf']) ?>"
                            data-nivel="<?= htmlspecialchars($u['nivel_acesso']) ?>">
                            <td class="td--nome" data-label="Nome"><?= htmlspecialchars($u['nome_completo']) ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($u['email']) ?></td>
                            <td data-label="Tipo">
                                <span class="nivelBadge nivelBadge--<?= $u['nivel_acesso'] ?>">
                                    <?= strtoupper($u['nivel_acesso']) ?>
                                </span>
                            </td>
                            <td data-label="Ações">
                                <button class="btnAcao btnAcao--editar btnEditar">Editar</button>
                                <?php if ($u['id'] !== (int)$_SESSION['usuario']['id']): ?>
                                <button class="btnAcao btnAcao--deletar btnDeletar">Excluir</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<!-- Modal Editar -->
<div class="uModal" id="uModal">
    <div class="uModal__overlay"></div>
    <div class="uModal__box">
        <button class="uModal__close" id="uModalClose">&times;</button>
        <h2 class="uModal__title">Editar usuário</h2>
        <input type="hidden" id="uId">
        <div class="uModal__grid">
            <div class="uModal__field">
                <label class="uModal__label" for="uNome">Nome</label>
                <input class="uModal__input" type="text" id="uNome" placeholder="Nome">
            </div>
            <div class="uModal__field">
                <label class="uModal__label" for="uSobrenome">Sobrenome</label>
                <input class="uModal__input" type="text" id="uSobrenome" placeholder="Sobrenome">
            </div>
            <div class="uModal__field">
                <label class="uModal__label" for="uEmail">E-mail</label>
                <input class="uModal__input" type="email" id="uEmail" placeholder="E-mail">
            </div>
            <div class="uModal__field">
                <label class="uModal__label" for="uCpf">CPF</label>
                <input class="uModal__input" type="text" id="uCpf" placeholder="000.000.000-00">
            </div>
            <div class="uModal__field uModal__field--full">
                <label class="uModal__label" for="uNivel">Nível de acesso</label>
                <select class="uModal__select" id="uNivel">
                    <option value="admin">Admin</option>
                    <option value="editor">Editor</option>
                    <option value="leitor">Leitor</option>
                </select>
            </div>
            <hr class="uModal__divider">
            <p class="uModal__hint">Deixe em branco para não alterar a senha.</p>
            <div class="uModal__field">
                <label class="uModal__label" for="uSenha">Nova senha</label>
                <input class="uModal__input" type="password" id="uSenha" placeholder="6 a 20 caracteres">
            </div>
            <div class="uModal__field">
                <label class="uModal__label" for="uConfirmar">Confirmar senha</label>
                <input class="uModal__input" type="password" id="uConfirmar" placeholder="Repita a senha">
            </div>
        </div>
        <p class="uModal__feedback" id="uFeedback"></p>
        <div class="uModal__actions">
            <button class="uModal__btn uModal__btn--cancelar" id="uBtnCancelar">Cancelar</button>
            <button class="uModal__btn uModal__btn--salvar" id="uBtnSalvar">Salvar alterações</button>
        </div>
    </div>
</div>

<!-- Confirm delete -->
<div class="uConfirm" id="uConfirm">
    <div class="uConfirm__overlay"></div>
    <div class="uConfirm__box">
        <div class="uConfirm__icon">⚠️</div>
        <p class="uConfirm__text">Tem certeza que deseja excluir o usuário <strong id="uConfirmNome"></strong>? Esta ação não pode ser desfeita.</p>
        <div class="uConfirm__actions">
            <button class="uConfirm__btn uConfirm__btn--nao" id="uConfirmNao">Cancelar</button>
            <button class="uConfirm__btn uConfirm__btn--sim" id="uConfirmSim">Sim, excluir</button>
        </div>
    </div>
</div>

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
(function() {
    var BASE = '<?= BASE_URL ?>';
    var modal   = document.getElementById('uModal');
    var confirm = document.getElementById('uConfirm');
    var pendingDeleteId = null;

    function openModal(tr) {
        document.getElementById('uId').value        = tr.dataset.id;
        document.getElementById('uNome').value      = tr.dataset.nome;
        document.getElementById('uSobrenome').value = tr.dataset.sobrenome;
        document.getElementById('uEmail').value     = tr.dataset.email;
        document.getElementById('uCpf').value       = tr.dataset.cpf;
        document.getElementById('uNivel').value     = tr.dataset.nivel;
        document.getElementById('uSenha').value     = '';
        document.getElementById('uConfirmar').value = '';
        document.getElementById('uFeedback').textContent = '';
        document.getElementById('uFeedback').className  = 'uModal__feedback';
        document.querySelectorAll('.uModal__input').forEach(function(i) { i.classList.remove('is-invalid'); });
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function openConfirm(id, nome) {
        pendingDeleteId = id;
        document.getElementById('uConfirmNome').textContent = nome;
        confirm.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeConfirm() {
        confirm.classList.remove('is-open');
        pendingDeleteId = null;
        document.body.style.overflow = '';
    }

    // Abrir modal editar
    document.querySelectorAll('.btnEditar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(this.closest('tr'));
        });
    });

    // Abrir confirm deletar
    document.querySelectorAll('.btnDeletar').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tr = this.closest('tr');
            openConfirm(tr.dataset.id, tr.dataset.nome + ' ' + tr.dataset.sobrenome);
        });
    });

    // Fechar modal
    document.getElementById('uModalClose').addEventListener('click', closeModal);
    document.getElementById('uBtnCancelar').addEventListener('click', closeModal);
    modal.querySelector('.uModal__overlay').addEventListener('click', closeModal);

    // Fechar confirm
    document.getElementById('uConfirmNao').addEventListener('click', closeConfirm);
    confirm.querySelector('.uConfirm__overlay').addEventListener('click', closeConfirm);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeModal(); closeConfirm(); }
    });

    // Salvar edição
    document.getElementById('uBtnSalvar').addEventListener('click', function() {
        var btn      = this;
        var feedback = document.getElementById('uFeedback');
        var senha    = document.getElementById('uSenha').value;
        var confirmar = document.getElementById('uConfirmar').value;

        document.querySelectorAll('.uModal__input').forEach(function(i) { i.classList.remove('is-invalid'); });

        if (senha && senha !== confirmar) {
            document.getElementById('uSenha').classList.add('is-invalid');
            document.getElementById('uConfirmar').classList.add('is-invalid');
            feedback.textContent = 'As senhas não coincidem.';
            feedback.className   = 'uModal__feedback uModal__feedback--err';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Salvando...';

        fetch(BASE + '/admin/services/editar_usuario_admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id:        parseInt(document.getElementById('uId').value),
                nome:      document.getElementById('uNome').value,
                sobrenome: document.getElementById('uSobrenome').value,
                email:     document.getElementById('uEmail').value,
                cpf:       document.getElementById('uCpf').value,
                nivel:     document.getElementById('uNivel').value,
                senha:     senha,
                confirmar: confirmar,
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                feedback.textContent = 'Salvo com sucesso!';
                feedback.className   = 'uModal__feedback uModal__feedback--ok';
                setTimeout(function() { location.reload(); }, 900);
            } else {
                feedback.textContent = res.message || 'Erro ao salvar.';
                feedback.className   = 'uModal__feedback uModal__feedback--err';
                btn.disabled    = false;
                btn.textContent = 'Salvar alterações';
            }
        })
        .catch(function() {
            feedback.textContent = 'Erro de comunicação.';
            feedback.className   = 'uModal__feedback uModal__feedback--err';
            btn.disabled    = false;
            btn.textContent = 'Salvar alterações';
        });
    });

    // Confirmar exclusão
    document.getElementById('uConfirmSim').addEventListener('click', function() {
        var btn = this;
        btn.disabled    = true;
        btn.textContent = 'Excluindo...';

        fetch(BASE + '/admin/services/deletar_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(pendingDeleteId) })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeConfirm();
                location.reload();
            } else {
                alert(res.message || 'Erro ao excluir.');
                btn.disabled    = false;
                btn.textContent = 'Sim, excluir';
            }
        })
        .catch(function() {
            alert('Erro de comunicação.');
            btn.disabled    = false;
            btn.textContent = 'Sim, excluir';
        });
    });
})();
</script>
</body>
</html>
