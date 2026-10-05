<?php
require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/chaveamento.php';
exigirLogin();

$pdo = conectar();

/**
 * Valida e retorna dados de um confronto submetido via formulário.
 * Retorna ['erros' => array, 'dados' => array] com os dados já tipados.
 */
function validarConfronto(PDO $pdo, array $post): array
{
    $erros = [];

    $chaId = $post['fk_cha_id'] ?? null;
    $fasId = $post['fk_fas_id'] ?? null;
    $tim1Id = $post['fk_tim_1_id'] ?? null;
    $tim2Id = $post['fk_tim_2_id'] ?? null;
    $data = $post['con_data'] ?? '';
    $hora = $post['con_hora'] ?? '';

    if (!inteiroValido($chaId)) { $erros[] = 'Selecione uma chave válida.'; }
    if (!inteiroValido($fasId)) { $erros[] = 'Selecione uma fase válida.'; }
    if (!inteiroValido($tim1Id)) { $erros[] = 'Selecione o Time 1.'; }
    if (!inteiroValido($tim2Id)) { $erros[] = 'Selecione o Time 2.'; }
    if (!dataValida($data)) { $erros[] = 'Informe uma data válida.'; }
    if (!horaValida($hora)) { $erros[] = 'Informe um horário válido.'; }

    if (!empty($erros)) {
        return ['erros' => $erros, 'dados' => null];
    }

    if ((int) $tim1Id === (int) $tim2Id) {
        $erros[] = 'O Time 1 e o Time 2 devem ser diferentes.';
        return ['erros' => $erros, 'dados' => null];
    }

    $stmtChave = $pdo->prepare('SELECT FK_MOD_ID FROM CHAVES WHERE CHA_ID = :id');
    $stmtChave->execute(['id' => (int) $chaId]);
    $chave = $stmtChave->fetch();

    $stmtFase = $pdo->prepare('SELECT FAS_ID FROM FASES WHERE FAS_ID = :id');
    $stmtFase->execute(['id' => (int) $fasId]);
    $fase = $stmtFase->fetch();

    $stmtTimes = $pdo->prepare('SELECT TIM_ID, FK_MOD_ID FROM TIMES WHERE TIM_ID IN (:t1, :t2)');
    $stmtTimes->execute(['t1' => (int) $tim1Id, 't2' => (int) $tim2Id]);
    $timesEncontrados = $stmtTimes->fetchAll();

    if (!$chave) { $erros[] = 'A chave selecionada não existe.'; }
    if (!$fase) { $erros[] = 'A fase selecionada não existe.'; }
    if (count($timesEncontrados) < 2) { $erros[] = 'Um dos times selecionados não existe.'; }

    if (empty($erros)) {
        foreach ($timesEncontrados as $t) {
            if ((int) $t['FK_MOD_ID'] !== (int) $chave['FK_MOD_ID']) {
                $erros[] = 'Os times selecionados devem pertencer à mesma modalidade da chave escolhida.';
                break;
            }
        }
    }

    if (!empty($erros)) {
        return ['erros' => $erros, 'dados' => null];
    }

    return [
        'erros' => [],
        'dados' => [
            'fk_cha_id' => (int) $chaId,
            'fk_fas_id' => (int) $fasId,
            'fk_tim_1_id' => (int) $tim1Id,
            'fk_tim_2_id' => (int) $tim2Id,
            'con_data' => $data,
            'con_hora' => $hora,
        ],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $resultado = validarConfronto($pdo, $_POST);

        if (!empty($resultado['erros'])) {
            definirMensagem('danger', implode(' ', $resultado['erros']));
        } else {
            $d = $resultado['dados'];
            try {
                if ($acao === 'criar') {
                    $stmt = $pdo->prepare(
                        'INSERT INTO CONFRONTOS (FK_CHA_ID, FK_FAS_ID, FK_TIM_1_ID, FK_TIM_2_ID, CON_DATA, CON_HORA)
                         VALUES (:cha, :fas, :t1, :t2, :data, :hora)'
                    );
                    $stmt->execute([
                        'cha' => $d['fk_cha_id'], 'fas' => $d['fk_fas_id'],
                        't1' => $d['fk_tim_1_id'], 't2' => $d['fk_tim_2_id'],
                        'data' => $d['con_data'], 'hora' => $d['con_hora'],
                    ]);
                    definirMensagem('success', 'Confronto cadastrado com sucesso.');
                } else {
                    $id = $_POST['con_id'] ?? null;
                    if (!inteiroValido($id)) {
                        definirMensagem('danger', 'Confronto inválido.');
                        redirecionar('confrontos.php');
                    }
                    $stmt = $pdo->prepare(
                        'UPDATE CONFRONTOS SET FK_CHA_ID = :cha, FK_FAS_ID = :fas,
                         FK_TIM_1_ID = :t1, FK_TIM_2_ID = :t2, CON_DATA = :data, CON_HORA = :hora
                         WHERE CON_ID = :id'
                    );
                    $stmt->execute([
                        'cha' => $d['fk_cha_id'], 'fas' => $d['fk_fas_id'],
                        't1' => $d['fk_tim_1_id'], 't2' => $d['fk_tim_2_id'],
                        'data' => $d['con_data'], 'hora' => $d['con_hora'],
                        'id' => (int) $id,
                    ]);
                    definirMensagem('success', 'Confronto atualizado com sucesso.');
                }
            } catch (PDOException $ex) {
                definirMensagem('danger', 'Não foi possível salvar o confronto.');
            }
        }
    } elseif ($acao === 'excluir') {
        $id = $_POST['con_id'] ?? null;
        if (inteiroValido($id)) {
            try {
                $pdo->beginTransaction();
                excluirConfronto($pdo, (int) $id);
                $pdo->commit();
                definirMensagem('success', 'Confronto excluído com sucesso.');
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                definirMensagem('danger', 'Não foi possível excluir o confronto.');
            }
        }
    }

    redirecionar('confrontos.php');
}

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME ASC')->fetchAll();
$fases = $pdo->query('SELECT FAS_ID, FAS_NOME FROM FASES ORDER BY FAS_ORDEM ASC')->fetchAll();
$chaves = $pdo->query('SELECT CHA_ID, CHA_NOME, FK_MOD_ID FROM CHAVES ORDER BY CHA_NOME ASC')->fetchAll();
$times = $pdo->query(
    'SELECT t.TIM_ID, t.FK_MOD_ID, tur.TUR_SERIE
     FROM TIMES t JOIN TURMAS tur ON tur.TUR_ID = t.FK_TUR_ID
     ORDER BY tur.TUR_SERIE ASC'
)->fetchAll();

