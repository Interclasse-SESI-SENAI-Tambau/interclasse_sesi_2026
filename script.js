/**
 * Interclasse SESI 2026 - Lógica da Aplicação e Acessibilidade
 */

// Estado da Acessibilidade
let tamanhoFonte = 100;
const MIN_FONTE = 80;
const MAX_FONTE = 140;

// Inicialização de preferências salvas
document.addEventListener('DOMContentLoaded', () => {
  carregarPreferencias();
});

/**
 * Ajusta o tamanho global da fonte em percentual
 * @param {number} delta - Variação (+1 ou -1)
 */
function alterarFonte(delta) {
  tamanhoFonte += delta * 10;
  if (tamanhoFonte < MIN_FONTE) tamanhoFonte = MIN_FONTE;
  if (tamanhoFonte > MAX_FONTE) tamanhoFonte = MAX_FONTE;
  
  document.documentElement.style.fontSize = tamanhoFonte + '%';
  localStorage.setItem('sesi_font_size', tamanhoFonte);
}

/**
 * Restaura o tamanho original da fonte
 */
function resetarFonte() {
  tamanhoFonte = 100;
  document.documentElement.style.fontSize = '100%';
  localStorage.setItem('sesi_font_size', 100);
}

/**
 * Alterna o modo de Alto Contraste (WCAG AAA)
 */
function alternarContraste() {
  const body = document.body;
  body.classList.remove('dark-mode');
  const ativo = body.classList.toggle('high-contrast');
  
  localStorage.setItem('sesi_high_contrast', ativo ? 'true' : 'false');
  localStorage.setItem('sesi_dark_mode', 'false');
}

/**
 * Alterna o Modo Escuro
 */
function alternarModoEscuro() {
  const body = document.body;
  body.classList.remove('high-contrast');
  const ativo = body.classList.toggle('dark-mode');
  
  localStorage.setItem('sesi_dark_mode', ativo ? 'true' : 'false');
  localStorage.setItem('sesi_high_contrast', 'false');
}

/**
 * Carrega as preferências salvas no localStorage
 */
function carregarPreferencias() {
  const fonteSalva = localStorage.getItem('sesi_font_size');
  if (fonteSalva) {
    tamanhoFonte = parseInt(fonteSalva, 10);
    document.documentElement.style.fontSize = tamanhoFonte + '%';
  }

  if (localStorage.getItem('sesi_high_contrast') === 'true') {
    document.body.classList.add('high-contrast');
  } else if (localStorage.getItem('sesi_dark_mode') === 'true') {
    document.body.classList.add('dark-mode');
  }
}

/**
 * Alterna a visibilidade do menu responsivo mobile
 */
function abrirMenu() {
  const nav = document.getElementById('nav-menu');
  const btn = document.getElementById('btn-menu');
  const ativo = nav.classList.toggle('active');
  
  btn.setAttribute('aria-expanded', ativo ? 'true' : 'false');
}

// Fechar menu mobile com tecla ESC
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const nav = document.getElementById('nav-menu');
    const btn = document.getElementById('btn-menu');
    if (nav && nav.classList.contains('active')) {
      nav.classList.remove('active');
      btn.setAttribute('aria-expanded', 'false');
      btn.focus();
    }
  }
});