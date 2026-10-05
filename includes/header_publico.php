<?php
/**
 * Header reutilizável da área pública.
 * Espera (opcional) a variável $paginaAtual definida antes do include,
 * usada para marcar o item de menu ativo.
 */
$paginaAtual = $paginaAtual ?? '';
?>
<header class="site-header">
    <div class="container">
        <a href="<?= e($raizPublica ?? '') ?>index.php" class="brand">
            <span class="brand-badge">IS</span>
            INTERCLASSE SESI
        </a>

        <nav class="main-nav" id="main-nav">
            <ul>
                <li><a href="<?= e($raizPublica ?? '') ?>index.php" class="<?= $paginaAtual === 'inicio' ? 'active' : '' ?>">Início</a></li>
                <li><a href="<?= e($raizPublica ?? '') ?>publico/jogos.php" class="<?= $paginaAtual === 'jogos' ? 'active' : '' ?>">Jogos</a></li>
                <li><a href="<?= e($raizPublica ?? '') ?>publico/times.php" class="<?= $paginaAtual === 'times' ? 'active' : '' ?>">Times</a></li>
                <li><a href="<?= e($raizPublica ?? '') ?>publico/chaves.php" class="<?= $paginaAtual === 'chaves' ? 'active' : '' ?>">Chaves</a></li>
                <li><a href="<?= e($raizPublica ?? '') ?>publico/resultados.php" class="<?= $paginaAtual === 'resultados' ? 'active' : '' ?>">Resultados</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="<?= e($raizPublica ?? '') ?>login.php" class="btn btn-outline btn-sm">
                <span class="text-desktop">Área Administrativa</span>
                <span class="text-mobile-only" style="display:none;">Admin</span>
            </a>
            <button type="button" class="menu-toggle" aria-label="Abrir menu">&#9776;</button>
        </div>
    </div>
</header>
