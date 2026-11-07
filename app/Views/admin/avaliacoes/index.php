<?php
/**
 * @var array $periodo @var int $total @var int $pagina @var int $paginas @var array $avaliacoes
 */
$filtros = array_filter([
    'periodo' => $periodo['chave'],
    'inicio' => $periodo['chave'] === 'personalizado' ? $periodo['inicio'] : null,
    'fim' => $periodo['chave'] === 'personalizado' ? $periodo['fim'] : null,
]);
$linkPagina = fn (int $p) => url('admin/avaliacoes?' . http_build_query($filtros + ['pagina' => $p]));
?>
<div class="cabecalho-pagina">
    <h1>Avaliações</h1>
    <?php $acao = 'admin/avaliacoes'; require BASE_PATH . '/app/Views/admin/partials/periodo.php'; ?>
</div>

<section class="bloco">
    <div class="bloco-cabeca">
        <p class="bloco-resumo"><?= $total === 1 ? '1 avaliação' : numero_br($total, 0) . ' avaliações' ?> no período</p>
        <?php if ($total > 0): ?>
            <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/avaliacoes/exportar?' . http_build_query($filtros))) ?>">Exportar planilha</a>
        <?php endif; ?>
    </div>

    <?php if (!$avaliacoes): ?>
        <p class="resultado-sem">Nenhuma avaliação neste período.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead>
                    <tr>
                        <th scope="col">Nº</th>
                        <th scope="col">Data e hora</th>
                        <th scope="col">Média</th>
                        <th scope="col">Origem</th>
                        <th scope="col">Comentário</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avaliacoes as $a):
                        $m = $a['media'] === null ? null : (float) $a['media']; ?>
                        <tr>
                            <td><a href="<?= e(url('admin/avaliacoes/' . (int) $a['id'])) ?>">#<?= (int) $a['id'] ?></a></td>
                            <td class="sem-quebra"><?= e(data_br($a['criado_em'])) ?></td>
                            <td class="sem-quebra">
                                <?php if ($m !== null): ?>
                                    <span class="media-celula"><?= carinha(nota_arredondada($m), 'celula-rosto') ?> <?= numero_br($m) ?></span>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td><?= e(ROTULOS_ORIGEM[$a['origem']] ?? $a['origem']) ?></td>
                            <td class="celula-comentario"><?= $a['comentario'] ? e(resumir($a['comentario'])) : '<span class="apagado">Sem comentário</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($paginas > 1): ?>
            <nav class="paginacao" aria-label="Páginas">
                <?php if ($pagina > 1): ?><a href="<?= e($linkPagina($pagina - 1)) ?>">Anterior</a><?php endif; ?>
                <span>Página <?= $pagina ?> de <?= $paginas ?></span>
                <?php if ($pagina < $paginas): ?><a href="<?= e($linkPagina($pagina + 1)) ?>">Próxima</a><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
