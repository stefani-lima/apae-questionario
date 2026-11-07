<?php /** @var string $mensagem */ ?>
<section class="obrigado">
    <?= carinha(2, 'obrigado-rosto') ?>
    <h1><?= e($titulo ?? 'Algo deu errado') ?></h1>
    <p class="abertura-texto"><?= e($mensagem) ?></p>
    <a class="botao botao-secundario" href="<?= e(url('index.php')) ?>">Voltar ao início</a>
</section>
