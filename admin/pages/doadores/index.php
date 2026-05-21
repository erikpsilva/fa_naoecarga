<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/mercadopago.php';
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

// ─── Sincroniza pendentes com a API do MP ────────────────────────────────────
$statusMap = [
    'approved'   => 'aprovado',
    'authorized' => 'aprovado',
    'active'     => 'aprovado',
    'rejected'   => 'recusado',
    'cancelled'  => 'cancelado',
    'paused'     => 'cancelado',
];

function mpGetStatus(string $url): ?array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . MP_ACCESS_TOKEN],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    return $raw ? json_decode($raw, true) : null;
}

$logDir  = ROOT . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0755, true);
$logFile = $logDir . '/sync_doadores.log';

$pendentes = $pdo->query("SELECT id, mp_id, tipo FROM doadores WHERE status = 'pendente' AND mp_id != ''")->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendentes as $d) {
    $mpStatus = null;
    $res      = null;

    if ($d['tipo'] === 'unica') {
        $res         = mpGetStatus("https://api.mercadopago.com/merchant_orders?preference_id=" . urlencode($d['mp_id']) . "&limit=1");
        $orderStatus = $res['elements'][0]['order_status'] ?? null;
        $orderMap    = ['paid' => 'approved', 'cancelled' => 'cancelled', 'reverted' => 'rejected'];
        $mpStatus    = isset($orderMap[$orderStatus]) ? $orderMap[$orderStatus] : ($res['elements'][0]['payments'][0]['status'] ?? null);
    } elseif ($d['tipo'] === 'mensal') {
        $res = mpGetStatus("https://api.mercadopago.com/preapproval/{$d['mp_id']}");
        $mpStatus = $res['status'] ?? null;
    }

    file_put_contents($logFile,
        '[' . date('Y-m-d H:i:s') . '] id=' . $d['id'] . ' tipo=' . $d['tipo'] . ' mp_id=' . $d['mp_id'] . ' mp_status=' . ($mpStatus ?? 'null') . ' api_resp=' . json_encode($res) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    $novoStatus = $statusMap[$mpStatus] ?? null;
    if ($novoStatus) {
        $pdo->prepare("UPDATE doadores SET status = :status WHERE id = :id")
            ->execute([':status' => $novoStatus, ':id' => $d['id']]);
    }
}

$busca  = isset($_GET['busca'])  ? trim($_GET['busca'])  : '';
$filtro = isset($_GET['status']) ? trim($_GET['status']) : '';
$tipo   = isset($_GET['tipo'])   ? trim($_GET['tipo'])   : '';

$where  = [];
$params = [];

if ($busca !== '') {
    $where[]          = '(nome LIKE :busca OR email LIKE :busca OR telefone LIKE :busca)';
    $params[':busca'] = '%' . $busca . '%';
}
if (in_array($filtro, ['pendente', 'aprovado', 'recusado', 'cancelado'])) {
    $where[]           = 'status = :status';
    $params[':status'] = $filtro;
}
if (in_array($tipo, ['unica', 'mensal'])) {
    $where[]         = 'tipo = :tipo';
    $params[':tipo'] = $tipo;
}

$sql  = 'SELECT * FROM doadores';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totais resumidos
$stmtTotais = $pdo->query("SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'aprovado' THEN valor ELSE 0 END) AS total_aprovado,
    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) AS pendentes
FROM doadores");
$totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Doadores — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
/* ── Stats ── */
.doadoresStats { display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.doadoresStat  { background: #fff; border-radius: 8px; padding: 20px 28px; box-shadow: 0 1px 4px rgba(0,0,0,.08); flex: 1; min-width: 140px; }
.doadoresStat__label { font-size: .78rem; color: #888; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .04em; }
.doadoresStat__value { font-size: 1.5rem; font-weight: 700; color: #222; margin: 0; }

/* ── Filters ── */
.doadoresFilters { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
.doadoresFilters input[type=text] { flex: 1; min-width: 180px; }
.doadoresFilters input[type=text],
.doadoresFilters select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: .9rem; background: #fff; }
.doadoresFilters button { padding: 8px 18px; background: #a01f2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: .9rem; white-space: nowrap; }
.doadoresFilters button:hover { background: #871a27; }
.doadoresFilters a.btnClear { padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; color: #555; font-size: .9rem; text-decoration: none; background: #fff; white-space: nowrap; }
.doadoresFilters a.btnClear:hover { background: #f5f5f5; }

/* ── Badges ── */
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; }
.badge--pendente  { background: #fff3cd; color: #856404; }
.badge--aprovado  { background: #d4edda; color: #155724; }
.badge--recusado  { background: #f8d7da; color: #721c24; }
.badge--cancelado { background: #e2e3e5; color: #383d41; }

.tipoBadge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 600; }
.tipoBadge--unica  { background: #cce5ff; color: #004085; }
.tipoBadge--mensal { background: #e2d9f3; color: #432874; }

/* ── Status select ── */
.statusSelect { padding: 5px 8px; border: 1px solid #ddd; border-radius: 6px; font-size: .82rem; background: #fff; cursor: pointer; width: 100%; max-width: 130px; }
.statusSelect--pendente  { border-color: #ffc107; color: #856404; }
.statusSelect--aprovado  { border-color: #28a745; color: #155724; }
.statusSelect--recusado  { border-color: #dc3545; color: #721c24; }
.statusSelect--cancelado { border-color: #adb5bd; color: #383d41; }
.statusSelect:disabled   { opacity: .6; cursor: not-allowed; }

/* ── Table desktop ── */
.tableWrap { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.08); overflow: hidden; }
.doadoresTable { width: 100%; border-collapse: collapse; font-size: .88rem; }
.doadoresTable thead { background: #f8f8f8; }
.doadoresTable th { text-align: left; padding: 12px 14px; font-size: .73rem; text-transform: uppercase; letter-spacing: .05em; color: #888; border-bottom: 1px solid #eee; white-space: nowrap; font-weight: 600; }
.doadoresTable td { padding: 13px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; color: #333; }
.doadoresTable tbody tr:last-child td { border-bottom: none; }
.doadoresTable tbody tr:hover td { background: #fafafa; }
.doadoresTable td.td--id { color: #aaa; font-size: .82rem; }
.doadoresTable td.td--nome { font-weight: 600; }

.emptyMsg { text-align: center; padding: 48px 16px; color: #aaa; font-size: .95rem; }

/* ── Table mobile (cards) ── */
@media (max-width: 860px) {
    .tableWrap { background: transparent; box-shadow: none; border-radius: 0; overflow: visible; }

    .doadoresTable thead { display: none; }

    .doadoresTable tbody tr {
        display: block;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
        margin-bottom: 12px;
        padding: 4px 0;
        overflow: hidden;
    }
    .doadoresTable tbody tr:hover { background: #fff; }

    .doadoresTable td {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        border-bottom: 1px solid #f5f5f5;
        font-size: .88rem;
    }
    .doadoresTable tbody tr td:last-child { border-bottom: none; }

    .doadoresTable td::before {
        content: attr(data-label);
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #999;
        flex-shrink: 0;
        margin-right: 12px;
    }
    .doadoresTable td.td--id { display: none; }
    .statusSelect { max-width: 100%; width: auto; flex: 1; }
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
                    <h2>Doadores</h2>
                    <p>Registro de todos que iniciaram uma doação.</p>
                </div>
            </div>

            <div class="doadoresStats">
                <div class="doadoresStat">
                    <p class="doadoresStat__label">Total de registros</p>
                    <p class="doadoresStat__value"><?= (int)$totais['total'] ?></p>
                </div>
                <div class="doadoresStat">
                    <p class="doadoresStat__label">Arrecadado (aprovado)</p>
                    <p class="doadoresStat__value">R$ <?= number_format((float)$totais['total_aprovado'], 2, ',', '.') ?></p>
                </div>
                <div class="doadoresStat">
                    <p class="doadoresStat__label">Pendentes</p>
                    <p class="doadoresStat__value"><?= (int)$totais['pendentes'] ?></p>
                </div>
            </div>

            <form method="GET" class="doadoresFilters">
                <input type="text" name="busca" placeholder="Buscar nome, e-mail ou telefone" value="<?= htmlspecialchars($busca) ?>">
                <select name="status">
                    <option value="">Todos os status</option>
                    <option value="pendente"  <?= $filtro === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                    <option value="aprovado"  <?= $filtro === 'aprovado'  ? 'selected' : '' ?>>Aprovado</option>
                    <option value="recusado"  <?= $filtro === 'recusado'  ? 'selected' : '' ?>>Recusado</option>
                    <option value="cancelado" <?= $filtro === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
                <select name="tipo">
                    <option value="">Todos os tipos</option>
                    <option value="unica"  <?= $tipo === 'unica'  ? 'selected' : '' ?>>Única</option>
                    <option value="mensal" <?= $tipo === 'mensal' ? 'selected' : '' ?>>Mensal</option>
                </select>
                <button type="submit">Filtrar</button>
                <a class="btnClear" href="<?= BASE_URL ?>/admin/doadores">Limpar</a>
            </form>

            <div class="tableWrap">
                <table class="doadoresTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Alterar status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($doadores)): ?>
                        <tr><td colspan="9" class="emptyMsg">Nenhum doador encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($doadores as $d): ?>
                        <tr>
                            <td class="td--id" data-label="#"><?= $d['id'] ?></td>
                            <td class="td--nome" data-label="Nome"><?= htmlspecialchars($d['nome'] ?: '—') ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($d['email'] ?: '—') ?></td>
                            <td data-label="Telefone"><?= htmlspecialchars($d['telefone'] ?: '—') ?></td>
                            <td data-label="Tipo">
                                <span class="tipoBadge tipoBadge--<?= $d['tipo'] ?>">
                                    <?= $d['tipo'] === 'mensal' ? 'Mensal' : 'Única' ?>
                                </span>
                            </td>
                            <td data-label="Valor">R$ <?= number_format((float)$d['valor'], 2, ',', '.') ?></td>
                            <td data-label="Status">
                                <span class="badge badge--<?= $d['status'] ?>">
                                    <?= ucfirst($d['status']) ?>
                                </span>
                            </td>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                            <td data-label="Alterar">
                                <select class="statusSelect statusSelect--<?= $d['status'] ?>" data-id="<?= $d['id'] ?>">
                                    <option value="pendente"  <?= $d['status'] === 'pendente'  ? 'selected' : '' ?>>Pendente</option>
                                    <option value="aprovado"  <?= $d['status'] === 'aprovado'  ? 'selected' : '' ?>>Aprovado</option>
                                    <option value="recusado"  <?= $d['status'] === 'recusado'  ? 'selected' : '' ?>>Recusado</option>
                                    <option value="cancelado" <?= $d['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
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

<?php include ROOT . '/admin/includes/footer/footer.php'; ?>
<?php include ROOT . '/admin/includes/scripts.php'; ?>
<script>
document.querySelectorAll('.statusSelect').forEach(function(sel) {
    sel.addEventListener('change', function() {
        var id        = this.dataset.id;
        var novoStatus = this.value;
        var el        = this;
        el.disabled   = true;

        fetch('<?= BASE_URL ?>/admin/services/atualizar_status_doador.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: parseInt(id), status: novoStatus })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                el.className = 'statusSelect statusSelect--' + novoStatus;
                // Atualiza o badge na mesma linha
                var badge = el.closest('tr').querySelector('.badge');
                badge.className = 'badge badge--' + novoStatus;
                badge.textContent = novoStatus.charAt(0).toUpperCase() + novoStatus.slice(1);
            } else {
                alert('Erro ao atualizar: ' + (res.message || ''));
                location.reload();
            }
        })
        .catch(function() {
            alert('Erro de comunicação.');
            location.reload();
        })
        .finally(function() {
            el.disabled = false;
        });
    });
});
</script>
</body>
</html>
