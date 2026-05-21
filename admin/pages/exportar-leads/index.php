<?php include ROOT . '/admin/includes/auth_check.php'; ?>
<?php
require_once ROOT . '/config/database.php';

$pdo = getDbConnection();

$busca   = isset($_GET['busca'])   ? trim($_GET['busca'])   : '';
$filtroLgpd = isset($_GET['lgpd']) ? trim($_GET['lgpd'])    : '';

$where  = [];
$params = [];

if ($busca !== '') {
    $where[]          = '(nome LIKE :busca OR email LIKE :busca OR telefone LIKE :busca)';
    $params[':busca'] = '%' . $busca . '%';
}
if (in_array($filtroLgpd, ['LGPD_OK', 'LGPD_NOK'])) {
    $where[]           = 'lgpd_tag = :lgpd_tag';
    $params[':lgpd_tag'] = $filtroLgpd;
}

$sql = 'SELECT id, nome, email, telefone, lgpd_tag, created_at FROM doadores';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtTotais = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN lgpd_tag = 'LGPD_OK'  THEN 1 ELSE 0 END) AS total_ok,
        SUM(CASE WHEN lgpd_tag = 'LGPD_NOK' THEN 1 ELSE 0 END) AS total_nok,
        SUM(CASE WHEN lgpd_tag IS NULL       THEN 1 ELSE 0 END) AS total_sem_tag
    FROM doadores
