<?php /** @var array $usuarios @var array $eu @var array $novo @var array $erros */ ?>
<div class="cabecalho-pagina">
    <div>
        <h1>Usuários</h1>
        <p class="subtitulo">Pessoas da equipe que podem ver os resultados e editar as perguntas.</p>
    </div>
</div>

<div class="duas-colunas">
    <section class="bloco">
        <h2>Equipe com acesso</h2>
        <ul class="lista-usuarios">
            <?php foreach ($usuarios as $u): $id = (int) $u['id']; ?>
                <li class="<?= $u['ativo'] ? '' : 'item-inativo' ?>">
                    <div>
                        <p class="usuario-nome"><?= e($u['nome']) ?><?= $id === $eu['id'] ? ' (você)' : '' ?>
                            <?php if (!$u['ativo']): ?><span class="etiqueta">Desativado</span><?php endif; ?></p>
                        <p class="usuario-detalhe"><?= e($u['email']) ?></p>
                        <p class="usuario-detalhe">Último acesso: <?= e(data_br($u['ultimo_acesso'])) ?></p>
                    </div>
                    <?php if ($id !== $eu['id']): ?>
                        <form method="post" action="<?= e(url('admin/usuarios/' . $id . '/alternar')) ?>">
                            <?= csrf_campo() ?>
                            <button type="submit" class="botao botao-secundario botao-pequeno"><?= $u['ativo'] ? 'Desativar' : 'Reativar' ?></button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="bloco">
        <h2>Cadastrar pessoa</h2>
        <form method="post" class="formulario" action="<?= e(url('admin/usuarios')) ?>" novalidate>
            <?= csrf_campo() ?>
            <?php foreach ([
                'nome' => ['Nome', 'text', 'name', $novo['nome']],
                'email' => ['E-mail', 'email', 'off', $novo['email']],
                'senha' => ['Senha inicial (mín. 8 caracteres)', 'password', 'new-password', ''],
            ] as $campo => [$rotulo, $tipo, $auto, $valor]): ?>
                <div class="campo<?= isset($erros[$campo]) ? ' campo-erro' : '' ?>">
                    <label for="novo-<?= $campo ?>"><?= e($rotulo) ?></label>
                    <input type="<?= $tipo ?>" id="novo-<?= $campo ?>" name="<?= $campo ?>" value="<?= e($valor) ?>"
                           autocomplete="<?= $auto ?>" <?= isset($erros[$campo]) ? 'aria-describedby="erro-' . $campo . '"' : '' ?> required>
                    <?php if (isset($erros[$campo])): ?><p class="campo-mensagem" id="erro-<?= $campo ?>"><?= e($erros[$campo]) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="botao">Cadastrar</button>
        </form>
    </section>
</div>
