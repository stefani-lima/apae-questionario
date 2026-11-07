<?php
declare(strict_types=1);

namespace App\Core;

/** Base de todos os controllers. */
abstract class Controller
{
    protected function view(string $visao, array $dados = [], string $layout = 'admin'): void
    {
        View::render($visao, $dados, $layout);
    }

    protected function redirecionar(string $caminho): never
    {
        redirecionar($caminho);
    }

    /** Interrompe a requisição se não houver administrador logado. */
    protected function exigirLogin(): array
    {
        return Auth::exigir();
    }
}
