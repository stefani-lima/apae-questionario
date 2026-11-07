<?php
/**
 * @var array  $perguntas
 * @var array  $erros
 * @var array  $valores
 * @var string $tokenEnvio
 * @var string $origem
 */
$total = count($perguntas);
?>
<?php if ($total === 0): ?>
    <section class="abertura">
        <h1>O questionário está em atualização.</h1>
        <p class="abertura-texto">Volte em breve para nos contar como foi sua visita.</p>
    </section>
<?php else: ?>
    <section class="abertura">
        <h1>Como foi a sua visita hoje?</h1>
        <p class="abertura-texto">
            São <?= $total ?> perguntas rápidas. Toque na carinha que mais combina com o que você sentiu.
        </p>
    </section>

    <?php if ($erros): ?>
        <div class="aviso aviso-erro" role="alert" tabindex="-1" id="resumo-erros">
            <?php if (isset($erros['geral'])): ?>
                <p><?= e($erros['geral']) ?></p>
            <?php else: ?>
                <p>Falta responder <?= count($erros) === 1 ? '1 pergunta' : count($erros) . ' perguntas' ?>:</p>
                <ul>
                    <?php foreach ($perguntas as $i => $p): if (!isset($erros[$p['id']])) continue; ?>
                        <li><a href="#p-<?= (int) $p['id'] ?>">Pergunta <?= $i + 1 ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('')) ?>" class="questionario" id="questionario" novalidate>
        <?= csrf_campo() ?>
        <input type="hidden" name="envio_token" value="<?= e($tokenEnvio) ?>">
        <input type="hidden" name="origem" value="<?= e($origem) ?>">

        <div class="campo-armadilha" aria-hidden="true">
            <label>Não preencha este campo <input type="text" name="site" tabindex="-1" autocomplete="off"></label>
        </div>

        <?php foreach ($perguntas as $i => $pergunta):
            $id = (int) $pergunta['id'];
            $erro = $erros[$id] ?? null;
            $valor = $valores[$id] ?? '';
        ?>
            <fieldset class="pergunta<?= $erro ? ' pergunta-com-erro' : '' ?>" id="p-<?= $id ?>"
                      data-obrigatoria="<?= $pergunta['obrigatoria'] ? '1' : '0' ?>"
                      data-tipo="<?= e($pergunta['tipo']) ?>"
                      <?= $erro ? 'aria-describedby="erro-' . $id . '"' : '' ?>>
                <legend>
                    <span class="pergunta-posicao"><?= $i + 1 ?> de <?= $total ?></span>
                    <span class="pergunta-texto"><?= e($pergunta['texto']) ?></span>
                    <?php if (!$pergunta['obrigatoria']): ?>
                        <span class="pergunta-opcional">Opcional</span>
                    <?php endif; ?>
                </legend>

                <?php if ($pergunta['tipo'] === 'escala'): ?>
                    <div class="escala">
                        <?php foreach (ROTULOS_NOTA as $nota => $rotulo): ?>
                            <label class="opcao">
                                <input type="radio" name="resp[<?= $id ?>]" value="<?= $nota ?>"
                                    <?= (string) $valor === (string) $nota ? 'checked' : '' ?>>
                                <?= carinha($nota) ?>
                                <span class="opcao-rotulo"><?= e($rotulo) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <label class="sr-only" for="resp-<?= $id ?>">Sua resposta</label>
                    <textarea id="resp-<?= $id ?>" name="resp[<?= $id ?>]" rows="4"
                              maxlength="<?= \App\Models\Avaliacao::TEXTO_MAX ?>"
                              placeholder="Escreva aqui, se quiser"
                              data-contador="contador-<?= $id ?>"><?= e($valor) ?></textarea>
                    <p class="contador" id="contador-<?= $id ?>" aria-live="polite"></p>
                <?php endif; ?>

                <p class="pergunta-erro" id="erro-<?= $id ?>" <?= $erro ? '' : 'hidden' ?>><?= e($erro ?? '') ?></p>
            </fieldset>
        <?php endforeach; ?>

        <div class="enviar">
            <button type="submit" class="botao botao-enviar" data-enviando="Enviando…">Enviar avaliação</button>
        </div>
    </form>
    <script src="<?= asset('js/questionario.js') ?>" defer></script>
<?php endif; ?>
