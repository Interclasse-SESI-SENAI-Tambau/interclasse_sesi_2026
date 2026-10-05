<?php

require_once __DIR__ . '/../autenticacao.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigirLogin();

$pdo = conectar();


// ==========================================================
// PROCESSAMENTO DAS AÇÕES
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';


    // ======================================================
    // CRIAR MODALIDADE
    // ======================================================

    if ($acao === 'criar') {

        $nome = trim($_POST['mod_nome'] ?? '');

        if ($nome === '') {

            definirMensagem(
                'danger',
                'O nome da modalidade é obrigatório.'
            );
        } else {

            try {

                $stmt = $pdo->prepare(
                    'INSERT INTO MODALIDADES (MOD_NOME)
                     VALUES (:nome)'
                );

                $stmt->execute([
                    'nome' => $nome
                ]);

                definirMensagem(
                    'success',
                    'Modalidade cadastrada com sucesso!'
                );
            } catch (PDOException $ex) {

                if ($ex->getCode() === '23000') {

                    definirMensagem(
                        'danger',
                        'Já existe uma modalidade cadastrada com esse nome.'
                    );
                } else {

                    definirMensagem(
                        'danger',
                        'Não foi possível cadastrar a modalidade.'
                    );
                }
            }
        }

        redirecionar('modalidades.php');
    }


    // ======================================================
    // EDITAR MODALIDADE
    // ======================================================

    if ($acao === 'editar') {

        $nome = trim($_POST['mod_nome'] ?? '');
        $id = $_POST['mod_id'] ?? null;


        if ($nome === '') {

            definirMensagem(
                'danger',
                'O nome da modalidade é obrigatório.'
            );

            redirecionar('modalidades.php');
        }


        if (!inteiroValido($id)) {

            definirMensagem(
                'danger',
                'Modalidade inválida.'
            );

            redirecionar('modalidades.php');
        }


        try {

            $stmt = $pdo->prepare(
                'UPDATE MODALIDADES
                 SET MOD_NOME = :nome
                 WHERE MOD_ID = :id'
            );

            $stmt->execute([
                'nome' => $nome,
                'id' => (int) $id
            ]);


            if ($stmt->rowCount() > 0) {

                definirMensagem(
                    'success',
                    'Modalidade atualizada com sucesso!'
                );
            } else {

                definirMensagem(
                    'success',
                    'Modalidade salva com sucesso!'
                );
            }
        } catch (PDOException $ex) {

            if ($ex->getCode() === '23000') {

                definirMensagem(
                    'danger',
                    'Já existe uma modalidade cadastrada com esse nome.'
                );
            } else {

                definirMensagem(
                    'danger',
                    'Não foi possível atualizar a modalidade.'
                );
            }
        }

        redirecionar('modalidades.php');
    }


    // ======================================================
    // EXCLUIR MODALIDADE
    // ======================================================

    if ($acao === 'excluir') {

        $id = $_POST['mod_id'] ?? null;


        if (!inteiroValido($id)) {

            definirMensagem(
                'danger',
                'Modalidade inválida.'
            );

            redirecionar('modalidades.php');
        }


        try {

            $stmt = $pdo->prepare(
                'DELETE FROM MODALIDADES
                 WHERE MOD_ID = :id'
            );

            $stmt->execute([
                'id' => (int) $id
            ]);


            definirMensagem(
                'success',
                'Modalidade excluída com sucesso!'
            );
        } catch (PDOException $ex) {

            if (eErroDeIntegridade($ex)) {

                definirMensagem(
                    'danger',
                    'Não é possível excluir esta modalidade porque existem registros vinculados a ela.'
                );
            } else {

                definirMensagem(
                    'danger',
                    'Não foi possível excluir a modalidade.'
                );
            }
        }

        redirecionar('modalidades.php');
    }
}


// ==========================================================
// BUSCAR MODALIDADES
// ==========================================================

