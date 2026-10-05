/**
 * INTERCLASSE SESI - JavaScript puro (sem jQuery/frameworks)
 * Responsável por: menu mobile, modais, confirmação de exclusão,
 * filtros client-side auxiliares e validações visuais de formulário.
 */

document.addEventListener('DOMContentLoaded', function () {
    inicializarMenuMobile();
    inicializarSidebarAdmin();
    inicializarModais();
    inicializarConfirmacaoExclusao();
    inicializarFechamentoAlertas();
});

/* ---------------- Menu mobile (área pública) ---------------- */
function inicializarMenuMobile() {
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.main-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
        nav.classList.toggle('open');
    });
}

/* ---------------- Sidebar admin (mobile) ---------------- */
function inicializarSidebarAdmin() {
    var toggle = document.querySelector('.sidebar-toggle');
    var sidebar = document.querySelector('.admin-sidebar');
    var overlay = document.querySelector('.sidebar-overlay');
    if (!toggle || !sidebar) return;

    function abrir() {
        sidebar.classList.add('open');
        if (overlay) overlay.classList.add('open');
    }
    function fechar() {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('open');
    }

    toggle.addEventListener('click', function () {
        sidebar.classList.contains('open') ? fechar() : abrir();
    });
    if (overlay) overlay.addEventListener('click', fechar);
}

/* ---------------- Modais genéricos ---------------- */
function inicializarModais() {
    document.querySelectorAll('[data-abrir-modal]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var id = botao.getAttribute('data-abrir-modal');
            var modal = document.getElementById(id);
            if (modal) modal.classList.add('open');
        });
    });

    document.querySelectorAll('[data-fechar-modal]').forEach(function (botao) {
        botao.addEventListener('click', function () {
            var overlay = botao.closest('.modal-overlay');
            if (overlay) overlay.classList.remove('open');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (evento) {
            if (evento.target === overlay) overlay.classList.remove('open');
        });
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(function (overlay) {
                overlay.classList.remove('open');
            });
        }
    });
}

function abrirModalEdicao(idModal, dados) {
    var modal = document.getElementById(idModal);
    if (!modal) return;

    Object.keys(dados).forEach(function (campo) {
        var input = modal.querySelector('[name="' + campo + '"]');
        if (!input) return;
        input.value = dados[campo];
        if (input.tagName === 'SELECT') {
            input.dispatchEvent(new Event('change'));
        }
    });

    modal.classList.add('open');
}

/* ---------------- Confirmação de exclusão ---------------- */
function inicializarConfirmacaoExclusao() {
    document.querySelectorAll('form[data-confirmar-exclusao]').forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            evento.preventDefault();
            var mensagem = form.getAttribute('data-confirmar-exclusao') || 'Tem certeza que deseja excluir este registro?';
            abrirModalConfirmacao(mensagem, function () {
                form.submit();
            });
        });
    });
}

function abrirModalConfirmacao(mensagem, aoConfirmar) {
    var modal = document.getElementById('modal-confirmacao-global');
    if (!modal) return;

    modal.querySelector('.modal-confirm-text').textContent = mensagem;
    modal.classList.add('open');

    var btnConfirmar = modal.querySelector('[data-confirmar]');
    var handler = function () {
        modal.classList.remove('open');
        btnConfirmar.removeEventListener('click', handler);
        aoConfirmar();
    };
    btnConfirmar.addEventListener('click', handler);
}

/* ---------------- Fechamento automático de alertas ---------------- */
function inicializarFechamentoAlertas() {
    document.querySelectorAll('.alert').forEach(function (alerta) {
        setTimeout(function () {
            alerta.style.transition = 'opacity .4s ease';
            alerta.style.opacity = '0';
            setTimeout(function () { alerta.remove(); }, 400);
        }, 5000);
    });
}

/* ---------------- Filtro dinâmico: chave por modalidade ----------------
   Usado em admin/confrontos.php: ao trocar a modalidade (campo auxiliar,
   não enviado no formulário), filtra os <option> do select de Chave. */
function filtrarChavesPorModalidade(selectModalidadeId, selectChaveId) {
    var selectModalidade = document.getElementById(selectModalidadeId);
    var selectChave = document.getElementById(selectChaveId);
    if (!selectModalidade || !selectChave) return;

    function aplicarFiltro() {
        var modId = selectModalidade.value;
        var valorAtual = selectChave.value;

        selectChave.querySelectorAll('option').forEach(function (opcao) {
            if (!opcao.value) { opcao.hidden = false; return; }
            opcao.hidden = modId ? opcao.getAttribute('data-modalidade') !== modId : false;
        });

        var opcaoAtual = selectChave.querySelector('option[value="' + valorAtual + '"]');
        if (valorAtual && opcaoAtual && opcaoAtual.hidden) {
            selectChave.value = '';
            selectChave.dispatchEvent(new Event('change'));
        }
    }

    selectModalidade.addEventListener('change', aplicarFiltro);
    aplicarFiltro();
}

/* ---------------- Filtro dinâmico: times pela chave selecionada ----------------
   Usado em admin/confrontos.php (criação e edição): ao trocar a chave,
   filtra os <option> dos selects de Time 1 / Time 2 pela modalidade da
   chave escolhida (atributo data-modalidade). Sem chave selecionada, todos
   os times ficam visíveis. */
function filtrarTimesPorChave(selectChaveId, selectsTimesIds) {
    var selectChave = document.getElementById(selectChaveId);
    if (!selectChave) return;

    function aplicarFiltro() {
        var opcaoChave = selectChave.options[selectChave.selectedIndex];
        var modId = opcaoChave ? opcaoChave.getAttribute('data-modalidade') : null;

        selectsTimesIds.forEach(function (id) {
            var select = document.getElementById(id);
            if (!select) return;
            var valorAtual = select.value;

            select.querySelectorAll('option').forEach(function (opcao) {
                if (!opcao.value) { opcao.hidden = false; return; }
                opcao.hidden = modId ? opcao.getAttribute('data-modalidade') !== modId : false;
            });

            var opcaoAtual = select.querySelector('option[value="' + valorAtual + '"]');
            if (valorAtual && opcaoAtual && opcaoAtual.hidden) {
                select.value = '';
            }
        });
    }

    selectChave.addEventListener('change', aplicarFiltro);
    aplicarFiltro();
}

/* ---------------- Validação visual: time 1 != time 2 ---------------- */
function validarTimesDiferentes(formId, selectTime1Id, selectTime2Id) {
    var form = document.getElementById(formId);
    var select1 = document.getElementById(selectTime1Id);
    var select2 = document.getElementById(selectTime2Id);
    if (!form || !select1 || !select2) return;

    form.addEventListener('submit', function (evento) {
        limparErro(select2);
        if (select1.value && select2.value && select1.value === select2.value) {
            evento.preventDefault();
            exibirErro(select2, 'O Time 2 deve ser diferente do Time 1.');
        }
    });
}

function exibirErro(campo, mensagem) {
    limparErro(campo);
    var erro = document.createElement('div');
    erro.className = 'form-error';
    erro.setAttribute('data-erro-js', '1');
    erro.textContent = mensagem;
    campo.insertAdjacentElement('afterend', erro);
    campo.focus();
}

function limparErro(campo) {
    var proximo = campo.nextElementSibling;
    if (proximo && proximo.hasAttribute('data-erro-js')) {
        proximo.remove();
    }
}

/* ---------------- Filtros públicos (jogos/times) via formulário GET ---------------- */
function inicializarAutoSubmitFiltro(formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', function () { form.submit(); });
    });
}
