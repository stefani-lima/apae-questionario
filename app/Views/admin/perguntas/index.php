<?php /** @var array $perguntas */
use App\Models\Pergunta;

$ativas = count(array_filter($perguntas, fn ($p) => $p['ativa']));
$ultimo = count($perguntas) - 1;
?>
<div class="cabecalho-pagina">
    <div>
        <h1>Perguntas</h1>
        <p class="subtitulo"><?= $ativas ?> de <?= count($perguntas) ?> aparecem no questionário, nesta ordem.</p>
    </div>
    <div class="acoes-pagina">
        <a class="botao botao-secundario" href="<?= e(url('')) ?>" target="_blank" rel="noopener">Ver questionário</a>
        <a class="botao" href="<?= e(url('admin/perguntas/nova')) ?>">Nova pergunta</a>
    </div>
</div>

<?php if (!$perguntas): ?>
    <div class="vazio">
        <?= carinha(3, 'vazio-rosto') ?>
        <h2>Nenhuma pergunta cadastrada</h2>
        <p>O questionário só aparece para o público depois que houver pelo menos uma pergunta ativa.</p>
        <a class="botao" href="<?= e(url('admin/perguntas/nova')) ?>">Criar a primeira pergunta</a>
    </div>
<?php else: ?>
    <ol class="lista-perguntas">
        <?php foreach ($perguntas as $i => $p):
            $id = (int) $p['id'];
            $respostas = (int) $p['total_respostas'];
        ?>
            <li class="item-pergunta<?= $p['ativa'] ? '' : ' item-inativo' ?>" id="pergunta-<?= $id ?>">
                <div class="item-ordem">
                    <form method="post" action="<?= e(url('admin/perguntas/' . $id . '/mover')) ?>">
                        <?= csrf_campo() ?>
                        <button type="submit" name="direcao" value="subir" class="botao-icone" <?= $i === 0 ? 'disabled' : '' ?>
                                aria-label="Mover para cima: <?= e(resumir($p['texto'], 40)) ?>">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 12l5-5 5 5"/></svg>
                        </button>
                        <button type="submit" name="direcao" value="descer" class="botao-icone" <?= $i === $ultimo ? 'disabled' : '' ?>
                                aria-label="Mover para baixo: <?= e(resumir($p['texto'], 40)) ?>">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 8l5 5 5-5"/></svg>
                        </button>
                    </form>
                </div>

                <div class="item-corpo">
                    <p class="item-texto"><?= e($p['texto']) ?></p>
                    <p class="item-detalhes">
                        <span class="etiqueta etiqueta-<?= e($p['tipo']) ?>"><?= $p['tipo'] === 'escala' ? 'Carinhas 1 a 5' : 'Texto livre' ?></span>
                        <span><?= $p['obrigatoria'] ? 'Obrigatória' : 'Opcional' ?></span>
                        <span><?= $respostas ?> <?= $respostas === 1 ? 'resposta' : 'respostas' ?></span>
                        <?php if (!$p['ativa']): ?><span class="etiqueta">Fora do questionário</span><?php endif; ?>
                    </p>
                </div>

                <div class="item-acoes">
                    <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/perguntas/' . $id . '/editar')) ?>">Editar</a>
                    <form method="post" action="<?= e(url('admin/perguntas/' . $id . '/alternar')) ?>">
                        <?= csrf_campo() ?>
                        <button type="submit" class="botao botao-secundario botao-pequeno">
                            <?= $p['ativa'] ? 'Desativar' : 'Ativar' ?>
                        </button>
                    </form>
                    <?php if ($respostas === 0): ?>
                        <form method="post" action="<?= e(url('admin/perguntas/' . $id . '/excluir')) ?>"
                              onsubmit="return confirm('Excluir esta pergunta? Não é possível desfazer.');">
                            <?= csrf_campo() ?>
                            <button type="submit" class="botao botao-perigo-texto botao-pequeno">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>
