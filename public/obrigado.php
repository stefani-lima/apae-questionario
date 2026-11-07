<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

render('publico/obrigado', [
    'titulo' => 'Obrigado!',
    'totem' => !empty($_SESSION['totem']),
], 'publico');
