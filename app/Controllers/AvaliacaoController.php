<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Avaliacao;

/** Lista, detalhe, exclusão e exportação das avaliações recebidas. */
final class AvaliacaoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $periodo = Avaliacao::periodoDaRequisicao($_GET);
        $avaliacoes = new Avaliacao();
        $total = $avaliacoes->contar($periodo);
        $paginas = max(1, (int) ceil($total / Avaliacao::POR_PAGINA));
        $pagina = min($paginas, max(1, (int) ($_GET['pagina'] ?? 1)));

        $this->view('admin/avaliacoes/index', [
            'titulo' => 'Avaliações',
            'menu' => 'avaliacoes',
            'periodo' => $periodo,
            'total' => $total,
            'pagina' => $pagina,
            'paginas' => $paginas,
            'avaliacoes' => $avaliacoes->listar($periodo, $pagina),
        ]);
    }

    public function mostrar(int $id): void
    {
        $this->exigirLogin();

        $avaliacao = (new Avaliacao())->buscar($id);
        if ($avaliacao === null) {
            flash('erro', 'Avaliação não encontrada.');
            $this->redirecionar('admin/avaliacoes');
        }
        $this->view('admin/avaliacoes/detalhe', [
            'titulo' => "Avaliação #$id",
            'menu' => 'avaliacoes',
            'avaliacao' => $avaliacao,
        ]);
    }

    public function excluir(int $id): void
    {
        $this->exigirLogin();
        csrf_validar();

        $avaliacoes = new Avaliacao();
        if ($avaliacoes->buscar($id)) {
            $avaliacoes->excluir($id);
            flash('sucesso', "Avaliação #$id excluída.");
        }
        $this->redirecionar('admin/avaliacoes');
    }

    /** CSV compatível com o Excel em português (separador ";" e BOM UTF-8). */
    public function exportar(): void
    {
        $this->exigirLogin();

        $periodo = Avaliacao::periodoDaRequisicao($_GET);
        [$cabecalho, $linhas] = (new Avaliacao())->planilha($periodo);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="avaliacoes-apae-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-store');

        $saida = fopen('php://output', 'w');
        fwrite($saida, "\xEF\xBB\xBF"); // BOM: faz o Excel reconhecer acentos

        // Evita "CSV injection": textos que começam com = + - @ seriam executados como fórmula.
        $protegerFormula = static fn (string $valor): string
            => preg_match('/^[=+\-@\t\r]/', $valor) ? "'" . $valor : $valor;

        fputcsv($saida, $cabecalho, ';', '"', '');
        foreach ($linhas as $linha) {
            fputcsv($saida, array_map($protegerFormula, $linha), ';', '"', '');
        }
        fclose($saida);
    }
}
