<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$pdo = conectar();

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME')->fetchAll();
$fases = $pdo->query('SELECT FAS_ID, FAS_NOME FROM FASES ORDER BY FAS_ORDEM ASC')->fetchAll();

$filtroModalidade = isset($_GET['modalidade']) && inteiroValido($_GET['modalidade']) ? (int) $_GET['modalidade'] : null;
$filtroFase = isset($_GET['fase']) && inteiroValido($_GET['fase']) ? (int) $_GET['fase'] : null;
$filtroData = isset($_GET['data']) && dataValida($_GET['data']) ? $_GET['data'] : null;

$sql = 'SELECT c.CON_ID, c.CON_DATA, c.CON_HORA,
               m.MOD_NOME, f.FAS_NOME,
               tur1.TUR_SERIE AS TIME1_SERIE,
               tur2.TUR_SERIE AS TIME2_SERIE
        FROM CONFRONTOS c
        JOIN CHAVES cha ON cha.CHA_ID = c.FK_CHA_ID
        JOIN MODALIDADES m ON m.MOD_ID = cha.FK_MOD_ID
        JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
        JOIN TIMES t1 ON t1.TIM_ID = c.FK_TIM_1_ID
        JOIN TIMES t2 ON t2.TIM_ID = c.FK_TIM_2_ID
        JOIN TURMAS tur1 ON tur1.TUR_ID = t1.FK_TUR_ID
        JOIN TURMAS tur2 ON tur2.TUR_ID = t2.FK_TUR_ID
        LEFT JOIN RESULTADOS r ON r.FK_CON_ID = c.CON_ID
        WHERE r.RES_ID IS NULL';
$params = [];

if ($filtroModalidade) {
    $sql .= ' AND m.MOD_ID = :modalidade';
    $params['modalidade'] = $filtroModalidade;
}
if ($filtroFase) {
    $sql .= ' AND f.FAS_ID = :fase';
    $params['fase'] = $filtroFase;
}
if ($filtroData) {
    $sql .= ' AND c.CON_DATA = :data';
    $params['data'] = $filtroData;
}

$sql .= ' ORDER BY c.CON_DATA ASC, c.CON_HORA ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jogos = $stmt->fetchAll();

$paginaAtual = 'jogos';
$raizPublica = '../';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jogos · INTERCLASSE SESI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header_publico.php'; ?>

<section class="stats-section">
    <div class="container">
        <div class="section-title">
            <h2>Programação dos jogos</h2>
            <p>Próximos confrontos do interclasse</p>
        </div>

        <form method="GET" action="jogos.php" id="filtro-jogos" class="filters-bar">
            <div class="filter-field">
                <label for="modalidade">Modalidade</label>
                <select name="modalidade" id="modalidade">
                    <option value="">Todas</option>
                    <?php foreach ($modalidades as $m): ?>
                        <option value="<?= (int) $m['MOD_ID'] ?>" <?= $filtroModalidade === (int) $m['MOD_ID'] ? 'selected' : '' ?>><?= e($m['MOD_NOME']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label for="fase">Fase</label>
                <select name="fase" id="fase">
                    <option value="">Todas</option>
                    <?php foreach ($fases as $f): ?>
                        <option value="<?= (int) $f['FAS_ID'] ?>" <?= $filtroFase === (int) $f['FAS_ID'] ? 'selected' : '' ?>><?= e($f['FAS_NOME']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-field">
                <label for="data">Data</label>
                <input type="date" name="data" id="data" value="<?= e($filtroData ?? '') ?>">
            </div>
            <div class="filter-field" style="flex:0;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
            <?php if ($filtroModalidade || $filtroFase || $filtroData): ?>
            <div class="filter-field" style="flex:0;">
                <a href="jogos.php" class="btn btn-outline-dark">Limpar</a>
            </div>
            <?php endif; ?>
        </form>

        <?php if (empty($jogos)): ?>
            <div class="empty-state">Nenhum jogo encontrado para os filtros selecionados.</div>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($jogos as $jogo): ?>
                    <div class="game-card">
                        <div class="game-card-header">
                            <span class="badge badge-primary"><?= e($jogo['MOD_NOME']) ?></span>
                            <span class="badge badge-accent"><?= e($jogo['FAS_NOME']) ?></span>
                        </div>
                        <div class="game-matchup">
                            <span class="team"><?= e($jogo['TIME1_SERIE']) ?></span>
                            <span class="vs">x</span>
                            <span class="team"><?= e($jogo['TIME2_SERIE']) ?></span>
                        </div>
                        <div class="game-meta">
                            <span>&#128197; <?= formatarData($jogo['CON_DATA']) ?></span>
                            <span>&#128337; <?= formatarHora($jogo['CON_HORA']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer_publico.php'; ?>
<script>inicializarAutoSubmitFiltro('filtro-jogos');</script>
</body>
</html>
