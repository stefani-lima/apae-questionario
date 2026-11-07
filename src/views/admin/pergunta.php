<?php
/** @var int $id @var array $dados @var array $erros @var bool $tipoBloqueado @var int $totalRespostas */
use App\PerguntaRepositorio;
?>
<p class="voltar"><a href="<?= e(url('admin/perguntas.php')) ?>">Voltar para perguntas</a></p>
<h1><?= $id ? 'Editar pergunta' : 'Nova pergunta' ?></h1>

<form method="post" class="formulario formulario-largo" action="<?= e(url('admin/pergunta.php')) ?>" novalidate>
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="campo<?= isset($erros['texto']) ? ' campo-erro' : '' ?>">
        <label for="texto">Pergunta</label>
        <p class="dica" id="dica-texto">Escreva como você perguntaria pessoalmente. Frases curtas são mais fáceis de entender.</p>
        <textarea id="texto" name="texto" rows="3" maxlength="<?= PerguntaRepositorio::TEXTO_MAX ?>"
                  aria-describedby="dica-texto<?= isset($erros['texto']) ? ' erro-texto' : '' ?>" required><?= e($dados['texto']) ?></textarea>
        <?php if (isset($erros['texto'])): ?><p class="campo-mensagem" id="erro-texto"><?= e($erros['texto']) ?></p><?php endif; ?>
    </div>

    <fieldset class="campo<?= isset($erros['tipo']) ? ' campo-erro' : '' ?>">
        <legend>Como as pessoas respondem</legend>
        <?php if ($tipoBloqueado): ?>
            <p class="dica">Esta pergunta já tem <?= $totalRespostas ?> <?= $totalRespostas === 1 ? 'resposta' : 'respostas' ?>,
               por isso o tipo não pode mudar. Para outro tipo, crie uma pergunta nova.</p>
        <?php endif; ?>
        <div class="tipos">
            <label class="tipo">
                <input type="radio" name="tipo" value="escala" <?= $dados['tipo'] === 'escala' ? 'checked' : '' ?> <?= $tipoBloqueado ? 'disabled' : '' ?>>
                <span class="tipo-cartao">
                    <span class="tipo-amostra" aria-hidden="true"><?php for ($n = 1; $n <= 5; $n++) echo carinha($n); ?></span>
                    <strong>Carinhas de 1 a 5</strong>
                    <span>De "Muito ruim" a "Ótimo". Gera média e gráfico.</span>
                </span>
            </label>
            <label class="tipo">
                <input type="radio" name="tipo" value="texto" <?= $dados['tipo'] === 'texto' ? 'checked' : '' ?> <?= $tipoBloqueado ? 'disabled' : '' ?>>
                <span class="tipo-cartao">
                    <span class="tipo-amostra tipo-amostra-texto" aria-hidden="true"><span></span><span></span><span></span></span>
                    <strong>Texto livre</strong>
                    <span>Para elogios, sugestões e críticas.</span>
                </span>
            </label>
        </div>
        <?php if (isset($erros['tipo'])): ?><p class="campo-mensagem"><?= e($erros['tipo']) ?></p><?php endif; ?>
    </fieldset>

    <div class="campo">
        <label class="caixa">
            <input type="checkbox" name="obrigatoria" value="1" <?= $dados['obrigatoria'] ? 'checked' : '' ?>>
            <span>Resposta obrigatória</span>
        </label>
        <label class="caixa">
            <input type="checkbox" name="ativa" value="1" <?= $dados['ativa'] ? 'checked' : '' ?>>
            <span>Mostrar no questionário</span>
        </label>
    </div>

    <div class="formulario-acoes">
        <button type="submit" class="botao"><?= $id ? 'Salvar alterações' : 'Criar pergunta' ?></button>
        <a class="botao botao-secundario" href="<?= e(url('admin/perguntas.php')) ?>">Cancelar</a>
    </div>
</form>
