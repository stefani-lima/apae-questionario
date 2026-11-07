<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pergunta;

/** Cadastro, edição, ordenação, ativação e exclusão de perguntas. */
final class PerguntaController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();
        $this->view('admin/perguntas/index', [
            'titulo' => 'Perguntas',
            'menu' => 'perguntas',
            'perguntas' => (new Pergunta())->listarTodas(),
        ]);
    }

    public function criar(): void
    {
        $this->exigirLogin();
        $this->formulario(null, ['texto' => '', 'tipo' => 'escala', 'obrigatoria' => true, 'ativa' => true]);
    }

    public function salvar(): void
    {
        $this->exigirLogin();
        csrf_validar();

        $dados = $this->dadosDoFormulario();
        $erros = Pergunta::validar($dados);
        if ($erros) {
            $this->formulario(null, $dados, $erros);
            return;
        }
        (new Pergunta())->criar($dados['texto'], $dados['tipo'], $dados['obrigatoria'], $dados['ativa']);
        flash('sucesso', 'Pergunta criada. Ela foi colocada no fim da lista.');
        $this->redirecionar('admin/perguntas');
    }

    public function editar(int $id): void
    {
        $this->exigirLogin();
        $pergunta = $this->buscarOuVoltar($id);
        $this->formulario($pergunta, [
            'texto' => $pergunta['texto'],
            'tipo' => $pergunta['tipo'],
            'obrigatoria' => (bool) $pergunta['obrigatoria'],
            'ativa' => (bool) $pergunta['ativa'],
        ]);
    }

    public function atualizar(int $id): void
    {
        $this->exigirLogin();
        csrf_validar();
        $pergunta = $this->buscarOuVoltar($id);

        $dados = $this->dadosDoFormulario();
        // Com respostas gravadas, o tipo não muda (RF13).
        if ((int) $pergunta['total_respostas'] > 0) {
            $dados['tipo'] = $pergunta['tipo'];
        }
        $erros = Pergunta::validar($dados);
        if ($erros) {
            $this->formulario($pergunta, $dados, $erros);
            return;
        }
        (new Pergunta())->atualizar($id, $dados['texto'], $dados['tipo'], $dados['obrigatoria'], $dados['ativa']);
        flash('sucesso', 'Pergunta atualizada.');
        $this->redirecionar('admin/perguntas');
    }

    public function mover(int $id): void
    {
        $this->exigirLogin();
        csrf_validar();
        $this->buscarOuVoltar($id);
        (new Pergunta())->mover($id, ($_POST['direcao'] ?? '') === 'subir' ? 'subir' : 'descer');
        $this->redirecionar('admin/perguntas#pergunta-' . $id);
    }

    public function alternar(int $id): void
    {
        $this->exigirLogin();
        csrf_validar();
        $pergunta = $this->buscarOuVoltar($id);
        (new Pergunta())->alternarAtiva($id);
        flash('sucesso', $pergunta['ativa']
            ? 'Pergunta desativada. Ela não aparece mais no questionário.'
            : 'Pergunta ativada. Ela já aparece no questionário.');
        $this->redirecionar('admin/perguntas');
    }

    public function excluir(int $id): void
    {
        $this->exigirLogin();
        csrf_validar();
        if ((new Pergunta())->excluir($id)) {
            flash('sucesso', 'Pergunta excluída.');
        } else {
            flash('erro', 'Esta pergunta já tem respostas e não pode ser excluída. Desative-a para tirá-la do questionário sem perder os resultados.');
        }
        $this->redirecionar('admin/perguntas');
    }

    private function formulario(?array $pergunta, array $dados, array $erros = []): void
    {
        $totalRespostas = (int) ($pergunta['total_respostas'] ?? 0);
        $this->view('admin/perguntas/formulario', [
            'titulo' => $pergunta ? 'Editar pergunta' : 'Nova pergunta',
            'menu' => 'perguntas',
            'id' => (int) ($pergunta['id'] ?? 0),
            'dados' => $dados,
            'erros' => $erros,
            'tipoBloqueado' => $totalRespostas > 0,
            'totalRespostas' => $totalRespostas,
        ]);
    }

    private function dadosDoFormulario(): array
    {
        return [
            'texto' => trim((string) ($_POST['texto'] ?? '')),
            'tipo' => (string) ($_POST['tipo'] ?? ''),
            'obrigatoria' => isset($_POST['obrigatoria']),
            'ativa' => isset($_POST['ativa']),
        ];
    }

    private function buscarOuVoltar(int $id): array
    {
        $pergunta = (new Pergunta())->buscar($id);
        if ($pergunta === null) {
            flash('erro', 'Pergunta não encontrada.');
            $this->redirecionar('admin/perguntas');
        }
        return $pergunta;
    }
}
