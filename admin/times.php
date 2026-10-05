
<?php

require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/chaveamento.php';

exigirLogin();

$pdo = conectar();


/*
|--------------------------------------------------------------------------
| AÇÕES
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | CRIAR / EDITAR TIME
    |--------------------------------------------------------------------------
    */

    if ($acao === 'criar' || $acao === 'editar') {

        $turId = $_POST['fk_tur_id'] ?? null;
        $modId = $_POST['fk_mod_id'] ?? null;
        $id = $_POST['tim_id'] ?? null;


        if (!inteiroValido($turId) || !inteiroValido($modId)) {

            definirMensagem(
                'danger',
                'Selecione uma turma e uma modalidade válidas.'
            );

        } else {

            try {

                if ($acao === 'criar') {

                    $stmt = $pdo->prepare(
                        'INSERT INTO TIMES (FK_TUR_ID, FK_MOD_ID)
                         VALUES (:tur, :mod)'
                    );

                    $stmt->execute([
                        'tur' => (int) $turId,
                        'mod' => (int) $modId
                    ]);

                    definirMensagem(
                        'success',
                        'Time cadastrado com sucesso.'
                    );

                } else {

                    if (!inteiroValido($id)) {

                        definirMensagem(
                            'danger',
                            'Time inválido.'
                        );

                        redirecionar('times.php');
                    }


                    $stmt = $pdo->prepare(
                        'UPDATE TIMES
                         SET FK_TUR_ID = :tur,
                             FK_MOD_ID = :mod
                         WHERE TIM_ID = :id'
                    );

                    $stmt->execute([
                        'tur' => (int) $turId,
                        'mod' => (int) $modId,
                        'id' => (int) $id
                    ]);

                    definirMensagem(
                        'success',
                        'Time atualizado com sucesso.'
                    );
                }

            } catch (PDOException $ex) {

                if ($ex->getCode() === '23000') {

                    definirMensagem(
                        'danger',
                        'Esta turma já possui um time cadastrado nesta modalidade.'
                    );

                } else {

                    definirMensagem(
                        'danger',
                        'Não foi possível salvar o time.'
                    );
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EXCLUIR TIME
    |--------------------------------------------------------------------------
    */

    elseif ($acao === 'excluir') {

        $id = $_POST['tim_id'] ?? null;

        if (inteiroValido($id)) {

            try {

                $pdo->beginTransaction();

                excluirTime($pdo, (int) $id);

                $pdo->commit();

                definirMensagem(
                    'success',
                    'Time excluído com sucesso.'
                );

            } catch (PDOException $ex) {

                if ($pdo->inTransaction()) {

                    $pdo->rollBack();
                }

                definirMensagem(
                    'danger',
                    'Não foi possível excluir o time.'
                );
            }
        }
    }


    redirecionar('times.php');
}


/*
|--------------------------------------------------------------------------
| BUSCAR TURMAS
|--------------------------------------------------------------------------
*/

$turmas = $pdo->query(
    'SELECT TUR_ID, TUR_SERIE
     FROM TURMAS
     ORDER BY TUR_ID ASC'
)->fetchAll();


/*
|--------------------------------------------------------------------------
| BUSCAR MODALIDADES
|--------------------------------------------------------------------------
*/

$modalidades = $pdo->query(
    'SELECT MOD_ID, MOD_NOME
     FROM MODALIDADES
     ORDER BY MOD_ID ASC'
)->fetchAll();


/*
|--------------------------------------------------------------------------
| BUSCAR TIMES
|--------------------------------------------------------------------------
*/

$times = $pdo->query(

    'SELECT
        t.TIM_ID,
        t.FK_TUR_ID,
        t.FK_MOD_ID,
        tur.TUR_SERIE,
        m.MOD_NOME

     FROM TIMES t

     JOIN TURMAS tur
        ON tur.TUR_ID = t.FK_TUR_ID

     JOIN MODALIDADES m
        ON m.MOD_ID = t.FK_MOD_ID

     ORDER BY t.TIM_ID ASC'

)->fetchAll();


$podeCriar = !empty($turmas) && !empty($modalidades);

$paginaAtual = 'times';
$tituloPagina = 'Times';

include __DIR__ . '/../includes/header_admin.php';

?>

<style>

/* ==========================================================
   PÁGINA DE TIMES
   ========================================================== */

.times-page {
    width: 100%;
}


/* ==========================================================
   CABEÇALHO
   ========================================================== */

.times-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.times-title-area h1 {
    margin: 0;
    color: #0f2a44;
    font-size: 30px;
    font-weight: 800;
}

.times-kicker {
    display: block;
    color: #0a66c2;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.times-description {
    margin: 7px 0 0;
    color: #64748b;
    font-size: 14px;
}


/* ==========================================================
   BOTÃO NOVO
   ========================================================== */

.times-new-button {
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

.times-new-button:hover {
    background: #084f96;
    transform: translateY(-1px);
}


/* ==========================================================
   ALERTA
   ========================================================== */

.times-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #eaf3fc;
    border: 1px solid #bfdbfe;
    border-left: 4px solid #0a66c2;
    color: #0f2a44;
    padding: 15px 17px;
    border-radius: 11px;
    margin-bottom: 20px;
    font-size: 13px;
    font-weight: 600;
}

.times-alert-icon {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 8px;
    background: #ffffff;
    color: #0a66c2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}


/* ==========================================================
   RESUMO
   ========================================================== */

.times-summary {
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

.times-summary-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eaf3fc;
    color: #0a66c2;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 800;
}

.times-summary strong {
    display: block;
    color: #0f2a44;
    font-size: 20px;
    line-height: 1;
}

.times-summary span {
    display: block;
    margin-top: 5px;
    color: #64748b;
    font-size: 13px;
}


/* ==========================================================
   TABELA
   ========================================================== */

.times-table-wrapper {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(15, 42, 68, 0.06);
}

.times-table {
    width: 100%;
    border-collapse: collapse;
}

.times-table thead {
    background: #0f2a44;
}

.times-table th {
    color: #ffffff;
    text-align: left;
    padding: 15px 18px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.times-table td {
    padding: 15px 18px;
    border-bottom: 1px solid #e2e8f0;
    color: #172033;
    font-size: 14px;
}

.times-table tbody tr {
    transition: 0.15s;
}

.times-table tbody tr:hover {
    background: #f6faff;
}

.times-table tbody tr:last-child td {
    border-bottom: none;
}

.times-id {
    color: #64748b;
    font-weight: 700;
}

.times-class {
    color: #0f2a44;
    font-weight: 700;
}

.times-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 10px;
    border-radius: 7px;
    background: #eaf3fc;
    color: #0a66c2;
    font-size: 11px;
    font-weight: 800;
}

.times-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.times-btn {
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

.times-btn-edit {
    background: #eaf3fc;
    color: #0a66c2;
}

.times-btn-edit:hover {
    background: #d9eafb;
}

.times-btn-delete {
    background: #fee2e2;
    color: #b91c1c;
}

.times-btn-delete:hover {
    background: #fecaca;
}

.times-empty {
    text-align: center !important;
    padding: 45px 20px !important;
    color: #64748b !important;
}


/* ==========================================================
   MODAIS
   ========================================================== */

.times-modal-overlay {
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

.times-modal-overlay.active {
    display: flex;
}

.times-modal-box {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(8, 26, 43, 0.25);
    animation: timesModalEntrada 0.18s ease-out;
}

@keyframes timesModalEntrada {

    from {
        opacity: 0;
        transform: translateY(12px) scale(0.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

.times-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #0f2a44;
    color: #ffffff;
    padding: 18px 20px;
}

.times-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.times-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-size: 23px;
    cursor: pointer;
}

.times-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.times-modal-body {
    padding: 22px;
}


/* ==========================================================
   FORMULÁRIO
   ========================================================== */

.times-form-group {
    margin-bottom: 19px;
}

.times-form-group label {
    display: block;
    margin-bottom: 7px;
    color: #0f2a44;
    font-size: 13px;
    font-weight: 700;
}

.times-form-group select {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    padding: 12px 13px;
    color: #172033;
    background: #ffffff;
    outline: none;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s;
}

.times-form-group select:focus {
    border-color: #0a66c2;
    box-shadow: 0 0 0 3px rgba(10, 102, 194, 0.12);
}

.times-form-hint {
    background: #f6f8fb;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    padding: 10px 12px;
    color: #64748b;
    font-size: 12px;
    margin-bottom: 20px;
}

.times-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.times-form-button {
    border: none;
    border-radius: 9px;
    padding: 11px 16px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.times-form-cancel {
    background: #f1f5f9;
    color: #334155;
}

.times-form-cancel:hover {
    background: #e2e8f0;
}

.times-form-save {
    background: #0a66c2;
    color: #ffffff;
}

.times-form-save:hover {
    background: #084f96;
}


/* ==========================================================
   SWEETALERT
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

@media (max-width: 750px) {

    .times-page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .times-new-button {
        width: 100%;
    }

    .times-table-wrapper {
        overflow-x: auto;
    }

    .times-table {
        min-width: 700px;
    }

}

@media (max-width: 500px) {

    .times-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .times-btn {
        width: 100%;
    }

}

</style>


<div class="times-page">

    <!-- ======================================================
         CABEÇALHO
         ====================================================== -->

    <div class="times-page-header">

        <div class="times-title-area">

            <span class="times-kicker">
                Administração
            </span>

            <h1>
                Times
            </h1>

            <p class="times-description">
                Cadastre e gerencie os times do Interclasse SESI.
            </p>

        </div>


        <?php if ($podeCriar): ?>

            <button
                type="button"
                class="times-new-button"
                id="btnNovoTime">

                + Novo time

            </button>

        <?php endif; ?>

    </div>


    <!-- ======================================================
         AVISO
         ====================================================== -->

    <?php if (!$podeCriar): ?>

        <div class="times-alert">

            <div class="times-alert-icon">
                !
            </div>

            <div>
                Cadastre ao menos uma turma e uma modalidade antes
                de criar times.
            </div>

        </div>

    <?php endif; ?>


    <!-- ======================================================
         RESUMO
         ====================================================== -->

    <div class="times-summary">

        <div class="times-summary-icon">
            #
        </div>

        <div>

            <strong>
                <?= count($times) ?>
            </strong>

            <span>
                <?= count($times) === 1
                    ? 'time cadastrado'
                    : 'times cadastrados'
                ?>
            </span>

        </div>

    </div>


    <!-- ======================================================
         TABELA
         ====================================================== -->

    <div class="times-table-wrapper">

        <table class="times-table">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Turma
                    </th>

                    <th>
                        Modalidade
                    </th>

                    <th style="width: 160px;">
                        Ações
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (empty($times)): ?>

                    <tr>

                        <td
                            colspan="4"
                            class="times-empty">

                            Nenhum time cadastrado.

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($times as $t): ?>

                        <tr>

                            <td class="times-id">

                                #<?= (int) $t['TIM_ID'] ?>

                            </td>


                            <td class="times-class">

                                <?= e($t['TUR_SERIE']) ?>

                            </td>


                            <td>

                                <span class="times-badge">

                                    <?= e($t['MOD_NOME']) ?>

                                </span>

                            </td>


                            <td>

                                <div class="times-actions">

                                    <button
                                        type="button"
                                        class="times-btn times-btn-edit btn-editar-time"
                                        data-id="<?= (int) $t['TIM_ID'] ?>"
                                        data-turma="<?= (int) $t['FK_TUR_ID'] ?>"
                                        data-modalidade="<?= (int) $t['FK_MOD_ID'] ?>">

                                        Editar

                                    </button>


                                    <form
                                        method="POST"
                                        action="times.php"
                                        class="form-excluir-time"
                                        data-time="<?= e($t['TUR_SERIE']) ?>"
                                        data-modalidade="<?= e($t['MOD_NOME']) ?>">

                                        <input
                                            type="hidden"
                                            name="acao"
                                            value="excluir">

                                        <input
                                            type="hidden"
                                            name="tim_id"
                                            value="<?= (int) $t['TIM_ID'] ?>">


                                        <button
                                            type="submit"
                                            class="times-btn times-btn-delete">

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
     MODAL NOVO TIME
     ========================================================== -->

<div
    class="times-modal-overlay"
    id="modal-novo-time">

    <div class="times-modal-box">

        <div class="times-modal-header">

            <h3>
                Novo time
            </h3>

            <button
                type="button"
                class="times-modal-close"
                data-fechar-time>

                &times;

            </button>

        </div>


        <div class="times-modal-body">

            <form
                method="POST"
                action="times.php"
                id="formNovoTime">

                <input
                    type="hidden"
                    name="acao"
                    value="criar">


                <div class="times-form-group">

                    <label for="fk_tur_id_novo">
                        Turma
                    </label>

                    <select
                        id="fk_tur_id_novo"
                        name="fk_tur_id"
                        required>

                        <option value="">
                            Selecione...
                        </option>

                        <?php foreach ($turmas as $t): ?>

                            <option
                                value="<?= (int) $t['TUR_ID'] ?>">

                                <?= e($t['TUR_SERIE']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="times-form-group">

                    <label for="fk_mod_id_novo">
                        Modalidade
                    </label>

                    <select
                        id="fk_mod_id_novo"
                        name="fk_mod_id"
                        required>

                        <option value="">
                            Selecione...
                        </option>

                        <?php foreach ($modalidades as $m): ?>

                            <option
                                value="<?= (int) $m['MOD_ID'] ?>">

                                <?= e($m['MOD_NOME']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="times-form-hint">

                    Uma turma não pode ter dois times na mesma modalidade.

                </div>


                <div class="times-form-actions">

                    <button
                        type="button"
                        class="times-form-button times-form-cancel"
                        data-fechar-time>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="times-form-button times-form-save">

                        Salvar

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ==========================================================
     MODAL EDITAR TIME
     ========================================================== -->

<div
    class="times-modal-overlay"
    id="modal-editar-time">

    <div class="times-modal-box">

        <div class="times-modal-header">

            <h3>
                Editar time
            </h3>

            <button
                type="button"
                class="times-modal-close"
                data-fechar-time>

                &times;

            </button>

        </div>


        <div class="times-modal-body">

            <form
                method="POST"
                action="times.php"
                id="formEditarTime">

                <input
                    type="hidden"
                    name="acao"
                    value="editar">


                <input
                    type="hidden"
                    name="tim_id"
                    id="tim_id_editar">


                <div class="times-form-group">

                    <label for="fk_tur_id_editar">
                        Turma
                    </label>

                    <select
                        id="fk_tur_id_editar"
                        name="fk_tur_id"
                        required>

                        <?php foreach ($turmas as $t): ?>

                            <option
                                value="<?= (int) $t['TUR_ID'] ?>">

                                <?= e($t['TUR_SERIE']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="times-form-group">

                    <label for="fk_mod_id_editar">
                        Modalidade
                    </label>

                    <select
                        id="fk_mod_id_editar"
                        name="fk_mod_id"
                        required>

                        <?php foreach ($modalidades as $m): ?>

                            <option
                                value="<?= (int) $m['MOD_ID'] ?>">

                                <?= e($m['MOD_NOME']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="times-form-actions">

                    <button
                        type="button"
                        class="times-form-button times-form-cancel"
                        data-fechar-time>

                        Cancelar

                    </button>


                    <button
                        type="submit"
                        class="times-form-button times-form-save">

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

    const modalNovo = document.getElementById('modal-novo-time');
    const modalEditar = document.getElementById('modal-editar-time');

    const btnNovo = document.getElementById('btnNovoTime');

    const formNovo = document.getElementById('formNovoTime');
    const formEditar = document.getElementById('formEditarTime');

    const campoIdEditar = document.getElementById('tim_id_editar');
    const campoTurmaEditar = document.getElementById('fk_tur_id_editar');
    const campoModalidadeEditar = document.getElementById('fk_mod_id_editar');


    /*
    |--------------------------------------------------------------------------
    | ABRIR NOVO TIME
    |--------------------------------------------------------------------------
    */

    if (btnNovo) {

        btnNovo.addEventListener('click', function () {

            modalNovo.classList.add('active');

            document.getElementById('fk_tur_id_novo').focus();

        });

    }


    /*
    |--------------------------------------------------------------------------
    | FECHAR MODAIS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('[data-fechar-time]').forEach(function (botao) {

        botao.addEventListener('click', function () {

            modalNovo.classList.remove('active');
            modalEditar.classList.remove('active');

        });

    });


    /*
    |--------------------------------------------------------------------------
    | CLICAR FORA
    |--------------------------------------------------------------------------
    */

    [modalNovo, modalEditar].forEach(function (modal) {

        modal.addEventListener('click', function (event) {

            if (event.target === modal) {

                modal.classList.remove('active');

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            modalNovo.classList.remove('active');
            modalEditar.classList.remove('active');

        }

    });


    /*
    |--------------------------------------------------------------------------
    | EDITAR TIME
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.btn-editar-time').forEach(function (botao) {

        botao.addEventListener('click', function () {

            const id = this.getAttribute('data-id');
            const turma = this.getAttribute('data-turma');
            const modalidade = this.getAttribute('data-modalidade');

            campoIdEditar.value = id;
            campoTurmaEditar.value = turma;
            campoModalidadeEditar.value = modalidade;

            modalEditar.classList.add('active');

            campoTurmaEditar.focus();

        });

    });


    /*
    |--------------------------------------------------------------------------
    | ENVIO NATIVO
    |--------------------------------------------------------------------------
    */

    function enviarFormulario(formulario) {

        HTMLFormElement.prototype.submit.call(formulario);

    }


    /*
    |--------------------------------------------------------------------------
    | CRIAR TIME
    |--------------------------------------------------------------------------
    */

    if (formNovo) {

        formNovo.addEventListener('submit', function (event) {

            event.preventDefault();

            const turma = document.getElementById('fk_tur_id_novo').value;
            const modalidade = document.getElementById('fk_mod_id_novo').value;

            if (turma === '' || modalidade === '') {

                Swal.fire({

                    icon: 'warning',

                    title: 'Dados incompletos',

                    text: 'Selecione uma turma e uma modalidade.',

                    confirmButtonText: 'OK',

                    confirmButtonColor: '#0a66c2'

                });

                return;

            }


            Swal.fire({

                icon: 'question',

                title: 'Cadastrar time?',

                text: 'Deseja cadastrar este time?',

                showCancelButton: true,

                confirmButtonText: 'Sim, cadastrar',

                cancelButtonText: 'Cancelar',

                confirmButtonColor: '#0a66c2',

                cancelButtonColor: '#64748b'

            }).then(function (resultado) {

                if (resultado.isConfirmed) {

                    enviarFormulario(formNovo);

                }

            });

        });

    }


    /*
    |--------------------------------------------------------------------------
    | EDITAR TIME
    |--------------------------------------------------------------------------
    */

    if (formEditar) {

        formEditar.addEventListener('submit', function (event) {

            event.preventDefault();

            const turma = campoTurmaEditar.value;
            const modalidade = campoModalidadeEditar.value;


            if (turma === '' || modalidade === '') {

                Swal.fire({

                    icon: 'warning',

                    title: 'Dados incompletos',

                    text: 'Selecione uma turma e uma modalidade.',

                    confirmButtonText: 'OK',

                    confirmButtonColor: '#0a66c2'

                });

                return;

            }


            Swal.fire({

                icon: 'question',

                title: 'Salvar alterações?',

                text: 'Deseja alterar este time?',

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

    }


    /*
    |--------------------------------------------------------------------------
    | EXCLUIR TIME
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.form-excluir-time').forEach(function (formulario) {

        formulario.addEventListener('submit', function (event) {

            event.preventDefault();

            const turma = this.getAttribute('data-time');
            const modalidade = this.getAttribute('data-modalidade');

            const formularioAtual = this;


            Swal.fire({

                icon: 'warning',

                title: 'Excluir time?',

                text:
                    'O time "' +
                    turma +
                    ' - ' +
                    modalidade +
                    '" será excluído. ' +
                    'Os confrontos e resultados vinculados também poderão ser excluídos.',

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

    }


);

</script>


<?php

/*
|--------------------------------------------------------------------------
| MENSAGEM DA SESSÃO
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

        icon: <?= json_encode(
            $tipo === 'danger'
                ? 'error'
                : $tipo
        ) ?>,

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
