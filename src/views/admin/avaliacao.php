<?php /** @var array $avaliacao */ ?>
<p class="voltar"><a href="<?= e(url('admin/avaliacoes.php')) ?>">Voltar para avaliações</a></p>

<div class="cabecalho-pagina">
    <div>
        <h1>Avaliação #<?= (int) $avaliacao['id'] ?></h1>
        <p class="subtitulo">
            Recebida em <?= e(data_br($avaliacao['criado_em'])) ?>. Origem: <?= e(ROTULOS_ORIGEM[$avaliacao['origem']] ?? $avaliacao['origem']) ?>
        </p>
    </div>
</div>

<section class="bloco">
    <dl class="respostas-detalhe">
        <?php foreach ($avaliacao['respostas'] as $r): ?>
            <div>
                <dt><?= e($r['pergunta']) ?></dt>
                <dd>
                    <?php if ($r['nota'] !== null): ?>
                        <span class="media-celula"><?= carinha((int) $r['nota'], 'celula-rosto') ?> <?= e(ROTULOS_NOTA[(int) $r['nota']]) ?> (<?= (int) $r['nota'] ?>)</span>
                    <?php else: ?>
                        <blockquote><?= nl2br(e($r['texto'])) ?></blockquote>
                    <?php endif; ?>
                </dd>
            </div>
        <?php endforeach; ?>
        <?php if (!$avaliacao['respostas']): ?>
            <p class="resultado-sem">Esta avaliação não tem respostas.</p>
        <?php endif; ?>
    </dl>
</section>

<section class="zona-perigo">
    <h2>Excluir avaliação</h2>
    <p>Use para remover testes ou respostas indevidas. Não é possível desfazer.</p>
    <form method="post" action="<?= e(url('admin/avaliacao.php')) ?>"
          onsubmit="return confirm('Excluir a avaliação #<?= (int) $avaliacao['id'] ?>? Não é possível desfazer.');">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $avaliacao['id'] ?>">
        <input type="hidden" name="acao" value="excluir">
        <button type="submit" class="botao botao-perigo botao-pequeno">Excluir avaliação</button>
    </form>
</section>
