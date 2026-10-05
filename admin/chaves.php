<?php
require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirLogin();

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome = trim($_POST['cha_nome'] ?? '');
        $modId = $_POST['fk_mod_id'] ?? null;
        $id = $_POST['cha_id'] ?? null;

        if ($nome === '' || !inteiroValido($modId)) {
            definirMensagem('danger', 'Informe o nome da chave e selecione uma modalidade válida.');
        } else {
            try {
                if ($acao === 'criar') {
                    $stmt = $pdo->prepare('INSERT INTO CHAVES (CHA_NOME, FK_MOD_ID) VALUES (:nome, :mod)');
                    $stmt->execute(['nome' => $nome, 'mod' => (int) $modId]);
                    definirMensagem('success', 'Chave cadastrada com sucesso.');
                } else {
                    if (!inteiroValido($id)) {
                        definirMensagem('danger', 'Chave inválida.');
                        redirecionar('chaves.php');
                    }
                    $stmt = $pdo->prepare('UPDATE CHAVES SET CHA_NOME = :nome, FK_MOD_ID = :mod WHERE CHA_ID = :id');
                    $stmt->execute(['nome' => $nome, 'mod' => (int) $modId, 'id' => (int) $id]);
                    definirMensagem('success', 'Chave atualizada com sucesso.');
                }
            } catch (PDOException $ex) {
                definirMensagem('danger', 'Não foi possível salvar a chave.');
            }
        }
    } elseif ($acao === 'excluir') {
        $id = $_POST['cha_id'] ?? null;
        if (inteiroValido($id)) {
            try {
                $stmt = $pdo->prepare('DELETE FROM CHAVES WHERE CHA_ID = :id');
                $stmt->execute(['id' => (int) $id]);
                definirMensagem('success', 'Chave excluída com sucesso.');
            } catch (PDOException $ex) {
                if (eErroDeIntegridade($ex)) {
                    definirMensagem('danger', 'Não é possível excluir esta chave porque existem confrontos vinculados a ela.');
                } else {
                    definirMensagem('danger', 'Não foi possível excluir a chave.');
                }
            }
        }
    }

    redirecionar('chaves.php');
}

$modalidades = $pdo->query('SELECT MOD_ID, MOD_NOME FROM MODALIDADES ORDER BY MOD_NOME ASC')->fetchAll();

$chaves = $pdo->query(
    'SELECT cha.CHA_ID, cha.CHA_NOME, cha.FK_MOD_ID, m.MOD_NOME
     FROM CHAVES cha
     JOIN MODALIDADES m ON m.MOD_ID = cha.FK_MOD_ID
     ORDER BY m.MOD_NOME ASC, cha.CHA_NOME ASC'
)->fetchAll();

$paginaAtual = 'chaves';
$tituloPagina = 'Chaves';
include __DIR__ . '/../includes/header_admin.php';
?>

<div class="page-header">
    <h1>Chaves</h1>
    <?php if (!empty($modalidades)): ?>
        <button type="button" class="btn btn-primary" data-abrir-modal="modal-nova-chave">+ Nova chave</button>
    <?php endif; ?>
</div>

<?php if (empty($modalidades)): ?>
    <div class="alert alert-info">Cadastre pelo menos uma modalidade antes de criar chaves.</div>
<?php endif; ?>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome da chave</th>
                <th>Modalidade</th>
                <th style="width:160px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($chaves)): ?>
                <tr><td colspan="4" class="empty-state">Nenhuma chave cadastrada.</td></tr>
            <?php else: ?>
                <?php foreach ($chaves as $c): ?>
                    <tr>
                        <td>#<?= (int) $c['CHA_ID'] ?></td>
                        <td><?= e($c['CHA_NOME']) ?></td>
                        <td><span class="badge badge-primary"><?= e($c['MOD_NOME']) ?></span></td>
                        <td class="table-actions">
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                onclick='abrirModalEdicao("modal-editar-chave", <?= json_encode(['cha_id' => $c['CHA_ID'], 'cha_nome' => $c['CHA_NOME'], 'fk_mod_id' => $c['FK_MOD_ID']]) ?>)'>
                                Editar
                            </button>
                            <form method="POST" action="chaves.php" data-confirmar-exclusao="Excluir a chave &quot;<?= e($c['CHA_NOME']) ?>&quot;?">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="cha_id" value="<?= (int) $c['CHA_ID'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-nova-chave">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Nova chave</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="chaves.php">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label for="cha_nome_novo">Nome da chave</label>
                    <input type="text" id="cha_nome_novo" name="cha_nome" required maxlength="100" placeholder="Ex: Chave A - Futebol">
                </div>
                <div class="form-group">
                    <label for="fk_mod_id_novo">Modalidade</label>
                    <select id="fk_mod_id_novo" name="fk_mod_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= (int) $m['MOD_ID'] ?>"><?= e($m['MOD_NOME']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-editar-chave">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Editar chave</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="chaves.php">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="cha_id">
                <div class="form-group">
                    <label for="cha_nome_editar">Nome da chave</label>
                    <input type="text" id="cha_nome_editar" name="cha_nome" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="fk_mod_id_editar">Modalidade</label>
                    <select id="fk_mod_id_editar" name="fk_mod_id" required>
                        <?php foreach ($modalidades as $m): ?>
                            <option value="<?= (int) $m['MOD_ID'] ?>"><?= e($m['MOD_NOME']) ?></option>
                        <?php endforeach; ?>
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

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>
