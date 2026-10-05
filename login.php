
<?php

require_once __DIR__ . '/autenticacao.php';
require_once __DIR__ . '/includes/funcoes.php';

if (estaLogado()) {
    redirecionar('admin/index.php');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha = (string) ($_POST['senha'] ?? '');

    if ($usuario === '' || $senha === '') {

        $erro = 'Usuário ou senha inválidos.';

    } elseif (autenticar($usuario, $senha)) {

        redirecionar('admin/index.php');

    } else {

        $erro = 'Usuário ou senha inválidos.';

    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login · INTERCLASSE SESI</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <!-- Logo -->

        <div class="login-logo">
            IS
        </div>


        <!-- Título -->

        <h1>
            INTERCLASSE SESI
        </h1>

        <p class="login-sub">
            Área administrativa
        </p>


        <!-- Mensagem de erro -->

        <?php if ($erro): ?>

            <div class="alert alert-danger">
                <?= e($erro) ?>
            </div>

        <?php endif; ?>


        <!-- Formulário -->

        <form
            method="POST"
            action="login.php"
            novalidate
        >

            <div class="form-group">

                <label for="usuario">
                    Usuário ou e-mail
                </label>

                <input
                    type="text"
                    id="usuario"
                    name="usuario"
                    required
                    autofocus
                    autocomplete="username"
                    value="<?= e($_POST['usuario'] ?? '') ?>"
                >

            </div>


            <div class="form-group">

                <label for="senha">
                    Senha
                </label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    autocomplete="current-password"
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary btn-block"
            >
                Entrar
            </button>

        </form>


        <!-- Voltar -->

        <a
            href="index.php"
            class="login-back"
        >
            &larr; Voltar ao site
        </a>

    </div>

</div>

</body>

</html>
