
<?php

require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$pdo = conectar();

/*
|--------------------------------------------------------------------------
| CONTADORES
|--------------------------------------------------------------------------
*/

$totalModalidades = (int) $pdo->query(
    'SELECT COUNT(*) FROM MODALIDADES'
)->fetchColumn();

$totalTurmas = (int) $pdo->query(
    'SELECT COUNT(*) FROM TURMAS'
)->fetchColumn();

$totalTimes = (int) $pdo->query(
    'SELECT COUNT(*) FROM TIMES'
)->fetchColumn();

$totalConfrontos = (int) $pdo->query(
    'SELECT COUNT(*) FROM CONFRONTOS'
)->fetchColumn();

$totalJogosRealizados = (int) $pdo->query(
    'SELECT COUNT(*) FROM RESULTADOS'
)->fetchColumn();


/*
|--------------------------------------------------------------------------
| PRÓXIMOS JOGOS
|--------------------------------------------------------------------------
*/

$stmtProximos = $pdo->query(

    'SELECT
        c.CON_DATA,
        c.CON_HORA,
        m.MOD_NOME,
        f.FAS_NOME,
        tur1.TUR_SERIE AS TIME1_SERIE,
        tur2.TUR_SERIE AS TIME2_SERIE

     FROM CONFRONTOS c

     JOIN CHAVES cha
        ON cha.CHA_ID = c.FK_CHA_ID

     JOIN MODALIDADES m
        ON m.MOD_ID = cha.FK_MOD_ID

     JOIN FASES f
        ON f.FAS_ID = c.FK_FAS_ID

     JOIN TIMES t1
        ON t1.TIM_ID = c.FK_TIM_1_ID

     JOIN TIMES t2
        ON t2.TIM_ID = c.FK_TIM_2_ID

     JOIN TURMAS tur1
        ON tur1.TUR_ID = t1.FK_TUR_ID

     JOIN TURMAS tur2
        ON tur2.TUR_ID = t2.FK_TUR_ID

     LEFT JOIN RESULTADOS r
        ON r.FK_CON_ID = c.CON_ID

     WHERE r.RES_ID IS NULL

     ORDER BY
        c.CON_DATA ASC,
        c.CON_HORA ASC

     LIMIT 6'

);

$proximosJogos = $stmtProximos->fetchAll();


/*
|--------------------------------------------------------------------------
| ÚLTIMOS RESULTADOS
|--------------------------------------------------------------------------
*/

$stmtUltimos = $pdo->query(

    'SELECT
        m.MOD_NOME,
        tur1.TUR_SERIE AS TIME1_SERIE,
        tur2.TUR_SERIE AS TIME2_SERIE,
        r.RES_PONTUACAO_TIME_1,
        r.RES_PONTUACAO_TIME_2,
        turV.TUR_SERIE AS VENCEDOR_SERIE

     FROM RESULTADOS r

     JOIN CONFRONTOS c
        ON c.CON_ID = r.FK_CON_ID

     JOIN CHAVES cha
        ON cha.CHA_ID = c.FK_CHA_ID

     JOIN MODALIDADES m
        ON m.MOD_ID = cha.FK_MOD_ID

     JOIN TIMES t1
        ON t1.TIM_ID = c.FK_TIM_1_ID

     JOIN TIMES t2
        ON t2.TIM_ID = c.FK_TIM_2_ID

     JOIN TURMAS tur1
        ON tur1.TUR_ID = t1.FK_TUR_ID

     JOIN TURMAS tur2
        ON tur2.TUR_ID = t2.FK_TUR_ID

     JOIN TIMES tv
        ON tv.TIM_ID = r.FK_TIM_VENCEDOR_ID

     JOIN TURMAS turV
        ON turV.TUR_ID = tv.FK_TUR_ID

     ORDER BY r.RES_ID DESC

     LIMIT 6'

);

$ultimosResultados = $stmtUltimos->fetchAll();


$paginaAtual = 'dashboard';
$tituloPagina = 'Dashboard';

include __DIR__ . '/../includes/header_admin.php';

?>

<style>

/* ==========================================================
   DASHBOARD
   ========================================================== */

.dashboard-page {
    width: 100%;
}


/* ==========================================================
   CABEÇALHO
   ========================================================== */

.dashboard-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.dashboard-kicker {
    display: block;
    color: #0a66c2;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.dashboard-page-header h1 {
    margin: 0;
    color: #0f2a44;
    font-size: 30px;
    font-weight: 800;
}

.dashboard-description {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
}


