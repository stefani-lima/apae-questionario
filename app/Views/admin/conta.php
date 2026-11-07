<?php /** @var array $eu @var array $erros */ ?>
<h1>Minha conta</h1>
<p class="subtitulo"><?= e($eu['nome']) ?>, <?= e($eu['email']) ?></p>

<section class="bloco bloco-estreito">
    <h2>Trocar senha</h2>
    <form method="post" class="formulario" action="<?= e(url('admin/conta')) ?>" novalidate>
        <?= csrf_campo() ?>
        <?php foreach ([
            'senha_atual' => ['Senha atual', 'current-password'],
            'senha_nova' => ['Nova senha (mín. 8 caracteres)', 'new-password'],
            'senha_confirmacao' => ['Repita a nova senha', 'new-password'],
        ] as $campo => [$rotulo, $auto]): ?>
            <div class="campo<?= isset($erros[$campo]) ? ' campo-erro' : '' ?>">
                <label for="<?= $campo ?>"><?= e($rotulo) ?></label>
                <input type="password" id="<?= $campo ?>" name="<?= $campo ?>" autocomplete="<?= $auto ?>"
                       <?= isset($erros[$campo]) ? 'aria-describedby="erro-' . $campo . '"' : '' ?> required>
                <?php if (isset($erros[$campo])): ?><p class="campo-mensagem" id="erro-<?= $campo ?>"><?= e($erros[$campo]) ?></p><?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="botao">Salvar nova senha</button>
    </form>
</section>
