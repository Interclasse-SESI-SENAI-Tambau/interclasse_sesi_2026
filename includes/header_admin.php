<?php
/**
 * Layout administrativo: sidebar + topbar.
 * Espera $paginaAtual e $tituloPagina definidos antes do include.
 */
$paginaAtual = $paginaAtual ?? '';
$tituloPagina = $tituloPagina ?? 'Painel Administrativo';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> · INTERCLASSE SESI</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar-overlay" id="sidebar-overlay"></div>
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="brand">
            <span class="brand-badge">IS</span>
            INTERCLASSE SESI
        </div>
        <nav class="admin-nav">
            <a href="index.php" class="<?= $paginaAtual === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="modalidades.php" class="<?= $paginaAtual === 'modalidades' ? 'active' : '' ?>">Modalidades</a>
            <a href="turmas.php" class="<?= $paginaAtual === 'turmas' ? 'active' : '' ?>">Turmas</a>
            <a href="times.php" class="<?= $paginaAtual === 'times' ? 'active' : '' ?>">Times</a>
            <a href="fases.php" class="<?= $paginaAtual === 'fases' ? 'active' : '' ?>">Fases</a>
            <a href="chaves.php" class="<?= $paginaAtual === 'chaves' ? 'active' : '' ?>">Chaves</a>
            <a href="confrontos.php" class="<?= $paginaAtual === 'confrontos' ? 'active' : '' ?>">Confrontos</a>
            <a href="resultados.php" class="<?= $paginaAtual === 'resultados' ? 'active' : '' ?>">Resultados</a>
        </nav>
        <div class="admin-sidebar-footer">
            <div class="user-name"><?= e($_SESSION['usu_nome'] ?? '') ?></div>
            <a href="../logout.php" class="logout-link">Sair</a>
        </div>
    </aside>

    <div class="admin-content">
        <div class="admin-topbar">
            <button type="button" class="sidebar-toggle" aria-label="Abrir menu">&#9776;</button>
            <h1><?= e($tituloPagina) ?></h1>
            <div></div>
        </div>
        <main class="admin-main">
            <?php
            $mensagem = obterMensagem();
            if ($mensagem):
            ?>
            <div class="alert alert-<?= e($mensagem['tipo']) ?>"><?= e($mensagem['texto']) ?></div>
            <?php endif; ?>
