<?php
declare(strict_types=1);

/*
 * Front controller: toda requisição passa por aqui e o roteador
 * encaminha para o controller certo (ver config/rotas.php).
 */

// No servidor embutido do PHP (php -S), arquivos que existem (CSS, JS, imagens) são entregues direto.
if (PHP_SAPI === 'cli-server') {
    $arquivo = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($arquivo) && $arquivo !== __FILE__) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

$router = new App\Core\Router();
require BASE_PATH . '/config/rotas.php';
$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
