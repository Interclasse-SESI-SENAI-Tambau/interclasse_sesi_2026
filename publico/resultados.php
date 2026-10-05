<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$pdo = conectar();

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME')->fetchAll();
$filtroModalidade = isset($_GET['modalidade']) && inteiroValido($_GET['modalidade']) ? (int) $_GET['modalidade'] : null;

$sql = 'SELECT c.CON_ID, c.CON_DATA,
               m.MOD_NOME, f.FAS_NOME,
               tur1.TUR_SERIE AS TIME1_SERIE,
               tur2.TUR_SERIE AS TIME2_SERIE,
               r.RES_PONTUACAO_TIME_1, r.RES_PONTUACAO_TIME_2,
               turV.TUR_SERIE AS VENCEDOR_SERIE
        FROM RESULTADOS r
        JOIN CONFRONTOS c ON c.CON_ID = r.FK_CON_ID
        JOIN CHAVES cha ON cha.CHA_ID = c.FK_CHA_ID
        JOIN MODALIDADES m ON m.MOD_ID = cha.FK_MOD_ID
        JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
        JOIN TIMES t1 ON t1.TIM_ID = c.FK_TIM_1_ID
        JOIN TIMES t2 ON t2.TIM_ID = c.FK_TIM_2_ID
        JOIN TURMAS tur1 ON tur1.TUR_ID = t1.FK_TUR_ID
        JOIN TURMAS tur2 ON tur2.TUR_ID = t2.FK_TUR_ID
        JOIN TIMES tv ON tv.TIM_ID = r.FK_TIM_VENCEDOR_ID
        JOIN TURMAS turV ON turV.TUR_ID = tv.FK_TUR_ID';
$params = [];
if ($filtroModalidade) {
    $sql .= ' WHERE m.MOD_ID = :modalidade';
    $params['modalidade'] = $filtroModalidade;
}
$sql .= ' ORDER BY c.CON_DATA DESC, c.CON_HORA DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll();

$paginaAtual = 'resultados';
$raizPublica = '../';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados · INTERCLASSE SESI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header_publico.php'; ?>

<section class="stats-section">
    <div class="container">
        <div class="section-title">
            <h2>Resultados</h2>
            <p>Confrontos já finalizados</p>
        </div>

        <form method="GET" action="resultados.php" id="filtro-resultados" class="filters-bar">
            <div class="filter-field">
                <label for="modalidade">Modalidade</label>
                <select name="modalidade" id="modalidade">
                    <option value="">Todas</option>
                    <?php foreach ($modalidades as $m): ?>
                        <option value="<?= (int) $m['MOD_ID'] ?>" <?= $filtroModalidade === (int) $m['MOD_ID'] ? 'selected' : '' ?>><?= e($m['MOD_NOME']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (empty($resultados)): ?>
            <div class="empty-state">Nenhum resultado registrado ainda.</div>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($resultados as $r): ?>
                    <div class="game-card">
                        <div class="game-card-header">
                            <span class="badge badge-primary"><?= e($r['MOD_NOME']) ?></span>
                            <span class="badge badge-accent"><?= e($r['FAS_NOME']) ?></span>
                        </div>
                        <div class="game-matchup">
                            <span class="team"><?= e($r['TIME1_SERIE']) ?></span>
                        </div>
                        <div class="game-result-score">
                            <?= (int) $r['RES_PONTUACAO_TIME_1'] ?>
                            <span class="score-sep">x</span>
                            <?= (int) $r['RES_PONTUACAO_TIME_2'] ?>
                        </div>
                        <div class="game-matchup">
                            <span class="team"><?= e($r['TIME2_SERIE']) ?></span>
                        </div>
                        <div class="winner-tag">Vencedor: <?= e($r['VENCEDOR_SERIE']) ?></div>
                        <div class="game-meta">
                            <span>&#128197; <?= formatarData($r['CON_DATA']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer_publico.php'; ?>
<script>inicializarAutoSubmitFiltro('filtro-resultados');</script>
</body>
</html>
