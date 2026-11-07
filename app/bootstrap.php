<?php
declare(strict_types=1);

/*
 * Inicialização comum da aplicação (usada pelo front controller e pelos scripts).
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/config/config.php';
require BASE_PATH . '/app/Core/helpers.php';

// Autoload: a classe App\Pasta\Nome fica em app/Pasta/Nome.php
spl_autoload_register(function (string $classe): void {
    $prefixo = 'App\\';
    if (!str_starts_with($classe, $prefixo)) {
        return;
    }
    $arquivo = BASE_PATH . '/app/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: SAMEORIGIN');
    iniciar_sessao();
}
