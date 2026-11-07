<?php
/**
 * @var array $periodo
 * @var array $indicadores
 * @var array $perguntas
 * @var array $comentarios
 */
$query = http_build_query(array_filter([
    'periodo' => $periodo['chave'],
    'inicio' => $periodo['chave'] === 'personalizado' ? $periodo['inicio'] : null,
    'fim' => $periodo['chave'] === 'personalizado' ? $periodo['fim'] : null,
]));
$media = $indicadores['media'];
?>
<div class="cabecalho-pagina">
    <h1>Resultados</h1>
    <?php $acao = 'admin/index.php'; require __DIR__ . '/_periodo.php'; ?>
</div>

<?php if ($indicadores['avaliacoes'] === 0): ?>
    <div class="vazio">
        <?= carinha(3, 'vazio-rosto') ?>
        <h2>Nenhuma avaliação neste período</h2>
        <p>Assim que as pessoas responderem pelo QR Code, os resultados aparecem aqui.
           Escolha outro período ou <a href="<?= e(url('admin/qrcode.php')) ?>">imprima o cartaz com o QR Code</a>.</p>
    </div>
<?php else: ?>

    <section class="destaque" aria-label="Resumo do período">
        <div class="destaque-nota">
            <?= carinha(nota_arredondada($media), 'destaque-rosto') ?>
            <div>
                <p class="destaque-numero"><?= numero_br($media) ?> <span>de 5</span></p>
                <p class="destaque-legenda">Satisfação média: <strong><?= e(ROTULOS_NOTA[nota_arredondada($media)]) ?></strong></p>
            </div>
        </div>
        <dl class="destaque-numeros">
            <div><dt>Avaliações</dt><dd><?= numero_br($indicadores['avaliacoes'], 0) ?></dd></div>
            <div><dt>Notas Bom ou Ótimo</dt><dd><?= $indicadores['positivas'] === null ? '—' : numero_br($indicadores['positivas'], 0) . '%' ?></dd></div>
            <div><dt>Comentários</dt><dd><?= numero_br($indicadores['comentarios'], 0) ?></dd></div>
        </dl>
    </section>

    <section class="bloco">
        <div class="bloco-cabeca">
            <h2>Notas por pergunta</h2>
            <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/exportar.php?' . $query)) ?>">Exportar planilha</a>
        </div>

        <ol class="legenda" aria-label="Legenda das notas">
            <?php foreach (ROTULOS_NOTA as $nota => $rotulo): ?>
                <li><span class="amostra nota-<?= $nota ?>"></span><?= e($rotulo) ?></li>
            <?php endforeach; ?>
        </ol>

        <ul class="resultados">
            <?php foreach ($perguntas as $pergunta):
                $total = (int) $pergunta['total'];
                $m = $pergunta['media'] === null ? null : (float) $pergunta['media'];
            ?>
                <li class="resultado">
                    <div class="resultado-topo">
                        <h3><?= e($pergunta['texto']) ?>
                            <?php if (!$pergunta['ativa']): ?><span class="etiqueta">Desativada</span><?php endif; ?>
                        </h3>
                        <p class="resultado-media">
                            <?php if ($m !== null): ?>
                                <?= carinha(nota_arredondada($m), 'resultado-rosto') ?>
                                <strong><?= numero_br($m) ?></strong>
                            <?php endif; ?>
                            <span><?= $total ?> <?= $total === 1 ? 'resposta' : 'respostas' ?></span>
                        </p>
                    </div>
                    <?php if ($total > 0): ?>
                        <div class="barra-notas" role="img"
                             aria-label="<?= e(implode(', ', array_map(fn ($n) => ROTULOS_NOTA[$n] . ': ' . $pergunta["n$n"], range(1, 5)))) ?>">
                            <?php for ($n = 1; $n <= 5; $n++):
                                $qtd = (int) $pergunta["n$n"];
                                if ($qtd === 0) continue;
                                $pct = $qtd * 100 / $total;
                            ?>
                                <span class="nota-<?= $n ?>" style="width: <?= number_format($pct, 2, '.', '') ?>%"
                                      title="<?= e(ROTULOS_NOTA[$n] . ': ' . $qtd) ?>"><?= $pct >= 9 ? round($pct) . '%' : '' ?></span>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <p class="resultado-sem">Sem respostas no período.</p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="bloco">
        <div class="bloco-cabeca">
            <h2>Comentários recentes</h2>
            <a href="<?= e(url('admin/avaliacoes.php?' . $query)) ?>">Ver todas as avaliações</a>
        </div>
        <?php if (!$comentarios): ?>
            <p class="resultado-sem">Ninguém deixou comentário neste período.</p>
        <?php else: ?>
            <ul class="comentarios">
                <?php foreach ($comentarios as $c): ?>
                    <li>
                        <blockquote><?= nl2br(e($c['texto'])) ?></blockquote>
                        <p><a href="<?= e(url('admin/avaliacao.php?id=' . (int) $c['envio_id'])) ?>">Avaliação #<?= (int) $c['envio_id'] ?></a>, <?= e(data_br($c['criado_em'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>
