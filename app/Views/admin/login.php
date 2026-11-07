<?php /** @var string $email @var ?string $erro */ ?>
<section class="entrada">
    <div class="entrada-cabeca">
        <?= carinha(5, 'entrada-rosto') ?>
        <h1>Área da equipe</h1>
        <p>Avaliação de atendimento da <?= e(APP_NOME) ?></p>
    </div>

    <?php if ($erro): ?>
        <div class="aviso aviso-erro" role="alert"><p><?= e($erro) ?></p></div>
    <?php endif; ?>

    <form method="post" class="formulario" action="<?= e(url('admin/login')) ?>">
        <?= csrf_campo() ?>
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
        </div>
        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" autocomplete="current-password" required>
        </div>
        <button type="submit" class="botao botao-largo">Entrar</button>
    </form>
    <p class="entrada-rodape"><a href="<?= e(url('')) ?>">Ir para o questionário</a></p>
</section>
