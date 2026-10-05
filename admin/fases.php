<?php
require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirLogin();

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {
        $nome = trim($_POST['fas_nome'] ?? '');
        $ordem = $_POST['fas_ordem'] ?? '';
        $id = $_POST['fas_id'] ?? null;

        if ($nome === '' || !inteiroValido($ordem) || (int) $ordem < 1) {
            definirMensagem('danger', 'Informe um nome válido e uma ordem numérica (maior que zero) para a fase.');
        } else {
            try {
                if ($acao === 'criar') {
                    $stmt = $pdo->prepare('INSERT INTO FASES (FAS_NOME, FAS_ORDEM) VALUES (:nome, :ordem)');
                    $stmt->execute(['nome' => $nome, 'ordem' => (int) $ordem]);
                    definirMensagem('success', 'Fase cadastrada com sucesso.');
                } else {
                    if (!inteiroValido($id)) {
                        definirMensagem('danger', 'Fase inválida.');
                        redirecionar('fases.php');
                    }
                    $stmt = $pdo->prepare('UPDATE FASES SET FAS_NOME = :nome, FAS_ORDEM = :ordem WHERE FAS_ID = :id');
                    $stmt->execute(['nome' => $nome, 'ordem' => (int) $ordem, 'id' => (int) $id]);
                    definirMensagem('success', 'Fase atualizada com sucesso.');
                }
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    definirMensagem('danger', 'Já existe uma fase cadastrada com esse nome.');
                } else {
                    definirMensagem('danger', 'Não foi possível salvar a fase.');
                }
            }
        }
    }

    redirecionar('fases.php');
}

$fases = $pdo->query('SELECT FAS_ID, FAS_NOME, FAS_ORDEM FROM FASES ORDER BY FAS_ORDEM ASC')->fetchAll();

$paginaAtual = 'fases';
$tituloPagina = 'Fases';
include __DIR__ . '/../includes/header_admin.php';
?>

<div class="page-header">
    <h1>Fases</h1>
    <button type="button" class="btn btn-primary" data-abrir-modal="modal-nova-fase">+ Nova fase</button>
</div>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Ordem</th>
                <th>Nome da fase</th>
                <th style="width:120px;">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($fases)): ?>
                <tr><td colspan="3" class="empty-state">Nenhuma fase cadastrada.</td></tr>
            <?php else: ?>
                <?php foreach ($fases as $f): ?>
                    <tr>
                        <td><span class="badge badge-muted"><?= (int) $f['FAS_ORDEM'] ?></span></td>
                        <td><?= e($f['FAS_NOME']) ?></td>
                        <td class="table-actions">
                            <button type="button" class="btn btn-outline-dark btn-sm"
                                onclick='abrirModalEdicao("modal-editar-fase", <?= json_encode(['fas_id' => $f['FAS_ID'], 'fas_nome' => $f['FAS_NOME'], 'fas_ordem' => $f['FAS_ORDEM']]) ?>)'>
                                Editar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal-overlay" id="modal-nova-fase">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Nova fase</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="fases.php">
                <input type="hidden" name="acao" value="criar">
                <div class="form-group">
                    <label for="fas_nome_novo">Nome da fase</label>
                    <input type="text" id="fas_nome_novo" name="fas_nome" required maxlength="50" placeholder="Ex: Oitavas de final">
                </div>
                <div class="form-group">
                    <label for="fas_ordem_novo">Ordem</label>
                    <input type="number" id="fas_ordem_novo" name="fas_ordem" required min="1" step="1" placeholder="Ex: 2">
                    <div class="form-hint">Define a sequência de exibição das fases (1 = primeira).</div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline-dark" data-fechar-modal>Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modal-editar-fase">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Editar fase</h3>
            <button type="button" class="modal-close" data-fechar-modal>&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="fases.php">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="fas_id">
                <div class="form-group">
                    <label for="fas_nome_editar">Nome da fase</label>
                    <input type="text" id="fas_nome_editar" name="fas_nome" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="fas_ordem_editar">Ordem</label>
                    <input type="number" id="fas_ordem_editar" name="fas_ordem" required min="1" step="1">
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
