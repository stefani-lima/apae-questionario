<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;

/** Troca da própria senha. */
final class ContaController extends Controller
{
    public function index(): void
    {
        $eu = $this->exigirLogin();
        $this->exibir($eu);
    }

    public function alterarSenha(): void
    {
        $eu = $this->exigirLogin();
        csrf_validar();

        $usuarios = new Usuario();
        $atual = (string) ($_POST['senha_atual'] ?? '');
        $nova = (string) ($_POST['senha_nova'] ?? '');
        $confirmacao = (string) ($_POST['senha_confirmacao'] ?? '');
        $erros = [];

        $usuario = $usuarios->buscar($eu['id']);
        if (!$usuario || !password_verify($atual, $usuario['senha_hash'])) {
            $erros['senha_atual'] = 'A senha atual não confere.';
        }
        if (mb_strlen($nova) < 8) {
            $erros['senha_nova'] = 'A nova senha precisa ter pelo menos 8 caracteres.';
        } elseif ($nova !== $confirmacao) {
            $erros['senha_confirmacao'] = 'A confirmação está diferente da nova senha.';
        }

        if ($erros) {
            $this->exibir($eu, $erros);
            return;
        }
        $usuarios->alterarSenha($eu['id'], $nova);
        session_regenerate_id(true);
        flash('sucesso', 'Senha alterada. Use a nova senha no próximo acesso.');
        $this->redirecionar('admin/conta');
    }

    private function exibir(array $eu, array $erros = []): void
    {
        $this->view('admin/conta', [
            'titulo' => 'Minha conta',
            'menu' => 'conta',
            'eu' => $eu,
            'erros' => $erros,
        ]);
    }
}
