<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Avaliacao;

/** Painel de resultados. */
final class PainelController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $periodo = Avaliacao::periodoDaRequisicao($_GET);
        $avaliacoes = new Avaliacao();

        $this->view('admin/painel', [
            'titulo' => 'Resultados',
            'menu' => 'painel',
            'periodo' => $periodo,
            'indicadores' => $avaliacoes->indicadores($periodo),
            'perguntas' => $avaliacoes->resumoPorPergunta($periodo),
            'comentarios' => $avaliacoes->comentariosRecentes($periodo),
        ]);
    }
}