/* ==========================================================
   CARDS DE ESTATÍSTICAS
   ========================================================== */

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.dashboard-card {
    position: relative;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15, 42, 68, 0.06);
    transition: 0.2s;
}

.dashboard-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #0a66c2;
}

.dashboard-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 9px 24px rgba(15, 42, 68, 0.10);
}

.dashboard-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eaf3fc;
    color: #0a66c2;
    font-size: 17px;
    font-weight: 800;
    margin-bottom: 15px;
}

.dashboard-card-value {
    color: #0f2a44;
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
}

.dashboard-card-label {
    margin-top: 7px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
}


/* ==========================================================
   PAINÉIS
   ========================================================== */

.dashboard-panels-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.dashboard-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15, 42, 68, 0.06);
}

.dashboard-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 17px 20px;
    background: #0f2a44;
    color: #ffffff;
}

.dashboard-panel-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 800;
}

.dashboard-panel-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
}

.dashboard-panel-count {
    min-width: 25px;
    height: 25px;
    padding: 0 7px;
    border-radius: 20px;
    background: #0a66c2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
}

.dashboard-panel-body {
    padding: 0;
}


/* ==========================================================
   ITENS DOS PAINÉIS
   ========================================================== */

.dashboard-list-item {
    padding: 17px 20px;
    border-bottom: 1px solid #e2e8f0;
    transition: 0.15s;
}

.dashboard-list-item:last-child {
    border-bottom: none;
}

.dashboard-list-item:hover {
    background: #f8fbff;
}

.dashboard-list-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.dashboard-match {
    color: #0f2a44;
    font-size: 14px;
    font-weight: 800;
}

.dashboard-list-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
    color: #64748b;
    font-size: 12px;
}


/* ==========================================================
   BADGES
   ========================================================== */

