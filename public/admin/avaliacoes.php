<?php
declare(strict_types=1);

/* US4 — Lista de avaliações com paginação. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\AvaliacaoRepositorio;

exigir_login();

$periodo = AvaliacaoRepositorio::periodoDaRequisicao($_GET);
$repositorio = new AvaliacaoRepositorio();

$total = $repositorio->contar($periodo);
$paginas = max(1, (int) ceil($total / AvaliacaoRepositorio::POR_PAGINA));
$pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));

render('admin/avaliacoes', [
    'titulo' => 'Avaliações',
    'menu' => 'avaliacoes',
    'periodo' => $periodo,
    'total' => $total,
    'pagina' => $pagina,
    'paginas' => $paginas,
    'avaliacoes' => $repositorio->listar($periodo, $pagina),
]);
