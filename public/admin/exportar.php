<?php
declare(strict_types=1);

/* US4 — Exportação em CSV compatível com o Excel em português (separador ";" e BOM UTF-8). */

require dirname(__DIR__, 2) . '/src/bootstrap.php';

use App\AvaliacaoRepositorio;

exigir_login();

$periodo = AvaliacaoRepositorio::periodoDaRequisicao($_GET);
[$cabecalho, $linhas] = (new AvaliacaoRepositorio())->planilha($periodo);

$nomeArquivo = 'avaliacoes-apae-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
header('Cache-Control: no-store');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM: faz o Excel reconhecer acentos

$protegerFormula = static function (string $valor): string {
    // Evita "CSV injection": textos que começam com = + - @ seriam executados como fórmula.
    return preg_match('/^[=+\-@\t\r]/', $valor) ? "'" . $valor : $valor;
};

fputcsv($saida, $cabecalho, ';', '"', '');
foreach ($linhas as $linha) {
    fputcsv($saida, array_map($protegerFormula, $linha), ';', '"', '');
}
fclose($saida);