$modalidades = $pdo->query(
    'SELECT MOD_ID, MOD_NOME
     FROM MODALIDADES
     ORDER BY MOD_NOME ASC'
)->fetchAll();


$paginaAtual = 'modalidades';
$tituloPagina = 'Modalidades';


// ==========================================================
// MENU / HEADER
// ==========================================================

include __DIR__ . '/../includes/header_admin.php';

?>


<!-- ==========================================================
     SWEETALERT2
     ========================================================== -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>


<style>
    /* ==========================================================
   CABEÇALHO DA PÁGINA
   ========================================================== */

    .modalidades-page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 20px;

        margin-bottom: 22px;
    }

    .modalidades-title-area {
        flex: 1;
    }

    .modalidades-kicker {
        display: block;

        margin-bottom: 4px;

        color: #0a66c2;

        font-size: 12px;
        font-weight: 800;

        text-transform: uppercase;
        letter-spacing: .8px;
    }

    .modalidades-title-area h1 {
        margin: 0 0 5px;

        color: #0f2a44;

        font-size: 27px;
        font-weight: 800;
    }

    .modalidades-description {
        margin: 0;

        color: #64748b;

        font-size: 14px;
    }


    /* ==========================================================
   BOTÃO NOVA MODALIDADE
   ========================================================== */

    .modalidades-new-button {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        gap: 6px;

        padding: 10px 16px;

        border: none;
        border-radius: 9px;

        background: #0a66c2;

        color: #ffffff;

        font-size: 13px;
        font-weight: 700;

        cursor: pointer;

        transition: .2s ease;
    }

    .modalidades-new-button:hover {
        background: #084f96;

        transform: translateY(-1px);
    }


    /* ==========================================================
   RESUMO
   ========================================================== */

    .modalidades-summary {
        display: flex;

        align-items: center;

        gap: 13px;

        width: fit-content;

        min-width: 210px;

        margin-bottom: 18px;

        padding: 13px 17px;

        background: #ffffff;

        border: 1px solid #e2e8f0;

        border-radius: 12px;

        box-shadow:
            0 3px 12px rgba(15, 42, 68, .06);
    }

    .modalidades-summary-icon {
        width: 38px;
        height: 38px;

        display: flex;

        align-items: center;
        justify-content: center;

        border-radius: 9px;

        background: #eaf3fc;

        color: #0a66c2;

        font-weight: 800;
    }

    .modalidades-summary-number {
        display: block;

        color: #0f2a44;

        font-size: 19px;
        font-weight: 800;
    }

    .modalidades-summary-label {
        display: block;

        color: #64748b;

        font-size: 12px;
    }


    /* ==========================================================
   TABELA
   ========================================================== */

    .modalidades-table {
        overflow: hidden;

        background: #ffffff;

        border: 1px solid #e2e8f0;

        border-radius: 12px;

        box-shadow:
            0 3px 14px rgba(15, 42, 68, .07);
    }

    .modalidades-table table {
        width: 100%;

        border-collapse: collapse;
    }

    .modalidades-table thead th {
        padding: 14px 17px;

        background: #0f2a44;

        color: #ffffff;

        font-size: 12px;
        font-weight: 700;

        text-align: left;

        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .modalidades-table tbody td {
        padding: 15px 17px;

        border-bottom: 1px solid #e2e8f0;

        vertical-align: middle;
    }

    .modalidades-table tbody tr:last-child td {
        border-bottom: none;
    }

    .modalidades-table tbody tr {
        transition: .15s ease;
    }

    .modalidades-table tbody tr:hover {
        background: #f7faff;
    }


    /* ==========================================================
   ID
   ========================================================== */

    .modalidades-id {
        width: 90px;
    }

    .modalidades-id-badge {
        display: inline-flex;

        align-items: center;

        padding: 4px 8px;

        border-radius: 6px;

        background: #eef4fa;

        color: #64748b;

        font-size: 12px;
        font-weight: 700;
    }


    /* ==========================================================
   NOME
   ========================================================== */

    .modalidades-name strong {
        display: block;

        color: #0f2a44;

        font-size: 14px;
        font-weight: 700;
    }

    .modalidades-name span {
        display: block;

        margin-top: 3px;

        color: #64748b;

        font-size: 12px;
    }


    /* ==========================================================
   AÇÕES
   ========================================================== */

    .modalidades-actions {
        width: 190px;
    }

    .modalidades-actions-wrapper {
        display: flex;

        align-items: center;

        gap: 7px;
    }

    .modalidades-actions-wrapper button {
        min-width: 72px;
    }


    /* ==========================================================
   MODAL
   ========================================================== */

    .modalidades-modal {
        position: fixed;

        inset: 0;

        z-index: 9999;

        display: none;

        align-items: center;
        justify-content: center;

        padding: 20px;

        background: rgba(8, 26, 43, .55);
    }

    .modalidades-modal.active {
        display: flex;
    }

    .modalidades-modal-box {
        width: 100%;

        max-width: 500px;

        background: #ffffff;

        border-radius: 14px;

        box-shadow:
            0 20px 60px rgba(8, 26, 43, .25);

        overflow: hidden;
    }

    .modalidades-modal-header {
        display: flex;

        align-items: center;
        justify-content: space-between;

        padding: 20px 24px;

        border-bottom: 1px solid #e2e8f0;

        background: #fbfcfe;
    }

    .modalidades-modal-header h3 {
        margin: 0;

        color: #0f2a44;

        font-size: 19px;
    }

    .modalidades-modal-close {
        width: 34px;
        height: 34px;

        display: flex;

        align-items: center;
        justify-content: center;

        border: none;
        border-radius: 8px;

        background: transparent;

        color: #64748b;

        font-size: 24px;

        cursor: pointer;
    }

    .modalidades-modal-close:hover {
        background: #eaf3fc;

        color: #0a66c2;
    }

    .modalidades-modal-body {
        padding: 24px;
    }

    .modalidades-modal-description {
        margin: 0 0 20px;

        color: #64748b;

        font-size: 13px;

        line-height: 1.6;
    }


    /* ==========================================================
   FORMULÁRIO
   ========================================================== */

    .modalidades-form-group {
        margin-bottom: 20px;
    }

    .modalidades-form-group label {
        display: block;

        margin-bottom: 7px;

        color: #172033;

        font-size: 13px;
        font-weight: 700;
    }

    .modalidades-form-group input {
        width: 100%;

        box-sizing: border-box;

        padding: 11px 13px;

        border: 1px solid #cbd5e1;

        border-radius: 8px;

        background: #ffffff;

        color: #172033;

        font-size: 14px;

        outline: none;

        transition: .2s ease;
    }

    .modalidades-form-group input:focus {
        border-color: #0a66c2;

        box-shadow:
            0 0 0 3px rgba(10, 102, 194, .10);
    }

    .modalidades-form-actions {
        display: flex;

        justify-content: flex-end;

        gap: 9px;

        padding-top: 5px;
    }


    /* ==========================================================
   ESTADO VAZIO
   ========================================================== */

    .modalidades-empty {
        display: flex;

        flex-direction: column;

        align-items: center;
        justify-content: center;

        min-height: 240px;

        padding: 30px;

        text-align: center;
    }

    .modalidades-empty-icon {
        width: 50px;
        height: 50px;

        display: flex;

        align-items: center;
        justify-content: center;

        margin-bottom: 10px;

        border-radius: 13px;

        background: #eaf3fc;

        color: #0a66c2;

        font-weight: 800;
    }

    .modalidades-empty strong {
        color: #0f2a44;

        font-size: 15px;
    }

    .modalidades-empty span {
        margin: 6px 0 14px;

        color: #64748b;

        font-size: 13px;
    }


    /* ==========================================================
   RESPONSIVO
   ========================================================== */

    @media (max-width: 700px) {

        .modalidades-page-header {
            align-items: stretch;

            flex-direction: column;
        }

        .modalidades-new-button {
            width: 100%;
        }

        .modalidades-summary {
            width: 100%;

            box-sizing: border-box;
        }

        .modalidades-table {
            overflow-x: auto;
        }

        .modalidades-actions-wrapper {
            flex-direction: column;

            align-items: stretch;
        }

        .modalidades-actions-wrapper button {
            width: 100%;
        }

        .modalidades-modal-body {
            padding: 20px;
        }

        .modalidades-form-actions {
            flex-direction: column;
        }

        .modalidades-form-actions button {
            width: 100%;
        }
    }

    /* ========================================================== SWEETALERT SEMPRE NA FRENTE DOS MODAIS ========================================================== */
    .swal2-container {
        z-index: 99999 !important;
    }

    .swal2-popup {
        z-index: 100000 !important;
    }
</style>


<!-- ==========================================================
     CABEÇALHO
     ========================================================== -->

<div class="modalidades-page-header">

    <div class="modalidades-title-area">

        <span class="modalidades-kicker">
            Administração
        </span>

        <h1>
            Modalidades
        </h1>

        <p class="modalidades-description">
            Cadastre e gerencie as modalidades do Interclasse SESI.
        </p>

    </div>


    <button
        type="button"
        class="modalidades-new-button"
        id="btnNovaModalidade">
        + Nova modalidade
    </button>

</div>


<!-- ==========================================================
     RESUMO
     ========================================================== -->

<div class="modalidades-summary">

    <div class="modalidades-summary-icon">
        M
    </div>

    <div>

        <span class="modalidades-summary-number">
            <?= count($modalidades) ?>
        </span>

        <span class="modalidades-summary-label">
            modalidade(s) cadastrada(s)
        </span>

    </div>

</div>


<!-- ==========================================================
     TABELA
     ========================================================== -->

<div class="modalidades-table">

    <table>

        <thead>

            <tr>

                <th class="modalidades-id">
                    ID
                </th>

                <th>
                    Modalidade
                </th>

                <th class="modalidades-actions">
                    Ações
                </th>

            </tr>

        </thead>


        <tbody>

            <?php if (empty($modalidades)): ?>

                <tr>

                    <td colspan="3">

                        <div class="modalidades-empty">

                            <div class="modalidades-empty-icon">
                                M
                            </div>

                            <strong>
                                Nenhuma modalidade cadastrada
                            </strong>

                            <span>
                                Cadastre a primeira modalidade para começar.
                            </span>

                            <button
                                type="button"
                                class="modalidades-new-button"
                                id="btnCadastrarPrimeira">
                                + Cadastrar modalidade
                            </button>

                        </div>

                    </td>

                </tr>

            <?php else: ?>

                <?php foreach ($modalidades as $m): ?>

                    <tr>

                        <td>

                            <span class="modalidades-id-badge">
                                #<?= (int) $m['MOD_ID'] ?>
                            </span>

                        </td>


                        <td>

                            <div class="modalidades-name">

                                <strong>
                                    <?= e($m['MOD_NOME']) ?>
                                </strong>

                                <span>
                                    Modalidade esportiva
                                </span>

                            </div>

                        </td>


                        <td>

                            <div class="modalidades-actions-wrapper">

                                <button
                                    type="button"
                                    class="btn btn-outline-dark btn-sm btn-editar-modalidade"
                                    data-id="<?= (int) $m['MOD_ID'] ?>"
                                    data-nome="<?= e($m['MOD_NOME']) ?>">
                                    Editar
                                </button>


                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm btn-excluir-modalidade"
                                    data-id="<?= (int) $m['MOD_ID'] ?>"
                                    data-nome="<?= e($m['MOD_NOME']) ?>">
                                    Excluir
                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

</div>


<!-- ==========================================================
     MODAL CRIAR
     ========================================================== -->

<div
    class="modalidades-modal"
    id="modal-nova-modalidade">

    <div class="modalidades-modal-box">

        <div class="modalidades-modal-header">

            <h3>
                Nova modalidade
            </h3>

            <button
                type="button"
                class="modalidades-modal-close"
                data-fechar-modal>
                &times;
            </button>

        </div>


        <div class="modalidades-modal-body">

            <p class="modalidades-modal-description">
                Informe o nome da modalidade que será utilizada
                no Interclasse SESI.
            </p>


            <form
                method="POST"
                action="modalidades.php"
                id="formNovaModalidade">

                <input
                    type="hidden"
                    name="acao"
                    value="criar">


                <div class="modalidades-form-group">

                    <label for="mod_nome_novo">
                        Nome da modalidade
                    </label>

                    <input
                        type="text"
                        id="mod_nome_novo"
                        name="mod_nome"
                        maxlength="100"
                        required
                        placeholder="Ex.: Futebol"
                        autocomplete="off">

                </div>


                <div class="modalidades-form-actions">

                    <button
                        type="button"
                        class="btn btn-outline-dark"
                        data-fechar-modal>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Salvar modalidade
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ==========================================================
     MODAL EDITAR
     ========================================================== -->

<div
    class="modalidades-modal"
    id="modal-editar-modalidade">

    <div class="modalidades-modal-box">

        <div class="modalidades-modal-header">

            <h3>
                Editar modalidade
            </h3>

            <button
                type="button"
                class="modalidades-modal-close"
                data-fechar-modal>
                &times;
            </button>

        </div>


        <div class="modalidades-modal-body">

            <p class="modalidades-modal-description">
                Altere o nome da modalidade e confirme a alteração.
            </p>


            <form
                method="POST"
                action="modalidades.php"
                id="formEditarModalidade">

                <input
                    type="hidden"
                    name="acao"
                    value="editar">

                <input
                    type="hidden"
                    name="mod_id"
                    id="editar_mod_id">


                <div class="modalidades-form-group">

                    <label for="mod_nome_editar">
                        Nome da modalidade
                    </label>

                    <input
                        type="text"
                        id="mod_nome_editar"
                        name="mod_nome"
                        maxlength="100"
                        required
                        autocomplete="off">

                </div>


                <div class="modalidades-form-actions">

                    <button
                        type="button"
                        class="btn btn-outline-dark"
                        data-fechar-modal>
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Salvar alterações
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- ==========================================================
     JAVASCRIPT
     ========================================================== -->

<script>
    document.addEventListener('DOMContentLoaded', function() {


        // ======================================================
        // VERIFICAR SWEETALERT
        // ======================================================

        if (typeof Swal === 'undefined') {

            console.error(
                'SweetAlert2 não foi carregado.'
            );

            return;
        }


        // ======================================================
        // ABRIR MODAL
        // ======================================================

        function abrirModal(id) {

            const modal =
                document.getElementById(id);


            if (modal) {

                modal.classList.add('active');

            }
        }


        // ======================================================
        // FECHAR MODAL
        // ======================================================

        function fecharModal(modal) {

            if (modal) {

                modal.classList.remove('active');

            }
        }


        // ======================================================
        // ENVIO REAL DO FORMULÁRIO
        // ======================================================

        function enviarFormulario(formulario) {

            HTMLFormElement.prototype.submit.call(
                formulario
            );
        }


        // ======================================================
        // NOVA MODALIDADE
        // ======================================================

        const btnNova =
            document.getElementById(
                'btnNovaModalidade'
            );


        if (btnNova) {

            btnNova.addEventListener(
                'click',
                function() {

                    abrirModal(
                        'modal-nova-modalidade'
                    );

                }
            );

        }


        // ======================================================
        // PRIMEIRA MODALIDADE
        // ======================================================

        const btnPrimeira =
            document.getElementById(
                'btnCadastrarPrimeira'
            );


        if (btnPrimeira) {

            btnPrimeira.addEventListener(
                'click',
                function() {

                    abrirModal(
                        'modal-nova-modalidade'
                    );

                }
            );

        }


        // ======================================================
        // FECHAR MODAIS
        // ======================================================

        const botoesFechar =
            document.querySelectorAll(
                '[data-fechar-modal]'
            );


        botoesFechar.forEach(
            function(botao) {

                botao.addEventListener(
                    'click',
                    function() {

                        const modal =
                            botao.closest(
                                '.modalidades-modal'
                            );


                        fecharModal(modal);

                    }
                );

            }
        );


        // ======================================================
        // FECHAR AO CLICAR FORA
        // ======================================================

        const modais =
            document.querySelectorAll(
                '.modalidades-modal'
            );


        modais.forEach(
            function(modal) {

                modal.addEventListener(
                    'click',
                    function(event) {

                        if (
                            event.target === modal
                        ) {

                            fecharModal(modal);

                        }

                    }
                );

            }
        );


        // ======================================================
        // EDITAR
        // ======================================================

        const botoesEditar =
            document.querySelectorAll(
                '.btn-editar-modalidade'
            );


        botoesEditar.forEach(
            function(botao) {

                botao.addEventListener(
                    'click',
                    function() {

                        const id =
                            botao.getAttribute(
                                'data-id'
                            );


                        const nome =
                            botao.getAttribute(
                                'data-nome'
                            );


                        const campoId =
                            document.getElementById(
                                'editar_mod_id'
                            );


                        const campoNome =
                            document.getElementById(
                                'mod_nome_editar'
                            );


                        if (
                            !campoId ||
                            !campoNome
                        ) {

                            return;

                        }


                        campoId.value = id;

                        campoNome.value = nome;


                        abrirModal(
                            'modal-editar-modalidade'
                        );

                    }
                );

            }
        );


        // ======================================================
        // CRIAR
        // ======================================================

        const formNova =
            document.getElementById(
                'formNovaModalidade'
            );


        if (formNova) {

            formNova.addEventListener(
                'submit',
                function(event) {

                    event.preventDefault();


                    const campoNome =
                        document.getElementById(
                            'mod_nome_novo'
                        );


                    const nome =
                        campoNome.value.trim();


                    if (nome === '') {

                        Swal.fire({

                            title: 'Nome obrigatório',

                            text: 'Digite o nome da modalidade.',

                            icon: 'warning',

                            confirmButtonText: 'Entendi',

                            confirmButtonColor: '#0a66c2'

                        });

                        campoNome.focus();

                        return;
                    }


                    Swal.fire({

                        title: 'Cadastrar modalidade?',

                        html: 'A modalidade <strong>' +
                            nome +
                            '</strong> será cadastrada.',

                        icon: 'question',

                        showCancelButton: true,

                        confirmButtonText: 'Sim, cadastrar',

                        cancelButtonText: 'Cancelar',

                        confirmButtonColor: '#0a66c2',

                        cancelButtonColor: '#64748b',

                        reverseButtons: true,

                        focusCancel: true

                    }).then(
                        function(resultado) {

                            if (
                                resultado.isConfirmed
                            ) {

                                enviarFormulario(
                                    formNova
                                );

                            }

                        }
                    );

                }
            );

        }


        // ======================================================
        // EDITAR
        // ======================================================

        const formEditar =
            document.getElementById(
                'formEditarModalidade'
            );


        if (formEditar) {

            formEditar.addEventListener(
                'submit',
                function(event) {

                    event.preventDefault();


                    const campoNome =
                        document.getElementById(
                            'mod_nome_editar'
                        );


                    const nome =
                        campoNome.value.trim();


                    if (nome === '') {

                        Swal.fire({

                            title: 'Nome obrigatório',

                            text: 'Digite o nome da modalidade.',

                            icon: 'warning',

                            confirmButtonText: 'Entendi',

                            confirmButtonColor: '#0a66c2'

                        });

                        campoNome.focus();

                        return;
                    }


                    Swal.fire({

                        title: 'Salvar alterações?',

                        html: 'A modalidade será alterada para:<br>' +
                            '<strong>' +
                            nome +
                            '</strong>',

                        icon: 'question',

                        showCancelButton: true,

                        confirmButtonText: 'Sim, salvar',

                        cancelButtonText: 'Cancelar',

                        confirmButtonColor: '#0a66c2',

                        cancelButtonColor: '#64748b',

                        reverseButtons: true,

                        focusCancel: true

                    }).then(
                        function(resultado) {

                            if (
                                resultado.isConfirmed
                            ) {

                                enviarFormulario(
                                    formEditar
                                );

                            }

                        }
                    );

                }
            );

        }


        // ======================================================
        // EXCLUIR
        // ======================================================

        const botoesExcluir =
            document.querySelectorAll(
                '.btn-excluir-modalidade'
            );


        botoesExcluir.forEach(
            function(botao) {

                botao.addEventListener(
                    'click',
                    function() {

                        const id =
                            botao.getAttribute(
                                'data-id'
                            );


                        const nome =
                            botao.getAttribute(
                                'data-nome'
                            );


                        Swal.fire({

                            title: 'Excluir modalidade?',

                            html: 'Você está prestes a excluir:<br>' +
                                '<strong>' +
                                nome +
                                '</strong>',

                            icon: 'warning',

                            showCancelButton: true,

                            confirmButtonText: 'Sim, excluir',

                            cancelButtonText: 'Cancelar',

                            confirmButtonColor: '#0a66c2',

                            cancelButtonColor: '#64748b',

                            reverseButtons: true,

                            focusCancel: true

                        }).then(
                            function(resultado) {

                                if (
                                    !resultado.isConfirmed
                                ) {

                                    return;

                                }


                                const formulario =
                                    document.createElement(
                                        'form'
                                    );


                                formulario.method =
                                    'POST';


                                formulario.action =
                                    'modalidades.php';


                                const campoAcao =
                                    document.createElement(
                                        'input'
                                    );


                                campoAcao.type =
                                    'hidden';

                                campoAcao.name =
                                    'acao';

                                campoAcao.value =
                                    'excluir';


                                const campoId =
                                    document.createElement(
                                        'input'
                                    );


                                campoId.type =
                                    'hidden';

                                campoId.name =
                                    'mod_id';

                                campoId.value =
                                    id;


                                formulario.appendChild(
                                    campoAcao
                                );


                                formulario.appendChild(
                                    campoId
                                );


                                document.body.appendChild(
                                    formulario
                                );


                                enviarFormulario(
                                    formulario
                                );

                            }
                        );

                    }
                );

            }
        );


        // ======================================================
        // MENSAGENS DO PHP
        // ======================================================

        <?php

        $mensagem = obterMensagem();

        ?>


        <?php if (
            $mensagem &&
            ($mensagem['tipo'] ?? '') === 'success'
        ): ?>

            Swal.fire({

                title: 'Sucesso!',

                text: <?= json_encode($mensagem['texto']) ?>,

                icon: 'success',

                confirmButtonText: 'Continuar',

                confirmButtonColor: '#0a66c2',

                timer: 2500,

                timerProgressBar: true

            });

        <?php endif; ?>


        <?php if (
            $mensagem &&
            ($mensagem['tipo'] ?? '') === 'danger'
        ): ?>

            Swal.fire({

                title: 'Não foi possível concluir',

                text: <?= json_encode($mensagem['texto']) ?>,

                icon: 'error',

                confirmButtonText: 'Entendi',

                confirmButtonColor: '#0a66c2'

            });

        <?php endif; ?>


    });
</script>


<?php

include __DIR__ . '/../includes/footer_admin.php';

?>