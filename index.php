<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/includes/funcoes.php';

$pdo = conectar();

$totalModalidades = (int) $pdo->query(
    'SELECT COUNT(*) FROM MODALIDADES'
)->fetchColumn();

$totalTimes = (int) $pdo->query(
    'SELECT COUNT(*) FROM TIMES'
)->fetchColumn();

$totalJogosRealizados = (int) $pdo->query(
    'SELECT COUNT(*) FROM RESULTADOS'
)->fetchColumn();

$totalProximosJogos = (int) $pdo->query(
    'SELECT COUNT(*)
     FROM CONFRONTOS c
     LEFT JOIN RESULTADOS r ON r.FK_CON_ID = c.CON_ID
     WHERE r.RES_ID IS NULL'
)->fetchColumn();


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

$paginaAtual = 'inicio';
$raizPublica = '';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>INTERCLASSE SESI</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php include __DIR__ . '/includes/header_publico.php'; ?>


<!-- ======================================================
     HERO
     ====================================================== -->

<section class="hero">

    <div class="container">

        <h1>INTERCLASSE SESI</h1>

        <p class="subtitle">
            Acompanhe os jogos, resultados e a trajetória das equipes.
        </p>

        <div class="hero-actions">

            <a
                href="publico/jogos.php"
                class="btn btn-accent"
            >
                Ver próximos jogos
            </a>

            <a
                href="publico/resultados.php"
                class="btn btn-outline"
            >
                Ver resultados
            </a>

        </div>

    </div>

</section>


<!-- ======================================================
     ESTATÍSTICAS
     ====================================================== -->

<section class="stats-section">

    <div class="container">

        <div class="stats-grid">

            <div class="stat-card">

                <div class="stat-value">
                    <?= $totalModalidades ?>
                </div>

                <div class="stat-label">
                    Modalidades
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-value">
                    <?= $totalTimes ?>
                </div>

                <div class="stat-label">
                    Times participantes
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-value">
                    <?= $totalJogosRealizados ?>
                </div>

                <div class="stat-label">
                    Jogos realizados
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-value">
                    <?= $totalProximosJogos ?>
                </div>

                <div class="stat-label">
                    Próximos jogos
                </div>

            </div>

        </div>

    </div>

</section>


<!-- ======================================================
     PRÓXIMOS JOGOS
     ====================================================== -->

<section class="stats-section">

    <div class="container">

        <div class="section-title">

            <h2>
                Próximos jogos
            </h2>

            <p>
                Confira os próximos confrontos do Interclasse SESI.
            </p>

        </div>


        <?php if (empty($proximosJogos)): ?>

            <div class="empty-state">

                <h3>
                    Nenhum jogo agendado
                </h3>

                <p>
                    No momento não existem confrontos cadastrados
                    para os próximos jogos.
                </p>

            </div>

        <?php else: ?>

            <div class="games-grid">

                <?php foreach ($proximosJogos as $jogo): ?>

                    <div class="game-card">


                        <!-- Modalidade e fase -->

                        <div class="game-card-header">

                            <span class="badge badge-primary">
                                <?= e($jogo['MOD_NOME']) ?>
                            </span>

                            <span class="badge badge-accent">
                                <?= e($jogo['FAS_NOME']) ?>
                            </span>

                        </div>


                        <!-- Confronto -->

                        <div class="game-matchup">

                            <span class="team">
                                <?= e($jogo['TIME1_SERIE']) ?>
                            </span>

                            <span class="vs">
                                X
                            </span>

                            <span class="team">
                                <?= e($jogo['TIME2_SERIE']) ?>
                            </span>

                        </div>


                        <!-- Data e horário -->

                        <div class="game-meta">

                            <span>
                                &#128197;
                                <?= formatarData($jogo['CON_DATA']) ?>
                            </span>

                            <span>
                                &#128337;
                                <?= formatarHora($jogo['CON_HORA']) ?>
                            </span>

                        </div>


                    </div>

                <?php endforeach; ?>

            </div>


            <!-- Ver todos -->

            <div class="section-action">

                <a
                    href="publico/jogos.php"
                    class="btn btn-outline-dark"
                >
                    Ver todos os jogos
                </a>

            </div>

        <?php endif; ?>

    </div>

</section>


<?php include __DIR__ . '/includes/footer_publico.php'; ?>


</body>

</html>