.dashboard-badge {
    display: inline-flex;
    align-items: center;
    max-width: 150px;
    padding: 5px 9px;
    border-radius: 7px;
    background: #eaf3fc;
    color: #0a66c2;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


/* ==========================================================
   DATA E HORÁRIO
   ========================================================== */

.dashboard-date {
    display: flex;
    align-items: center;
    gap: 5px;
}

.dashboard-time {
    font-weight: 700;
    color: #0f2a44;
}


/* ==========================================================
   RESULTADO
   ========================================================== */

.dashboard-score {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #0f2a44;
    font-weight: 800;
}

.dashboard-score-number {
    font-size: 16px;
}

.dashboard-score-x {
    color: #94a3b8;
    font-size: 11px;
}


/* ==========================================================
   VENCEDOR
   ========================================================== */

.dashboard-winner {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #0a66c2;
    font-weight: 700;
}


/* ==========================================================
   ESTADO VAZIO
   ========================================================== */

.dashboard-empty {
    padding: 45px 20px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

.dashboard-empty-icon {
    width: 46px;
    height: 46px;
    margin: 0 auto 12px;
    border-radius: 12px;
    background: #eaf3fc;
    color: #0a66c2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 800;
}


/* ==========================================================
   RESPONSIVO
   ========================================================== */

@media (max-width: 1200px) {

    .dashboard-grid {
        grid-template-columns: repeat(3, 1fr);
    }

}

@media (max-width: 900px) {

    .dashboard-panels-grid {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 650px) {

    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .dashboard-page-header h1 {
        font-size: 25px;
    }

    .dashboard-list-top {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

}

@media (max-width: 450px) {

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-list-bottom {
        align-items: flex-start;
        flex-direction: column;
    }

}

</style>


<div class="dashboard-page">

    <!-- ======================================================
         CABEÇALHO
         ====================================================== -->

    <div class="dashboard-page-header">

        <div>

            <span class="dashboard-kicker">
                Administração
            </span>

            <h1>
                Dashboard
            </h1>

            <p class="dashboard-description">
                Visão geral do Interclasse SESI.
            </p>

        </div>

    </div>


    <!-- ======================================================
         CARDS
         ====================================================== -->

    <div class="dashboard-grid">


        <!-- Modalidades -->

        <div class="dashboard-card">

            <div class="dashboard-card-icon">
                M
            </div>

            <div class="dashboard-card-value">
                <?= $totalModalidades ?>
            </div>

            <div class="dashboard-card-label">
                Modalidades
            </div>

        </div>


        <!-- Turmas -->

        <div class="dashboard-card">

            <div class="dashboard-card-icon">
                T
            </div>

            <div class="dashboard-card-value">
                <?= $totalTurmas ?>
            </div>

            <div class="dashboard-card-label">
                Turmas
            </div>

        </div>


        <!-- Times -->

        <div class="dashboard-card">

            <div class="dashboard-card-icon">
                +
            </div>

            <div class="dashboard-card-value">
                <?= $totalTimes ?>
            </div>

            <div class="dashboard-card-label">
                Times
            </div>

        </div>


        <!-- Confrontos -->

        <div class="dashboard-card">

            <div class="dashboard-card-icon">
                VS
            </div>

            <div class="dashboard-card-value">
                <?= $totalConfrontos ?>
            </div>

            <div class="dashboard-card-label">
                Confrontos
            </div>

        </div>


        <!-- Jogos realizados -->

        <div class="dashboard-card">

            <div class="dashboard-card-icon">
                ✓
            </div>

            <div class="dashboard-card-value">
                <?= $totalJogosRealizados ?>
            </div>

            <div class="dashboard-card-label">
                Jogos realizados
            </div>

        </div>

    </div>


    <!-- ======================================================
         PAINÉIS
         ====================================================== -->

    <div class="dashboard-panels-grid">


        <!-- ==================================================
             PRÓXIMOS JOGOS
             ================================================== -->

        <div class="dashboard-panel">

            <div class="dashboard-panel-header">

                <div class="dashboard-panel-title">

                    <div class="dashboard-panel-icon">
                        →
                    </div>

                    Próximos jogos

                </div>

                <div class="dashboard-panel-count">
                    <?= count($proximosJogos) ?>
                </div>

            </div>


            <div class="dashboard-panel-body">

                <?php if (empty($proximosJogos)): ?>

                    <div class="dashboard-empty">

                        <div class="dashboard-empty-icon">
                            —
                        </div>

                        Nenhum jogo agendado.

                    </div>

                <?php else: ?>

                    <?php foreach ($proximosJogos as $j): ?>

                        <div class="dashboard-list-item">

                            <div class="dashboard-list-top">

                                <span class="dashboard-match">

                                    <?= e($j['TIME1_SERIE']) ?>

                                    <span style="color:#94a3b8;">
                                        x
                                    </span>

                                    <?= e($j['TIME2_SERIE']) ?>

                                </span>

                                <span class="dashboard-badge">

                                    <?= e($j['MOD_NOME']) ?>

                                </span>

                            </div>


                            <div class="dashboard-list-bottom">

                                <span>
                                    <?= e($j['FAS_NOME']) ?>
                                </span>

                                <span class="dashboard-date">

                                    <?= formatarData($j['CON_DATA']) ?>

                                    <span>•</span>

                                    <span class="dashboard-time">
                                        <?= formatarHora($j['CON_HORA']) ?>
                                    </span>

                                </span>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>


        <!-- ==================================================
             ÚLTIMOS RESULTADOS
             ================================================== -->

        <div class="dashboard-panel">

            <div class="dashboard-panel-header">

                <div class="dashboard-panel-title">

                    <div class="dashboard-panel-icon">
                        ✓
                    </div>

                    Últimos resultados

                </div>

                <div class="dashboard-panel-count">
                    <?= count($ultimosResultados) ?>
                </div>

            </div>


            <div class="dashboard-panel-body">

                <?php if (empty($ultimosResultados)): ?>

                    <div class="dashboard-empty">

                        <div class="dashboard-empty-icon">
                            —
                        </div>

                        Nenhum resultado registrado.

                    </div>

                <?php else: ?>

                    <?php foreach ($ultimosResultados as $r): ?>

                        <div class="dashboard-list-item">

                            <div class="dashboard-list-top">

                                <div class="dashboard-score">

                                    <span>
                                        <?= e($r['TIME1_SERIE']) ?>
                                    </span>

                                    <span class="dashboard-score-number">
                                        <?= (int) $r['RES_PONTUACAO_TIME_1'] ?>
                                    </span>

                                    <span class="dashboard-score-x">
                                        x
                                    </span>

                                    <span class="dashboard-score-number">
                                        <?= (int) $r['RES_PONTUACAO_TIME_2'] ?>
                                    </span>

                                    <span>
                                        <?= e($r['TIME2_SERIE']) ?>
                                    </span>

                                </div>

                                <span class="dashboard-badge">

                                    <?= e($r['MOD_NOME']) ?>

                                </span>

                            </div>


                            <div class="dashboard-list-bottom">

                                <span class="dashboard-winner">

                                    Vencedor:

                                    <strong>
                                        <?= e($r['VENCEDOR_SERIE']) ?>
                                    </strong>

                                </span>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
