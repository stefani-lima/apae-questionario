<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Throwable;

/**
 * Verificações para a hospedagem e monitores.
 * /ping  não consulta o banco: use no monitor (UptimeRobot) para manter o site acordado
 *        sem gastar as horas de processamento do banco gratuito.
 * /saude consulta o banco: use para checar manualmente se está tudo funcionando.
 */
final class SaudeController extends Controller
{
    public function ping(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-store');
        echo 'ok';
    }

    public function verificar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        try {
            Database::conexao()->query('SELECT 1');
            echo json_encode(['status' => 'ok']);
        } catch (Throwable) {
            http_response_code(503);
            echo json_encode(['status' => 'banco indisponível']);
        }
    }
}
