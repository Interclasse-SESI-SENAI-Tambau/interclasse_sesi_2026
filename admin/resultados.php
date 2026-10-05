<?php
require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/chaveamento.php';
exigirLogin();

$pdo = conectar();

/**
 * Confirma que o time vencedor informado é de fato um dos dois times
 * daquele confronto (nunca confiar apenas no select do front-end).
 */
function vencedorPertenceAoConfronto(PDO $pdo, int $conId, int $vencedorId): ?array
{
    $stmt = $pdo->prepare('SELECT FK_TIM_1_ID, FK_TIM_2_ID FROM CONFRONTOS WHERE CON_ID = :id');
    $stmt->execute(['id' => $conId]);
    $confronto = $stmt->fetch();

    if (!$confronto) {
        return null;
    }

    if ($vencedorId !== (int) $confronto['FK_TIM_1_ID'] && $vencedorId !== (int) $confronto['FK_TIM_2_ID']) {
        return null;
    }

    return $confronto;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'registrar' || $acao === 'editar') {
        $conId = $_POST['con_id'] ?? null;
        $pontos1 = $_POST['pontuacao_1'] ?? null;
        $pontos2 = $_POST['pontuacao_2'] ?? null;
        $vencedorId = $_POST['fk_tim_vencedor_id'] ?? null;

        if (!inteiroValido($conId) || !inteiroValido($vencedorId)
            || !inteiroValido($pontos1) || !inteiroValido($pontos2)
            || (int) $pontos1 < 0 || (int) $pontos2 < 0) {
            definirMensagem('danger', 'Preencha a pontuação (números inteiros não negativos) e selecione o vencedor.');
            redirecionar('resultados.php');
        }

        $confronto = vencedorPertenceAoConfronto($pdo, (int) $conId, (int) $vencedorId);
        if (!$confronto) {
            definirMensagem('danger', 'O vencedor selecionado não participa deste confronto.');
            redirecionar('resultados.php');
        }

        try {
            $pdo->beginTransaction();

            if ($acao === 'registrar') {
                $stmtExiste = $pdo->prepare('SELECT RES_ID FROM RESULTADOS WHERE FK_CON_ID = :con');
                $stmtExiste->execute(['con' => (int) $conId]);
                if ($stmtExiste->fetch()) {
                    $pdo->rollBack();
                    definirMensagem('danger', 'Este confronto já possui um resultado registrado. Utilize a opção de edição.');
                    redirecionar('resultados.php');
                }

                $stmt = $pdo->prepare(
                    'INSERT INTO RESULTADOS (FK_CON_ID, RES_PONTUACAO_TIME_1, RES_PONTUACAO_TIME_2, FK_TIM_VENCEDOR_ID)
                     VALUES (:con, :p1, :p2, :vencedor)'
                );
                $stmt->execute([
                    'con' => (int) $conId,
                    'p1' => (int) $pontos1,
                    'p2' => (int) $pontos2,
                    'vencedor' => (int) $vencedorId,
                ]);
                avancarVencedor($pdo, (int) $conId);
                $pdo->commit();
                definirMensagem('success', 'Resultado cadastrado com sucesso.');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE RESULTADOS SET RES_PONTUACAO_TIME_1 = :p1, RES_PONTUACAO_TIME_2 = :p2, FK_TIM_VENCEDOR_ID = :vencedor
                     WHERE FK_CON_ID = :con'
                );
                $stmt->execute([
                    'p1' => (int) $pontos1,
                    'p2' => (int) $pontos2,
                    'vencedor' => (int) $vencedorId,
                    'con' => (int) $conId,
                ]);
                avancarVencedor($pdo, (int) $conId);
                $pdo->commit();
                definirMensagem('success', 'Resultado atualizado com sucesso.');
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            definirMensagem('danger', 'Não foi possível salvar o resultado.');
        }
    }

    redirecionar('resultados.php');
}

$pendentes = $pdo->query(
    'SELECT c.CON_ID, c.CON_DATA, c.CON_HORA, m.MOD_NOME, f.FAS_NOME,
            c.FK_TIM_1_ID, c.FK_TIM_2_ID, tur1.TUR_SERIE AS TIME1_SERIE, tur2.TUR_SERIE AS TIME2_SERIE
     FROM CONFRONTOS c
     JOIN CHAVES cha ON cha.CHA_ID = c.FK_CHA_ID
     JOIN MODALIDADES m ON m.MOD_ID = cha.FK_MOD_ID
     JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
     JOIN TIMES t1 ON t1.TIM_ID = c.FK_TIM_1_ID
     JOIN TIMES t2 ON t2.TIM_ID = c.FK_TIM_2_ID
     JOIN TURMAS tur1 ON tur1.TUR_ID = t1.FK_TUR_ID
     JOIN TURMAS tur2 ON tur2.TUR_ID = t2.FK_TUR_ID
     LEFT JOIN RESULTADOS r ON r.FK_CON_ID = c.CON_ID
     WHERE r.RES_ID IS NULL
     ORDER BY c.CON_DATA ASC, c.CON_HORA ASC'
)->fetchAll();

$registrados = $pdo->query(
    'SELECT c.CON_ID, c.CON_DATA, m.MOD_NOME, f.FAS_NOME,
            c.FK_TIM_1_ID, c.FK_TIM_2_ID, tur1.TUR_SERIE AS TIME1_SERIE, tur2.TUR_SERIE AS TIME2_SERIE,
            r.RES_PONTUACAO_TIME_1, r.RES_PONTUACAO_TIME_2, r.FK_TIM_VENCEDOR_ID, turV.TUR_SERIE AS VENCEDOR_SERIE
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
     JOIN TURMAS turV ON turV.TUR_ID = tv.FK_TUR_ID
     ORDER BY c.CON_DATA DESC'
)->fetchAll();

