<?php
declare(strict_types=1);

/* US3 — Lista de perguntas: ordenar, ativar/desativar, excluir. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\PerguntaRepositorio;

exigir_login();

$repositorio = new PerguntaRepositorio();

if (metodo_post()) {
    csrf_validar();
    $id = (int) ($_POST['id'] ?? 0);
    $pergunta = $repositorio->buscar($id);

    if ($pergunta === null) {
        flash('erro', 'Pergunta não encontrada.');
    } else {
        switch ($_POST['acao'] ?? '') {
            case 'subir':
            case 'descer':
                $repositorio->mover($id, $_POST['acao']);
                break;
            case 'alternar':
                $repositorio->alternarAtiva($id);
                flash('sucesso', $pergunta['ativa']
                    ? 'Pergunta desativada. Ela não aparece mais no questionário.'
                    : 'Pergunta ativada. Ela já aparece no questionário.');
                break;
            case 'excluir':
                if ($repositorio->excluir($id)) {
                    flash('sucesso', 'Pergunta excluída.');
                } else {
                    flash('erro', 'Esta pergunta já tem respostas e não pode ser excluída. Desative-a para tirá-la do questionário sem perder os resultados.');
                }
                break;
        }
    }
    redirecionar('admin/perguntas.php' . (isset($_POST['acao']) && in_array($_POST['acao'], ['subir', 'descer'], true) ? '#pergunta-' . $id : ''));
}

render('admin/perguntas', [
    'titulo' => 'Perguntas',
    'menu' => 'perguntas',
    'perguntas' => $repositorio->listarTodas(),
]);
