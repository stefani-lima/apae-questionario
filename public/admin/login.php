<?php
declare(strict_types=1);

/* US2 — Entrada na área administrativa. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

if (usuario_logado()) {
    redirecionar('admin/index.php');
}

$email = '';
$erro = null;

if (metodo_post()) {
    csrf_validar();
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $erro = tentar_login($email, $senha);
        if ($erro === null) {
            redirecionar('admin/index.php');
        }
    }
}

render('admin/login', ['titulo' => 'Entrar', 'email' => $email, 'erro' => $erro]);