$paginaAtual = 'resultados';
$tituloPagina = 'Resultados';
include __DIR__ . '/../includes/header_admin.php';
?>

<div class="page-header">
    <h1>Confrontos aguardando resultado</h1>
</div>

<?php if (empty($pendentes)): ?>
    <div class="empty-state" style="margin-bottom:32px;">Não há confrontos pendentes de resultado.</div>
<?php else: ?>
    <div class="table-wrapper" style="margin-bottom:36px;">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Modalidade</th>
                    <th>Fase</th>
                    <th>Confronto</th>
                    <th style="width:170px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendentes as $p): ?>
                    <tr>
                        <td><?= formatarData($p['CON_DATA']) ?> <?= formatarHora($p['CON_HORA']) ?></td>
                        <td><span class="badge badge-primary"><?= e($p['MOD_NOME']) ?></span></td>
                        <td><?= e($p['FAS_NOME']) ?></td>
                        <td><?= e($p['TIME1_SERIE']) ?> x <?= e($p['TIME2_SERIE']) ?></td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" data-abrir-modal="modal-registrar-<?= (int) $p['CON_ID'] ?>">
                                Registrar resultado
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($pendentes as $p): ?>
        <div class="modal-overlay" id="modal-registrar-<?= (int) $p['CON_ID'] ?>">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Registrar resultado</h3>
                    <button type="button" class="modal-close" data-fechar-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="game-matchup" style="margin-bottom:18px;">
                        <span class="team"><?= e($p['TIME1_SERIE']) ?></span>
                        <span class="vs">x</span>
                        <span class="team"><?= e($p['TIME2_SERIE']) ?></span>
                    </div>
                    <form method="POST" action="resultados.php">
                        <input type="hidden" name="acao" value="registrar">
                        <input type="hidden" name="con_id" value="<?= (int) $p['CON_ID'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pontuação — <?= e($p['TIME1_SERIE']) ?></label>
                                <input type="number" name="pontuacao_1" min="0" step="1" required>
                            </div>
                            <div class="form-group">
                                <label>Pontuação — <?= e($p['TIME2_SERIE']) ?></label>
                                <input type="number" name="pontuacao_2" min="0" step="1" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Vencedor</label>
                            <select name="fk_tim_vencedor_id" required>
                                <option value="">Selecione...</option>
                                <option value="<?= (int) $p['FK_TIM_1_ID'] ?>"><?= e($p['TIME1_SERIE']) ?></option>
                                <option value="<?= (int) $p['FK_TIM_2_ID'] ?>"><?= e($p['TIME2_SERIE']) ?></option>
                            </select>
                            <div class="form-hint">Este banco não possui suporte a empate: em caso de pontuação igual, selecione manualmente o vencedor.</div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar resultado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="page-header">
    <h1>Resultados registrados</h1>
</div>

<?php if (empty($registrados)): ?>
    <div class="empty-state">Nenhum resultado registrado ainda.</div>
<?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Modalidade</th>
                    <th>Fase</th>
                    <th>Placar</th>
                    <th>Vencedor</th>
                    <th style="width:120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registrados as $r): ?>
                    <tr>
                        <td><?= formatarData($r['CON_DATA']) ?></td>
                        <td><span class="badge badge-primary"><?= e($r['MOD_NOME']) ?></span></td>
                        <td><?= e($r['FAS_NOME']) ?></td>
                        <td><?= e($r['TIME1_SERIE']) ?> <?= (int) $r['RES_PONTUACAO_TIME_1'] ?> x <?= (int) $r['RES_PONTUACAO_TIME_2'] ?> <?= e($r['TIME2_SERIE']) ?></td>
                        <td><span class="badge badge-success"><?= e($r['VENCEDOR_SERIE']) ?></span></td>
                        <td>
                            <button type="button" class="btn btn-outline-dark btn-sm" data-abrir-modal="modal-editar-<?= (int) $r['CON_ID'] ?>">Editar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php foreach ($registrados as $r): ?>
        <div class="modal-overlay" id="modal-editar-<?= (int) $r['CON_ID'] ?>">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>Editar resultado</h3>
                    <button type="button" class="modal-close" data-fechar-modal>&times;</button>
                </div>
                <div class="modal-body">
                    <div class="game-matchup" style="margin-bottom:18px;">
                        <span class="team"><?= e($r['TIME1_SERIE']) ?></span>
                        <span class="vs">x</span>
                        <span class="team"><?= e($r['TIME2_SERIE']) ?></span>
                    </div>
                    <form method="POST" action="resultados.php">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="con_id" value="<?= (int) $r['CON_ID'] ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Pontuação — <?= e($r['TIME1_SERIE']) ?></label>
                                <input type="number" name="pontuacao_1" min="0" step="1" required value="<?= (int) $r['RES_PONTUACAO_TIME_1'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Pontuação — <?= e($r['TIME2_SERIE']) ?></label>
                                <input type="number" name="pontuacao_2" min="0" step="1" required value="<?= (int) $r['RES_PONTUACAO_TIME_2'] ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Vencedor</label>
                            <select name="fk_tim_vencedor_id" required>
                                <option value="<?= (int) $r['FK_TIM_1_ID'] ?>" <?= (int) $r['FK_TIM_VENCEDOR_ID'] === (int) $r['FK_TIM_1_ID'] ? 'selected' : '' ?>><?= e($r['TIME1_SERIE']) ?></option>
                                <option value="<?= (int) $r['FK_TIM_2_ID'] ?>" <?= (int) $r['FK_TIM_VENCEDOR_ID'] === (int) $r['FK_TIM_2_ID'] ? 'selected' : '' ?>><?= e($r['TIME2_SERIE']) ?></option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
