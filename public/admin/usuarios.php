<?php
declare(strict_types=1);

/* US6 — Cadastro e ativação de administradores. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\UsuarioRepositorio;

$eu = exigir_login();
$repositorio = new UsuarioRepositorio();

$novo = ['nome' => '', 'email' => ''];
$erros = [];

if (metodo_post()) {
    csrf_validar();
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'alternar') {
        $id = (int) ($_POST['id'] ?? 0);
        $alvo = $repositorio->buscar($id);
        if ($alvo === null) {
            flash('erro', 'Usuário não encontrado.');
        } elseif ($id === $eu['id']) {
            flash('erro', 'Você não pode desativar o seu próprio acesso.');
        } else {
            $repositorio->alternarAtivo($id);
            flash('sucesso', $alvo['ativo'] ? "Acesso de {$alvo['nome']} desativado." : "Acesso de {$alvo['nome']} reativado.");
        }
        redirecionar('admin/usuarios.php');
    }

    if ($acao === 'criar') {
        $novo = [
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
        ];
        $senha = (string) ($_POST['senha'] ?? '');

        if ($novo['nome'] === '' || mb_strlen($novo['nome']) > 120) {
            $erros['nome'] = 'Informe o nome (até 120 caracteres).';
        }
        if (!filter_var($novo['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido, como nome@apae.org.br.';
        } elseif ($repositorio->emailEmUso($novo['email'])) {
            $erros['email'] = 'Já existe um usuário com este e-mail.';
        }
        if (mb_strlen($senha) < 8) {
            $erros['senha'] = 'A senha precisa ter pelo menos 8 caracteres.';
        }

        if (!$erros) {
            $repositorio->criar($novo['nome'], $novo['email'], $senha);
            flash('sucesso', "Usuário {$novo['nome']} cadastrado. Passe a senha inicial pessoalmente e peça para trocá-la em \"Minha conta\".");
            redirecionar('admin/usuarios.php');
        }
    }
}

render('admin/usuarios', [
    'titulo' => 'Usuários',
    'menu' => 'usuarios',
    'usuarios' => $repositorio->listar(),
    'eu' => $eu,
    'novo' => $novo,
    'erros' => $erros,
]);