$podeCriar = !empty($chaves) && !empty($fases) && count($times) >= 2;

$confrontos = $pdo->query(
    'SELECT c.CON_ID, c.FK_CHA_ID, c.FK_FAS_ID, c.FK_TIM_1_ID, c.FK_TIM_2_ID, c.CON_DATA, c.CON_HORA,
            m.MOD_NOME, f.FAS_NOME, cha.CHA_NOME,
            tur1.TUR_SERIE AS TIME1_SERIE, tur2.TUR_SERIE AS TIME2_SERIE,
            r.RES_ID
     FROM CONFRONTOS c
     JOIN CHAVES cha ON cha.CHA_ID = c.FK_CHA_ID
     JOIN MODALIDADES m ON m.MOD_ID = cha.FK_MOD_ID
     JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
     LEFT JOIN TIMES t1 ON t1.TIM_ID = c.FK_TIM_1_ID
     LEFT JOIN TIMES t2 ON t2.TIM_ID = c.FK_TIM_2_ID
     LEFT JOIN TURMAS tur1 ON tur1.TUR_ID = t1.FK_TUR_ID
     LEFT JOIN TURMAS tur2 ON tur2.TUR_ID = t2.FK_TUR_ID
     LEFT JOIN RESULTADOS r ON r.FK_CON_ID = c.CON_ID
     ORDER BY c.CON_DATA DESC, c.CON_HORA DESC'
)->fetchAll();

// Vencedores já definidos, agrupados por chave, para auxiliar a progressão de fases.
$vencedoresPorChave = [];
$stmtVencedores = $pdo->query(
    'SELECT c.FK_CHA_ID, c.CON_ID, f.FAS_NOME, tv.TIM_ID AS VENCEDOR_ID, turV.TUR_SERIE AS VENCEDOR_SERIE
     FROM RESULTADOS r
     JOIN CONFRONTOS c ON c.CON_ID = r.FK_CON_ID
     JOIN FASES f ON f.FAS_ID = c.FK_FAS_ID
     JOIN TIMES tv ON tv.TIM_ID = r.FK_TIM_VENCEDOR_ID
     JOIN TURMAS turV ON turV.TUR_ID = tv.FK_TUR_ID'
);
foreach ($stmtVencedores->fetchAll() as $v) {
    $vencedoresPorChave[$v['FK_CHA_ID']][] = $v;
}

$paginaAtual = 'confrontos';
$tituloPagina = 'Confrontos';
include __DIR__ . '/../includes/header_admin.php';
?>

