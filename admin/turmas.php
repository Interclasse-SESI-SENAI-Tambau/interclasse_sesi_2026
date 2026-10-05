
<?php

require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/chaveamento.php';

exigirLogin();

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';

    if ($acao === 'criar' || $acao === 'editar') {

        $serie = trim($_POST['tur_serie'] ?? '');
        $id = $_POST['tur_id'] ?? null;

        if ($serie === '') {

            definirMensagem('danger', 'A série/turma é obrigatória.');

        } else {

            try {

                if ($acao === 'criar') {

                    $stmt = $pdo->prepare(
                        'INSERT INTO TURMAS (TUR_SERIE) VALUES (:serie)'
                    );

                    $stmt->execute([
                        'serie' => $serie
                    ]);

                    definirMensagem(
                        'success',
                        'Turma cadastrada com sucesso.'
                    );

                } else {

                    if (!inteiroValido($id)) {

                        definirMensagem(
                            'danger',
                            'Turma inválida.'
                        );

                        redirecionar('turmas.php');
                    }

                    $stmt = $pdo->prepare(
                        'UPDATE TURMAS
                         SET TUR_SERIE = :serie
                         WHERE TUR_ID = :id'
                    );

                    $stmt->execute([
                        'serie' => $serie,
                        'id' => (int) $id
                    ]);

                    definirMensagem(
                        'success',
                        'Turma atualizada com sucesso.'
                    );
                }

            } catch (PDOException $ex) {

                if ($ex->getCode() === '23000') {

                    definirMensagem(
                        'danger',
                        'Já existe uma turma cadastrada com esse nome.'
                    );

                } else {

                    definirMensagem(
                        'danger',
                        'Não foi possível salvar a turma.'
                    );
                }
            }
        }

    } elseif ($acao === 'excluir') {

        $id = $_POST['tur_id'] ?? null;

        if (inteiroValido($id)) {

            try {

                $pdo->beginTransaction();

                excluirTurma($pdo, (int) $id);

                $pdo->commit();

                definirMensagem(
                    'success',
                    'Turma excluída com sucesso.'
                );

            } catch (PDOException $ex) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                definirMensagem(
                    'danger',
                    'Não foi possível excluir a turma.'
                );
            }
        }
    }

    redirecionar('turmas.php');
}

/*
|--------------------------------------------------------------------------
| Busca as turmas
|--------------------------------------------------------------------------
| Ordenado pelo ID para manter a ordem de cadastro.
*/
$turmas = $pdo->query(
    'SELECT TUR_ID, TUR_SERIE
     FROM TURMAS
     ORDER BY TUR_ID ASC'
)->fetchAll();

$paginaAtual = 'turmas';
$tituloPagina = 'Turmas';

include __DIR__ . '/../includes/header_admin.php';

?>

<style>

/* ==========================================================
   PÁGINA DE TURMAS
   ========================================================== */

.turmas-page {
    width: 100%;
}

.turmas-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.turmas-title-area h1 {
    margin: 0;
    color: #0f2a44;
    font-size: 30px;
    font-weight: 800;
}

.turmas-kicker {
    display: block;
    color: #0a66c2;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.turmas-description {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
}

.turmas-new-button {
    border: none;
    background: #0a66c2;
    color: #ffffff;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
    box-shadow: 0 5px 14px rgba(10, 102, 194, 0.18);
}

.turmas-new-button:hover {
    background: #084f96;
    transform: translateY(-1px);
}

/* ==========================================================
   RESUMO
   ========================================================== */

.turmas-summary {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #0a66c2;
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15, 42, 68, 0.05);
}

.turmas-summary-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eaf3fc;
    color: #0a66c2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
}

.turmas-summary strong {
    display: block;
    color: #0f2a44;
    font-size: 20px;
    line-height: 1;
}

.turmas-summary span {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 13px;
}

/* ==========================================================
   TABELA
   ========================================================== */

.turmas-table-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15, 42, 68, 0.06);
}

.turmas-table {
    width: 100%;
    border-collapse: collapse;
}

.turmas-table thead {
    background: #0f2a44;
}

