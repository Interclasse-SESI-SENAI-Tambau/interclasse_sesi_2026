<?php
/**
 * Controle de sessão e autenticação administrativa.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conexao.php';

function estaLogado(): bool
{
    return isset($_SESSION['usu_id']);
}

function exigirLogin(): void
{
    if (!estaLogado()) {
        header('Location: ' . caminhoLogin());
        exit;
    }
}

/**
 * Resolve o caminho relativo de login.php a partir de qualquer subpasta (admin/).
 */
function caminhoLogin(): string
{
    $dentroDeAdmin = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/admin/') !== false;
    return $dentroDeAdmin ? '../login.php' : 'login.php';
}

function autenticar(string $usuario, string $senha): bool
{
    $pdo = conectar();

    $stmt = $pdo->prepare(
        'SELECT USU_ID, USU_NOME, USU_EMAIL, USU_SENHA
         FROM USUARIOS
         WHERE USU_NOME = :usuario1 OR USU_EMAIL = :usuario2
         LIMIT 1'
    );
    $stmt->execute(['usuario1' => $usuario, 'usuario2' => $usuario]);
    $usuarioEncontrado = $stmt->fetch();

    if (!$usuarioEncontrado || !password_verify($senha, $usuarioEncontrado['USU_SENHA'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['usu_id'] = $usuarioEncontrado['USU_ID'];
    $_SESSION['usu_nome'] = $usuarioEncontrado['USU_NOME'];
    $_SESSION['usu_email'] = $usuarioEncontrado['USU_EMAIL'];

    return true;
}
