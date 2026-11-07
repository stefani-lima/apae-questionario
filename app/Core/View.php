<?php
declare(strict_types=1);

namespace App\Core;

/** Renderiza uma view de app/Views dentro de um layout de app/Views/layouts. */
final class View
{
    public static function render(string $__visao, array $__dados = [], string $__layout = 'admin'): void
    {
        // Nomes com "__" evitam conflito com as variáveis passadas à view.
        extract($__dados, EXTR_SKIP);
        ob_start();
        require BASE_PATH . "/app/Views/{$__visao}.php";
        $conteudo = ob_get_clean();
        require BASE_PATH . "/app/Views/layouts/{$__layout}.php";
    }
}
