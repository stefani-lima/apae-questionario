<?php
declare(strict_types=1);

/*
 * Ponto de entrada comum: toda página pública começa com
 *   require dirname(__DIR__) . '/src/bootstrap.php';
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/config.php';
require BASE_PATH . '/src/helpers.php';

// Carrega automaticamente as classes App\Xxx de src/Xxx.php
spl_autoload_register(function (string $classe): void {
    $prefixo = 'App\\';
    if (!str_starts_with($classe, $prefixo)) {
        return;
    }
    $arquivo = BASE_PATH . '/src/' . str_replace('\\', '/', substr($classe, strlen($prefixo))) . '.php';
    if (is_file($arquivo)) {
        require $arquivo;
    }
});

require BASE_PATH . '/src/auth.php';

date_default_timezone_set('America/Sao_Paulo');
mb_internal_encoding('UTF-8');

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('X-Frame-Options: SAMEORIGIN');

iniciar_sessao();