.turmas-table th {
    color: #ffffff;
    text-align: left;
    padding: 15px 18px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.turmas-table td {
    padding: 15px 18px;
    border-bottom: 1px solid #e2e8f0;
    color: #172033;
    font-size: 14px;
}

.turmas-table tbody tr {
    transition: 0.15s;
}

.turmas-table tbody tr:hover {
    background: #f6faff;
}

.turmas-table tbody tr:last-child td {
    border-bottom: none;
}

.turmas-id {
    color: #64748b;
    font-weight: 700;
}

.turmas-name {
    color: #0f2a44;
    font-weight: 700;
}

.turmas-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.turmas-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.turmas-btn-edit {
    background: #eaf3fc;
    color: #0a66c2;
}

.turmas-btn-edit:hover {
    background: #d9eafb;
}

.turmas-btn-delete {
    background: #fee2e2;
    color: #b91c1c;
}

.turmas-btn-delete:hover {
    background: #fecaca;
}

.turmas-empty {
    text-align: center !important;
    padding: 45px 20px !important;
    color: #64748b !important;
}

/* ==========================================================
   MODAIS
   ========================================================== */

.turmas-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(8, 26, 43, 0.65);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
    backdrop-filter: blur(3px);
}

.turmas-modal-overlay.active {
    display: flex;
}

.turmas-modal-box {
    width: 100%;
    max-width: 480px;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(8, 26, 43, 0.25);
    animation: turmasModalEntrada 0.18s ease-out;
}

@keyframes turmasModalEntrada {

    from {
        opacity: 0;
        transform: translateY(12px) scale(0.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.turmas-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0f2a44;
    color: #ffffff;
    padding: 18px 20px;
}

.turmas-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.turmas-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-size: 23px;
    cursor: pointer;
}

.turmas-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.turmas-modal-body {
    padding: 22px;
}

/* ==========================================================
   FORMULÁRIO
   ========================================================== */

.turmas-form-group {
    margin-bottom: 20px;
}

.turmas-form-group label {
    display: block;
    margin-bottom: 7px;
    color: #0f2a44;
    font-size: 13px;
    font-weight: 700;
}

.turmas-form-group input {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 12px 13px;
    color: #172033;
    background: #ffffff;
    outline: none;
    font-size: 14px;
    transition: 0.2s;
}

.turmas-form-group input:focus {
    border-color: #0a66c2;
    box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.12);
}

