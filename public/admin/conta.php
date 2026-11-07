<?php
declare(strict_types=1);

/* US6 — Trocar a própria senha. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\UsuarioRepositorio;

$eu = exigir_login();
$repositorio = new UsuarioRepositorio();
$erros = [];

if (metodo_post()) {
    csrf_validar();
    $atual = (string) ($_POST['senha_atual'] ?? '');
    $nova = (string) ($_POST['senha_nova'] ?? '');
    $confirmacao = (string) ($_POST['senha_confirmacao'] ?? '');

    $usuario = $repositorio->buscar($eu['id']);
    if (!$usuario || !password_verify($atual, $usuario['senha_hash'])) {
        $erros['senha_atual'] = 'A senha atual não confere.';
    }
    if (mb_strlen($nova) < 8) {
        $erros['senha_nova'] = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif ($nova !== $confirmacao) {
        $erros['senha_confirmacao'] = 'A confirmação está diferente da nova senha.';
    }

    if (!$erros) {
        $repositorio->alterarSenha($eu['id'], $nova);
        session_regenerate_id(true);
        flash('sucesso', 'Senha alterada. Use a nova senha no próximo acesso.');
        redirecionar('admin/conta.php');
    }
}

render('admin/conta', [
    'titulo' => 'Minha conta',
    'menu' => 'conta',
    'eu' => $eu,
    'erros' => $erros,
]);
