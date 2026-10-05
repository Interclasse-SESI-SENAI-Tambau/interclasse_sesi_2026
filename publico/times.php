<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$pdo = conectar();

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME')->fetchAll();

$filtroModalidade = isset($_GET['modalidade']) && inteiroValido($_GET['modalidade']) ? (int) $_GET['modalidade'] : null;

$sql = 'SELECT t.TIM_ID, tur.TUR_SERIE, m.MOD_NOME
        FROM TIMES t
        JOIN TURMAS tur ON tur.TUR_ID = t.FK_TUR_ID
        JOIN MODALIDADES m ON m.MOD_ID = t.FK_MOD_ID';
$params = [];
if ($filtroModalidade) {
    $sql .= ' WHERE t.FK_MOD_ID = :modalidade';
    $params['modalidade'] = $filtroModalidade;
}
$sql .= ' ORDER BY m.MOD_NOME ASC, tur.TUR_SERIE ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$times = $stmt->fetchAll();

$paginaAtual = 'times';
$raizPublica = '../';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Times · INTERCLASSE SESI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header_publico.php'; ?>

<section class="stats-section">
    <div class="container">
        <div class="section-title">
            <h2>Times participantes</h2>
            <p>Equipes inscritas por modalidade</p>
        </div>

        <form method="GET" action="times.php" id="filtro-times" class="filters-bar">
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

        <?php if (empty($times)): ?>
            <div class="empty-state">Nenhum time cadastrado.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Time (Turma)</th>
                            <th>Modalidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($times as $t): ?>
                            <tr>
                                <td><?= e($t['TUR_SERIE']) ?></td>
                                <td><span class="badge badge-primary"><?= e($t['MOD_NOME']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer_publico.php'; ?>
<script>inicializarAutoSubmitFiltro('filtro-times');</script>
</body>
</html>
