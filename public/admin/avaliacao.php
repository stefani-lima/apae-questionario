<?php
declare(strict_types=1);

/* US4 — Detalhe de uma avaliação (e exclusão). */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\AvaliacaoRepositorio;

exigir_login();

$repositorio = new AvaliacaoRepositorio();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if (metodo_post()) {
    csrf_validar();
    if (($_POST['acao'] ?? '') === 'excluir' && $repositorio->buscar($id)) {
        $repositorio->excluir($id);
        flash('sucesso', "Avaliação #$id excluída.");
    }
    redirecionar('admin/avaliacoes.php');
}

$avaliacao = $repositorio->buscar($id);
if ($avaliacao === null) {
    flash('erro', 'Avaliação não encontrada.');
    redirecionar('admin/avaliacoes.php');
}

render('admin/avaliacao', [
    'titulo' => "Avaliação #$id",
    'menu' => 'avaliacoes',
    'avaliacao' => $avaliacao,
]);