.turmas-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.turmas-form-button {
    border: none;
    border-radius: 9px;
    padding: 11px 16px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.turmas-form-cancel {
    background: #f1f5f9;
    color: #334155;
}

.turmas-form-cancel:hover {
    background: #e2e8f0;
}

.turmas-form-save {
    background: #0a66c2;
    color: #ffffff;
}

.turmas-form-save:hover {
    background: #084f96;
}

/* ==========================================================
   SWEETALERT SEMPRE NA FRENTE DOS MODAIS
   ========================================================== */

.swal2-container {
    z-index: 99999 !important;
}

.swal2-popup {
    z-index: 100000 !important;
}

/* ==========================================================
   RESPONSIVO
   ========================================================== */

@media (max-width: 700px) {

    .turmas-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .turmas-new-button {
        width: 100%;
    }

    .turmas-table-wrapper {
        overflow-x: auto;
    }

    .turmas-table {
        min-width: 600px;
    }

    .turmas-actions {
        flex-wrap: wrap;
    }
}

</style>

<div class="turmas-page">

    <div class="turmas-page-header">

        <div class="turmas-title-area">

            <span class="turmas-kicker">
                Administração
            </span>

            <h1>
                Turmas
            </h1>

            <p class="turmas-description">
                Cadastre e gerencie as turmas do Interclasse SESI.
            </p>

        </div>

        <button
            type="button"
            class="turmas-new-button"
            id="btnNovaTurma">

            + Nova turma

        </button>

    </div>

    <div class="turmas-summary">

        <div class="turmas-summary-icon">
            #
        </div>

        <div>

            <strong>
                <?= count($turmas) ?>
            </strong>

            <span>
                <?= count($turmas) === 1 ? 'turma cadastrada' : 'turmas cadastradas' ?>
            </span>

        </div>

    </div>

    <div class="turmas-table-wrapper">

        <table class="turmas-table">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Série / Turma
                    </th>

                    <th style="width: 160px;">
                        Ações
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php if (empty($turmas)): ?>

                    <tr>

                        <td
                            colspan="3"
                            class="turmas-empty">

                            Nenhuma turma cadastrada.

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($turmas as $t): ?>

                        <tr>

                            <td class="turmas-id">

                                #<?= (int) $t['TUR_ID'] ?>

                            </td>

                            <td class="turmas-name">

                                <?= e($t['TUR_SERIE']) ?>

                            </td>

                            <td>

                                <div class="turmas-actions">

                                    <button
                                        type="button"
                                        class="turmas-btn turmas-btn-edit btn-editar-turma"
                                        data-id="<?= (int) $t['TUR_ID'] ?>"
                                        data-serie="<?= e($t['TUR_SERIE']) ?>">

                                        Editar

                                    </button>

                                    <form
                                        method="POST"
                                        action="turmas.php"
                                        class="form-excluir-turma"
                                        data-serie="<?= e($t['TUR_SERIE']) ?>">

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir">

                                        <input
                                            type="hidden"
                                            name="tur_id"
                                            value="<?= (int) $t['TUR_ID'] ?>">

                                        <button
                                            type="submit"
                                            class="turmas-btn turmas-btn-delete">

                                            Excluir

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ==========================================================
     MODAL NOVA TURMA
     ========================================================== -->

<div
    class="turmas-modal-overlay"
    id="modal-nova-turma">

    <div class="turmas-modal-box">

        <div class="turmas-modal-header">

            <h3>
                Nova turma
            </h3>

            <button
                type="button"
                class="turmas-modal-close"
                data-fechar-turma>

                &times;

            </button>

        </div>

        <div class="turmas-modal-body">

            <form
                method="POST"
                action="turmas.php"
                id="formNovaTurma">

                <input
                    type="hidden"
                    name="acao"
                    value="criar">

                <div class="turmas-form-group">

                    <label for="tur_serie_novo">
                        Série / Turma
                    </label>

                    <input
                        type="text"
                        id="tur_serie_novo"
                        name="tur_serie"
                        required
                        maxlength="30"
                        placeholder="Ex: 1º EM-A">

                </div>

                <div class="turmas-form-actions">

                    <button
                        type="button"
                        class="turmas-form-button turmas-form-cancel"
                        data-fechar-turma>

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="turmas-form-button turmas-form-save">

                        Salvar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- ==========================================================
     MODAL EDITAR TURMA
     ========================================================== -->

<div
    class="turmas-modal-overlay"
    id="modal-editar-turma">

    <div class="turmas-modal-box">

        <div class="turmas-modal-header">

            <h3>
                Editar turma
            </h3>

            <button
                type="button"
                class="turmas-modal-close"
                data-fechar-turma>

                &times;

            </button>

        </div>

        <div class="turmas-modal-body">

            <form
                method="POST"
                action="turmas.php"
                id="formEditarTurma">

                <input
                    type="hidden"
                    name="acao"
                    value="editar">

                <input
                    type="hidden"
                    name="tur_id"
                    id="tur_id_editar">

                <div class="turmas-form-group">

                    <label for="tur_serie_editar">
                        Série / Turma
                    </label>

                    <input
                        type="text"
                        id="tur_serie_editar"
                        name="tur_serie"
                        required
                        maxlength="30">

                </div>

                <div class="turmas-form-actions">

                    <button
                        type="button"
                        class="turmas-form-button turmas-form-cancel"
                        data-fechar-turma>

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="turmas-form-button turmas-form-save">

                        Salvar alterações

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const modalNova = document.getElementById('modal-nova-turma');
    const modalEditar = document.getElementById('modal-editar-turma');

    const btnNova = document.getElementById('btnNovaTurma');

    const formNova = document.getElementById('formNovaTurma');
    const formEditar = document.getElementById('formEditarTurma');

    const campoIdEditar = document.getElementById('tur_id_editar');
    const campoSerieEditar = document.getElementById('tur_serie_editar');

    /*
    |--------------------------------------------------------------------------
    | Abrir modal de criação
    |--------------------------------------------------------------------------
    */

    btnNova.addEventListener('click', function () {

        modalNova.classList.add('active');

        document.getElementById('tur_serie_novo').focus();

    });

    /*
    |--------------------------------------------------------------------------
    | Fechar modais
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('[data-fechar-turma]').forEach(function (botao) {

        botao.addEventListener('click', function () {

            modalNova.classList.remove('active');
            modalEditar.classList.remove('active');

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Fechar clicando fora do modal
    |--------------------------------------------------------------------------
    */

    [modalNova, modalEditar].forEach(function (modal) {

        modal.addEventListener('click', function (event) {

            if (event.target === modal) {

                modal.classList.remove('active');

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Fechar com ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            modalNova.classList.remove('active');
            modalEditar.classList.remove('active');

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Abrir edição
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.btn-editar-turma').forEach(function (botao) {

        botao.addEventListener('click', function () {

            const id = this.getAttribute('data-id');
            const serie = this.getAttribute('data-serie');

            campoIdEditar.value = id;
            campoSerieEditar.value = serie;

            modalEditar.classList.add('active');

            campoSerieEditar.focus();

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Função para enviar formulário sem conflito com outros eventos
    |--------------------------------------------------------------------------
    */

    function enviarFormulario(formulario) {

        HTMLFormElement.prototype.submit.call(formulario);

    }

    /*
    |--------------------------------------------------------------------------
    | Criar turma
    |--------------------------------------------------------------------------
    */

    formNova.addEventListener('submit', function (event) {

        event.preventDefault();

        const serie = document.getElementById('tur_serie_novo').value.trim();

        if (serie === '') {

            Swal.fire({
                icon: 'warning',
                title: 'Campo obrigatório',
                text: 'Informe a série ou turma.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0a66c2'
            });

            return;

        }

        Swal.fire({
            icon: 'question',
            title: 'Cadastrar turma?',
            text: 'Deseja cadastrar a turma "' + serie + '"?',
            showCancelButton: true,
            confirmButtonText: 'Sim, cadastrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#64748b'
        }).then(function (resultado) {

            if (resultado.isConfirmed) {

                enviarFormulario(formNova);

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Editar turma
    |--------------------------------------------------------------------------
    */

    formEditar.addEventListener('submit', function (event) {

        event.preventDefault();

        const serie = campoSerieEditar.value.trim();

        if (serie === '') {

            Swal.fire({
                icon: 'warning',
                title: 'Campo obrigatório',
                text: 'Informe a série ou turma.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0a66c2'
            });

            return;

        }

        Swal.fire({
            icon: 'question',
            title: 'Salvar alterações?',
            text: 'Deseja alterar a turma para "' + serie + '"?',
            showCancelButton: true,
            confirmButtonText: 'Sim, salvar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0a66c2',
            cancelButtonColor: '#64748b'
        }).then(function (resultado) {

            if (resultado.isConfirmed) {

                enviarFormulario(formEditar);

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Exclusão
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.form-excluir-turma').forEach(function (formulario) {

        formulario.addEventListener('submit', function (event) {

            event.preventDefault();

            const serie = this.getAttribute('data-serie');

            const formularioAtual = this;

            Swal.fire({
                icon: 'warning',
                title: 'Excluir turma?',
                text: 'A turma "' + serie + '" e todos os times, confrontos e resultados vinculados a ela serão excluídos.',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b'
            }).then(function (resultado) {

                if (resultado.isConfirmed) {

                    enviarFormulario(formularioAtual);

                }

            });

        });

    });

});

</script>

<?php

/*
|--------------------------------------------------------------------------
| Mensagens vindas da sessão
|--------------------------------------------------------------------------
*/

$mensagem = obterMensagem();

if ($mensagem):

    $tipo = $mensagem['tipo'] ?? 'info';
    $texto = $mensagem['mensagem'] ?? '';

?>

<script>

document.addEventListener('DOMContentLoaded', function () {

    Swal.fire({

        icon: <?= json_encode($tipo === 'danger' ? 'error' : $tipo) ?>,

        title: <?= json_encode(
            $tipo === 'success'
                ? 'Tudo certo!'
                : 'Atenção'
        ) ?>,

        text: <?= json_encode($texto) ?>,

        confirmButtonText: 'OK',

        confirmButtonColor: '#0a66c2'

    });

});

</script>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer_admin.php'; ?>

