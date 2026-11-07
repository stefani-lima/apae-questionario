<?php
declare(strict_types=1);

/* Verificação de saúde para o provedor de hospedagem e monitores (ex.: UptimeRobot). */

require dirname(__DIR__) . '/src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    App\Database::conexao()->query('SELECT 1');
    echo json_encode(['status' => 'ok']);
} catch (Throwable $erro) {
    http_response_code(503);
    echo json_encode(['status' => 'banco indisponível']);
}