<div class="page-header">
    <h1>Confrontos</h1>
    <?php if ($podeCriar): ?>
        <button type="button" class="btn btn-primary" data-abrir-modal="modal-novo-confronto">+ Novo confronto</button>
    <?php endif; ?>
</div>

<?php if (!$podeCriar): ?>
    <div class="alert alert-info">Cadastre modalidades, chaves, fases e pelo menos dois times antes de criar confrontos.</div>
<?php endif; ?>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Horário</th>
                <th>Modalidade</th>
                <th>Fase</th>
                <th>Chave</th>
                <th>Time 1</th>
                <th>Time 2</th>
                <th>Status</th>
                <th style="width:160px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($confrontos)): ?>
                <tr><td colspan="9" class="empty-state">Nenhum confronto cadastrado.</td></tr>
            <?php else: ?>
                <?php foreach ($confrontos as $c): ?>
                    <?php
                    $finalizado = $c['RES_ID'] !== null;
                    $pendenteDeTime = $c['FK_TIM_1_ID'] === null || $c['FK_TIM_2_ID'] === null;
                    ?>
                    <tr>
                        <td><?= formatarData($c['CON_DATA']) ?></td>
                        <td><?= formatarHora($c['CON_HORA']) ?></td>
                        <td><span class="badge badge-primary"><?= e($c['MOD_NOME']) ?></span></td>
                        <td><?= e($c['FAS_NOME']) ?></td>
                        <td><?= e($c['CHA_NOME']) ?></td>
                        <td><?= $c['TIME1_SERIE'] !== null ? e($c['TIME1_SERIE']) : '—' ?></td>
                        <td><?= $c['TIME2_SERIE'] !== null ? e($c['TIME2_SERIE']) : '—' ?></td>
                        <td>
                            <?php if ($finalizado): ?>
                                <span class="badge badge-success">Finalizado</span>
                            <?php elseif ($pendenteDeTime): ?>
                                <span class="badge badge-muted">Aguardando definição</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Agendado</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                onclick='abrirModalEdicao("modal-editar-confronto", <?= json_encode([
                                    'con_id' => $c['CON_ID'],
                                    'fk_cha_id' => $c['FK_CHA_ID'],
                                    'fk_fas_id' => $c['FK_FAS_ID'],
                                    'fk_tim_1_id' => $c['FK_TIM_1_ID'],
                                    'fk_tim_2_id' => $c['FK_TIM_2_ID'],
                                    'con_data' => $c['CON_DATA'],
                                    'con_hora' => $c['CON_HORA'] !== null ? substr($c['CON_HORA'], 0, 5) : '',
                                ]) ?>)'>
                                Editar
                            </button>
                            <form method="POST" action="confrontos.php" data-confirmar-exclusao="<?= $finalizado ? 'Este confronto já tem resultado registrado. Excluí-lo também desfará qualquer avanço de fase já gerado a partir dele. Continuar?' : 'Excluir este confronto?' ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="con_id" value="<?= (int) $c['CON_ID'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
/**
 * Gera os <option> de times/chaves com atributo data-modalidade,
 * usado pelo JS para filtrar conforme a modalidade escolhida.
 */
function renderOptionsChaves(array $chaves, $selecionado = null): void
{
    foreach ($chaves as $c) {
        $sel = $selecionado !== null && (int) $selecionado === (int) $c['CHA_ID'] ? 'selected' : '';
        echo '<option value="' . (int) $c['CHA_ID'] . '" data-modalidade="' . (int) $c['FK_MOD_ID'] . '" ' . $sel . '>' . e($c['CHA_NOME']) . '</option>';
    }
}
function renderOptionsTimes(array $times, $selecionado = null): void
{
    foreach ($times as $t) {
        $sel = $selecionado !== null && (int) $selecionado === (int) $t['TIM_ID'] ? 'selected' : '';
        echo '<option value="' . (int) $t['TIM_ID'] . '" data-modalidade="' . (int) $t['FK_MOD_ID'] . '" ' . $sel . '>' . e($t['TUR_SERIE']) . '</option>';
    }
}
?>

<!-- Modal: novo confronto -->
<div class="modal-overlay" id="modal-novo-confronto">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Novo confronto</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="confrontos.php" id="form-novo-confronto">
                <input type="hidden" name="acao" value="criar">

                <div class="form-group">
                    <label for="modalidade_novo">Modalidade</label>
                    <select id="modalidade_novo">
                        <option value="">Selecione...</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= (int) $m['MOD_ID'] ?>"><?= e($m['MOD_NOME']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-hint">Filtra a chave e os times disponíveis abaixo.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fk_cha_id_novo">Chave</label>
                        <select id="fk_cha_id_novo" name="fk_cha_id" required>
                            <option value="">Selecione...</option>
                            <?php renderOptionsChaves($chaves); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fk_fas_id_novo">Fase</label>
                        <select id="fk_fas_id_novo" name="fk_fas_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($fases as $f): ?>
                                <option value="<?= (int) $f['FAS_ID'] ?>"><?= e($f['FAS_NOME']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fk_tim_1_id_novo">Time 1</label>
                        <select id="fk_tim_1_id_novo" name="fk_tim_1_id" required>
                            <option value="">Selecione...</option>
                            <?php renderOptionsTimes($times); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fk_tim_2_id_novo">Time 2</label>
                        <select id="fk_tim_2_id_novo" name="fk_tim_2_id" required>
                            <option value="">Selecione...</option>
                            <?php renderOptionsTimes($times); ?>
                        </select>
                    </div>
                </div>

                <div id="painel-vencedores-novo" class="form-hint hidden" style="margin-bottom:14px;"></div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="con_data_novo">Data</label>
                        <input type="date" id="con_data_novo" name="con_data" required>
                    </div>
                    <div class="form-group">
                        <label for="con_hora_novo">Horário</label>
                        <input type="time" id="con_hora_novo" name="con_hora" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: editar confronto -->
<div class="modal-overlay" id="modal-editar-confronto">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Editar confronto</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="confrontos.php" id="form-editar-confronto">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="con_id">

                <div class="form-row">
                    <div class="form-group">
                        <label for="fk_cha_id_editar">Chave</label>
                        <select id="fk_cha_id_editar" name="fk_cha_id" required>
                            <?php renderOptionsChaves($chaves); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fk_fas_id_editar">Fase</label>
                        <select id="fk_fas_id_editar" name="fk_fas_id" required>
                            <?php foreach ($fases as $f): ?>
                                <option value="<?= (int) $f['FAS_ID'] ?>"><?= e($f['FAS_NOME']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fk_tim_1_id_editar">Time 1</label>
                        <select id="fk_tim_1_id_editar" name="fk_tim_1_id" required>
                            <?php renderOptionsTimes($times); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fk_tim_2_id_editar">Time 2</label>
                        <select id="fk_tim_2_id_editar" name="fk_tim_2_id" required>
                            <?php renderOptionsTimes($times); ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="con_data_editar">Data</label>
                        <input type="date" id="con_data_editar" name="con_data" required>
                    </div>
                    <div class="form-group">
                        <label for="con_hora_editar">Horário</label>
                        <input type="time" id="con_hora_editar" name="con_hora" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>

<script>
    var vencedoresPorChave = <?= json_encode($vencedoresPorChave, JSON_UNESCAPED_UNICODE) ?>;

    filtrarChavesPorModalidade('modalidade_novo', 'fk_cha_id_novo');
    filtrarTimesPorChave('fk_cha_id_novo', ['fk_tim_1_id_novo', 'fk_tim_2_id_novo']);
    filtrarTimesPorChave('fk_cha_id_editar', ['fk_tim_1_id_editar', 'fk_tim_2_id_editar']);
    validarTimesDiferentes('form-novo-confronto', 'fk_tim_1_id_novo', 'fk_tim_2_id_novo');
    validarTimesDiferentes('form-editar-confronto', 'fk_tim_1_id_editar', 'fk_tim_2_id_editar');

    var selectChaveNovo = document.getElementById('fk_cha_id_novo');
    var painelVencedores = document.getElementById('painel-vencedores-novo');
    if (selectChaveNovo && painelVencedores) {
        selectChaveNovo.addEventListener('change', function () {
            var lista = vencedoresPorChave[selectChaveNovo.value] || [];
            if (lista.length === 0) {
                painelVencedores.classList.add('hidden');
                painelVencedores.innerHTML = '';
                return;
            }
            var html = '<strong>Vencedores já definidos nesta chave (use para montar a próxima fase):</strong><ul style="margin-top:6px;">';
            lista.forEach(function (v) {
                html += '<li>Jogo #' + v.CON_ID + ' (' + v.FAS_NOME + '): <strong>' + v.VENCEDOR_SERIE + '</strong> — selecione manualmente nos campos Time 1/Time 2 acima.</li>';
            });
            html += '</ul>';
            painelVencedores.innerHTML = html;
            painelVencedores.classList.remove('hidden');
        });
    }
</script>
