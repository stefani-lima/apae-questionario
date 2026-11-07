<?php
declare(strict_types=1);

/* US3 — Criar ou editar uma pergunta. */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\PerguntaRepositorio;

exigir_login();

$repositorio = new PerguntaRepositorio();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$existente = $id ? $repositorio->buscar($id) : null;

if ($id && $existente === null) {
    flash('erro', 'Pergunta não encontrada.');
    redirecionar('admin/perguntas.php');
}

$tipoBloqueado = $existente !== null && (int) $existente['total_respostas'] > 0;

$dados = [
    'texto' => $existente['texto'] ?? '',
    'tipo' => $existente['tipo'] ?? 'escala',
    'obrigatoria' => $existente ? (bool) $existente['obrigatoria'] : true,
    'ativa' => $existente ? (bool) $existente['ativa'] : true,
];
$erros = [];

if (metodo_post()) {
    csrf_validar();
    $dados = [
        'texto' => trim((string) ($_POST['texto'] ?? '')),
        // Com respostas gravadas, o tipo não muda (RF13).
        'tipo' => $tipoBloqueado ? $existente['tipo'] : (string) ($_POST['tipo'] ?? ''),
        'obrigatoria' => isset($_POST['obrigatoria']),
        'ativa' => isset($_POST['ativa']),
    ];
    $erros = PerguntaRepositorio::validar($dados);

    if (!$erros) {
        if ($existente) {
            $repositorio->atualizar($id, $dados['texto'], $dados['tipo'], $dados['obrigatoria'], $dados['ativa']);
            flash('sucesso', 'Pergunta atualizada.');
        } else {
            $repositorio->criar($dados['texto'], $dados['tipo'], $dados['obrigatoria'], $dados['ativa']);
            flash('sucesso', 'Pergunta criada. Ela foi colocada no fim da lista.');
        }
        redirecionar('admin/perguntas.php');
    }
}

render('admin/pergunta', [
    'titulo' => $existente ? 'Editar pergunta' : 'Nova pergunta',
    'menu' => 'perguntas',
    'id' => $id,
    'dados' => $dados,
    'erros' => $erros,
    'tipoBloqueado' => $tipoBloqueado,
    'totalRespostas' => (int) ($existente['total_respostas'] ?? 0),
]);
