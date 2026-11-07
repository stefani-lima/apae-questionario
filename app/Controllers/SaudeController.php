<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Throwable;

/** Verificação de saúde para a hospedagem e monitores (ex.: UptimeRobot). */
final class SaudeController extends Controller
{
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
