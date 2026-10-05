<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$pdo = conectar();

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME')->fetchAll();

$modalidadeSelecionada = isset($_GET['modalidade']) && inteiroValido($_GET['modalidade'])
    ? (int) $_GET['modalidade']
    : (($modalidades[0]['MOD_ID'] ?? null) ? (int) $modalidades[0]['MOD_ID'] : null);

$chaves = [];
if ($modalidadeSelecionada) {
    $stmtChaves = $pdo->prepare('SELECT CHA_ID, CHA_NOME FROM CHAVES WHERE FK_MOD_ID = :mod ORDER BY CHA_NOME');
    $stmtChaves->execute(['mod' => $modalidadeSelecionada]);
    $chaves = $stmtChaves->fetchAll();
}

$stmtConfrontos = $pdo->prepare(
    'SELECT c.CON_ID, c.CON_DATA, c.CON_HORA,
            f.FAS_NOME, f.FAS_ORDEM,
            t1.TIM_ID AS TIME1_ID, tur1.TUR_SERIE AS TIME1_SERIE,
            t2.TIM_ID AS TIME2_ID, tur2.TUR_SERIE AS TIME2_SERIE,
            r.RES_PONTUACAO_TIME_1, r.RES_PONTUACAO_TIME_2, r.FK_TIM_VENCEDOR_ID
     FROM CONFRONTOS c
     JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
     LEFT JOIN TIMES t1 ON t1.TIM_ID = c.FK_TIM_1_ID
     LEFT JOIN TIMES t2 ON t2.TIM_ID = c.FK_TIM_2_ID
     LEFT JOIN TURMAS tur1 ON tur1.TUR_ID = t1.FK_TUR_ID
     LEFT JOIN TURMAS tur2 ON tur2.TUR_ID = t2.FK_TUR_ID
     LEFT JOIN RESULTADOS r ON r.FK_CON_ID = c.CON_ID
     WHERE c.FK_CHA_ID = :cha
     ORDER BY f.FAS_ORDEM ASC, c.CON_ID ASC'
);

$paginaAtual = 'chaves';
$raizPublica = '../';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chaves · INTERCLASSE SESI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/header_publico.php'; ?>

<section class="stats-section">
    <div class="container">
        <div class="section-title">
            <h2>Chaves</h2>
            <p>Chaveamento das modalidades</p>
        </div>

        <form method="GET" action="chaves.php" id="filtro-chaves" class="filters-bar">
            <div class="filter-field">
                <label for="modalidade">Modalidade</label>
                <select name="modalidade" id="modalidade">
                    <?php foreach ($modalidades as $m): ?>
                        <option value="<?= (int) $m['MOD_ID'] ?>" <?= $modalidadeSelecionada === (int) $m['MOD_ID'] ? 'selected' : '' ?>><?= e($m['MOD_NOME']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (empty($modalidades)): ?>
            <div class="empty-state">Nenhuma modalidade cadastrada.</div>
        <?php elseif (empty($chaves)): ?>
            <div class="empty-state">Nenhuma chave cadastrada para esta modalidade.</div>
        <?php else: ?>
            <?php foreach ($chaves as $chave): ?>
                <?php
                $stmtConfrontos->execute(['cha' => $chave['CHA_ID']]);
                $confrontos = $stmtConfrontos->fetchAll();

                $porFase = [];
                foreach ($confrontos as $con) {
                    $porFase[$con['FAS_NOME']][] = $con;
                }
                ?>
                <div class="chave-block">
                    <div class="chave-block-title"><?= e($chave['CHA_NOME']) ?></div>
                    <div class="chave-block-sub"><?= count($confrontos) ?> confronto(s) cadastrado(s)</div>

                    <?php if (empty($confrontos)): ?>
                        <div class="empty-state">Nenhum confronto cadastrado nesta chave.</div>
                    <?php else: ?>
                        <div class="bracket">
                            <?php foreach ($porFase as $nomeFase => $listaConfrontos): ?>
                                <div class="bracket-round">
                                    <div class="bracket-round-title"><?= e($nomeFase) ?></div>
                                    <?php foreach ($listaConfrontos as $con): ?>
                                        <?php
                                        $temResultado = $con['RES_PONTUACAO_TIME_1'] !== null;
                                        $venceu1 = $temResultado && (int) $con['FK_TIM_VENCEDOR_ID'] === (int) $con['TIME1_ID'];
                                        $venceu2 = $temResultado && (int) $con['FK_TIM_VENCEDOR_ID'] === (int) $con['TIME2_ID'];
                                        $time1Definido = $con['TIME1_ID'] !== null;
                                        $time2Definido = $con['TIME2_ID'] !== null;
                                        ?>
                                        <div class="bracket-match">
                                            <div class="bracket-team <?= $venceu1 ? 'winner' : '' ?> <?= !$time1Definido ? 'pending' : '' ?>">
                                                <span><?= $time1Definido ? e($con['TIME1_SERIE']) : 'Aguardando definição' ?></span>
                                                <?php if ($temResultado): ?><span class="score"><?= (int) $con['RES_PONTUACAO_TIME_1'] ?></span><?php endif; ?>
                                            </div>
                                            <div class="bracket-team <?= $venceu2 ? 'winner' : '' ?> <?= !$time2Definido ? 'pending' : '' ?>">
                                                <span><?= $time2Definido ? e($con['TIME2_SERIE']) : 'Aguardando definição' ?></span>
                                                <?php if ($temResultado): ?><span class="score"><?= (int) $con['RES_PONTUACAO_TIME_2'] ?></span><?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!$temResultado && $time1Definido && $time2Definido): ?>
                                            <div class="bracket-pending">Aguardando resultado</div>
                                        <?php elseif (!$time1Definido || !$time2Definido): ?>
                                            <div class="bracket-pending">Aguardando vencedor da fase anterior</div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer_publico.php'; ?>
<script>inicializarAutoSubmitFiltro('filtro-chaves');</script>
</body>
</html>
