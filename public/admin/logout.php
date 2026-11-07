<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/bootstrap.php';

if (metodo_post()) {
    csrf_validar();
    sair();
    iniciar_sessao();
    flash('sucesso', 'Você saiu do sistema.');
}
redirecionar('admin/login.php');