");
$totais = $stmtTotais->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<title>Exportar Leads — Admin Animal não é carga</title>
<?php include ROOT . '/admin/includes/assets.php'; ?>
<style>
/* ── Stats ── */
.leadsStats { display: flex; gap: 16px; margin-bottom: 28px; flex-wrap: wrap; }
.leadsStat  { background: #fff; border-radius: 8px; padding: 20px 28px; box-shadow: 0 1px 4px rgba(0,0,0,.08); flex: 1; min-width: 140px; }
.leadsStat__label { font-size: .78rem; color: #888; margin: 0 0 4px; text-transform: uppercase; letter-spacing: .04em; }
.leadsStat__value { font-size: 1.5rem; font-weight: 700; color: #222; margin: 0; }

/* ── Filters & Actions ── */
.leadsToolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
.leadsToolbar input[type=text] { flex: 1; min-width: 180px; }
.leadsToolbar input[type=text],
.leadsToolbar select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: .9rem; background: #fff; }
.leadsToolbar button[type=submit] { padding: 8px 18px; background: #a01f2e; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: .9rem; white-space: nowrap; }
.leadsToolbar button[type=submit]:hover { background: #871a27; }
.leadsToolbar a.btnClear { padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; color: #555; font-size: .9rem; text-decoration: none; background: #fff; white-space: nowrap; }
.leadsToolbar a.btnClear:hover { background: #f5f5f5; }

.btnExport { display: inline-flex; align-items: center; gap: 7px; padding: 8px 18px; background: #1a7a3c; color: #fff; border: none; border-radius: 6px; font-size: .9rem; font-weight: 600; text-decoration: none; white-space: nowrap; cursor: pointer; }
.btnExport:hover { background: #155e2f; color: #fff; }
.btnExport svg { flex-shrink: 0; }
.btnExport__hint { font-size: .75rem; color: #666; margin-left: 4px; }

/* ── LGPD badges ── */
.lgpdBadge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .73rem; font-weight: 700; letter-spacing: .03em; }
.lgpdBadge--ok  { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.lgpdBadge--nok { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.lgpdBadge--none { background: #e2e3e5; color: #383d41; border: 1px solid #d6d8db; }

/* ── Table ── */
.tableWrap { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.08); overflow: hidden; }
.leadsTable { width: 100%; border-collapse: collapse; font-size: .88rem; }
.leadsTable thead { background: #f8f8f8; }
.leadsTable th { text-align: left; padding: 12px 14px; font-size: .73rem; text-transform: uppercase; letter-spacing: .05em; color: #888; border-bottom: 1px solid #eee; white-space: nowrap; font-weight: 600; }
.leadsTable td { padding: 13px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; color: #333; }
.leadsTable tbody tr:last-child td { border-bottom: none; }
.leadsTable tbody tr:hover td { background: #fafafa; }
.leadsTable td.td--nome { font-weight: 600; }

.emptyMsg { text-align: center; padding: 48px 16px; color: #aaa; font-size: .95rem; }

/* ── Mobile cards ── */
@media (max-width: 760px) {
    .tableWrap { background: transparent; box-shadow: none; border-radius: 0; overflow: visible; }
    .leadsTable thead { display: none; }
    .leadsTable tbody tr {
        display: block; background: #fff; border-radius: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08); margin-bottom: 12px; padding: 4px 0;
    }
    .leadsTable td {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 16px; border-bottom: 1px solid #f5f5f5; font-size: .88rem;
    }
    .leadsTable td::before {
        content: attr(data-label);
        font-size: .72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .04em; color: #999; flex-shrink: 0; margin-right: 12px;
    }
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
                    <h2>Exportar Leads</h2>
                    <p>Lista de pessoas que iniciaram uma doação. Exportação CSV inclui apenas contatos com consentimento LGPD_OK.</p>
                </div>
            </div>

            <div class="leadsStats">
                <div class="leadsStat">
                    <p class="leadsStat__label">Total de leads</p>
                    <p class="leadsStat__value"><?= (int)$totais['total'] ?></p>
                </div>
                <div class="leadsStat">
                    <p class="leadsStat__label">LGPD OK</p>
                    <p class="leadsStat__value" style="color:#155724"><?= (int)$totais['total_ok'] ?></p>
                </div>
                <div class="leadsStat">
                    <p class="leadsStat__label">LGPD NOK</p>
                    <p class="leadsStat__value" style="color:#721c24"><?= (int)$totais['total_nok'] ?></p>
                </div>
                <div class="leadsStat">
                    <p class="leadsStat__label">Sem resposta</p>
                    <p class="leadsStat__value" style="color:#888"><?= (int)$totais['total_sem_tag'] ?></p>
                </div>
            </div>

            <form method="GET" class="leadsToolbar">
                <input type="text" name="busca" placeholder="Buscar nome, e-mail ou telefone" value="<?= htmlspecialchars($busca) ?>">
                <select name="lgpd">
                    <option value="">Todos</option>
                    <option value="LGPD_OK"  <?= $filtroLgpd === 'LGPD_OK'  ? 'selected' : '' ?>>LGPD OK</option>
                    <option value="LGPD_NOK" <?= $filtroLgpd === 'LGPD_NOK' ? 'selected' : '' ?>>LGPD NOK</option>
                </select>
                <button type="submit">Filtrar</button>
                <a class="btnClear" href="<?= BASE_URL ?>/admin/exportar-leads">Limpar</a>
                <a class="btnExport" href="<?= BASE_URL ?>/admin/services/exportar_leads_csv.php" title="Exporta somente contatos com LGPD_OK">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Exportar CSV
                    <span class="btnExport__hint">(LGPD_OK)</span>
                </a>
            </form>

            <div class="tableWrap">
                <table class="leadsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>LGPD</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($leads)): ?>
                        <tr><td colspan="6" class="emptyMsg">Nenhum lead encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leads as $l): ?>
                        <?php
                            $tag = $l['lgpd_tag'];
                            if ($tag === 'LGPD_OK') {
                                $badgeClass = 'lgpdBadge--ok';
                                $badgeLabel = 'LGPD OK';
                            } elseif ($tag === 'LGPD_NOK') {
                                $badgeClass = 'lgpdBadge--nok';
                                $badgeLabel = 'LGPD NOK';
                            } else {
                                $badgeClass = 'lgpdBadge--none';
                                $badgeLabel = 'Sem resposta';
                            }
                        ?>
                        <tr>
                            <td data-label="#"><?= $l['id'] ?></td>
                            <td class="td--nome" data-label="Nome"><?= htmlspecialchars($l['nome'] ?: '—') ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($l['email'] ?: '—') ?></td>
                            <td data-label="Telefone"><?= htmlspecialchars($l['telefone'] ?: '—') ?></td>
                            <td data-label="LGPD">
                                <span class="lgpdBadge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                            </td>
                            <td data-label="Data"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
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
</body>
</html>
