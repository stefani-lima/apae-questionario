<?php
declare(strict_types=1);

/* US4 — Painel de resultados. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\AvaliacaoRepositorio;

exigir_login();

$periodo = AvaliacaoRepositorio::periodoDaRequisicao($_GET);
$repositorio = new AvaliacaoRepositorio();

render('admin/painel', [
    'titulo' => 'Resultados',
    'menu' => 'painel',
    'periodo' => $periodo,
    'indicadores' => $repositorio->indicadores($periodo),
    'perguntas' => $repositorio->resumoPorPergunta($periodo),
    'comentarios' => $repositorio->comentariosRecentes($periodo),
]);
