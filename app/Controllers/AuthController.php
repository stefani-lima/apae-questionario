<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

/** Entrada e saída da área administrativa. */
final class AuthController extends Controller
{
    public function formulario(): void
    {
        if (Auth::usuario()) {
            $this->redirecionar('admin');
        }
        $this->view('admin/login', ['titulo' => 'Entrar', 'email' => '', 'erro' => null]);
    }

    public function entrar(): void
    {
        csrf_validar();
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        $erro = ($email === '' || $senha === '')
            ? 'Preencha o e-mail e a senha.'
            : Auth::tentar($email, $senha);

        if ($erro === null) {
            $this->redirecionar('admin');
        }
        $this->view('admin/login', ['titulo' => 'Entrar', 'email' => $email, 'erro' => $erro]);
    }

    public function sair(): void
    {
        csrf_validar();
        Auth::sair();
        iniciar_sessao();
        flash('sucesso', 'Você saiu do sistema.');
        $this->redirecionar('admin/login');
    }
}
