<?php /** @var bool $totem */ ?>
<section class="obrigado">
    <?= carinha(5, 'obrigado-rosto') ?>
    <h1>Obrigado pela sua avaliação!</h1>
    <p class="abertura-texto">Sua opinião ajuda a APAE a cuidar cada vez melhor de quem precisa.</p>
    <?php if ($totem): ?>
        <p class="obrigado-totem" aria-live="polite">
            Voltando ao início em <strong id="contagem">10</strong> segundos.
        </p>
        <a class="botao" href="<?= e(url('index.php?modo=totem')) ?>">Nova avaliação</a>
        <script>
            (function () {
                var restante = 10, alvo = document.getElementById('contagem');
                var timer = setInterval(function () {
                    restante -= 1;
                    if (alvo) alvo.textContent = String(restante);
                    if (restante <= 0) { clearInterval(timer); window.location.href = <?= json_encode(url('index.php?modo=totem')) ?>; }
                }, 1000);
            })();
        </script>
    <?php else: ?>
        <a class="botao botao-secundario" href="<?= e(url('index.php')) ?>">Fazer outra avaliação</a>
    <?php endif; ?>
</section>
