<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

/** Cadastro e ativação dos administradores. */
final class UsuarioController extends Controller
{
    public function index(): void
    {
        $eu = $this->exigirLogin();
        $this->exibir($eu);
    }

    public function salvar(): void
    {
        $eu = $this->exigirLogin();
        csrf_validar();

        $usuarios = new Usuario();
        $novo = [
            'nome' => trim((string) ($_POST['nome'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
        ];
        $senha = (string) ($_POST['senha'] ?? '');
        $erros = [];

        if ($novo['nome'] === '' || mb_strlen($novo['nome']) > 120) {
            $erros['nome'] = 'Informe o nome (até 120 caracteres).';
        }
        if (!filter_var($novo['email'], FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido, como nome@apae.org.br.';
        } elseif ($usuarios->emailEmUso($novo['email'])) {
            $erros['email'] = 'Já existe um usuário com este e-mail.';
        }
        if (mb_strlen($senha) < 8) {
            $erros['senha'] = 'A senha precisa ter pelo menos 8 caracteres.';
        }

        if ($erros) {
            $this->exibir($eu, $novo, $erros);
            return;
        }
        $usuarios->criar($novo['nome'], $novo['email'], $senha);
        flash('sucesso', "Usuário {$novo['nome']} cadastrado. Passe a senha inicial pessoalmente e peça para trocá-la em \"Minha conta\".");
        $this->redirecionar('admin/usuarios');
    }

    public function alternar(int $id): void
    {
        $eu = $this->exigirLogin();
        csrf_validar();

        $usuarios = new Usuario();
        $alvo = $usuarios->buscar($id);
        if ($alvo === null) {
            flash('erro', 'Usuário não encontrado.');
        } elseif ($id === $eu['id']) {
            flash('erro', 'Você não pode desativar o seu próprio acesso.');
        } else {
            $usuarios->alternarAtivo($id);
            flash('sucesso', $alvo['ativo'] ? "Acesso de {$alvo['nome']} desativado." : "Acesso de {$alvo['nome']} reativado.");
        }
        $this->redirecionar('admin/usuarios');
    }

    private function exibir(array $eu, array $novo = ['nome' => '', 'email' => ''], array $erros = []): void
    {
        $this->view('admin/usuarios', [
            'titulo' => 'Usuários',
            'menu' => 'usuarios',
            'usuarios' => (new Usuario())->listar(),
            'eu' => $eu,
            'novo' => $novo,
            'erros' => $erros,
        ]);
    }
}
