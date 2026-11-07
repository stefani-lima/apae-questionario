<?php
/** @var string $conteudo */
$titulo = $titulo ?? 'Painel';
$ativo = $menu ?? '';
$usuario = usuario_logado();
$itensMenu = [
    'painel' => ['admin/index.php', 'Resultados'],
    'avaliacoes' => ['admin/avaliacoes.php', 'Avaliações'],
    'perguntas' => ['admin/perguntas.php', 'Perguntas'],
    'qrcode' => ['admin/qrcode.php', 'QR Code'],
    'usuarios' => ['admin/usuarios.php', 'Usuários'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title><?= e($titulo) ?> · Administração · <?= e(APP_NOME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
</head>
<body class="admin">
    <a class="pular" href="#conteudo">Pular para o conteúdo</a>
    <?php if ($usuario): ?>
        <header class="barra">
            <div class="barra-interna">
                <a class="barra-marca" href="<?= e(url('admin/index.php')) ?>">
                    <?= carinha(5, 'barra-rosto') ?>
                    <span>Avaliação <?= e(APP_NOME) ?></span>
                </a>
                <nav class="menu" aria-label="Menu principal">
                    <?php foreach ($itensMenu as $chave => [$link, $rotulo]): ?>
                        <a href="<?= e(url($link)) ?>" <?= $ativo === $chave ? 'aria-current="page"' : '' ?>><?= e($rotulo) ?></a>
                    <?php endforeach; ?>
                </nav>
                <div class="barra-usuario">
                    <a href="<?= e(url('admin/conta.php')) ?>" <?= $ativo === 'conta' ? 'aria-current="page"' : '' ?>
                       title="Minha conta"><?= e(explode(' ', $usuario['nome'])[0]) ?></a>
                    <form method="post" action="<?= e(url('admin/logout.php')) ?>">
                        <?= csrf_campo() ?>
                        <button type="submit" class="link-botao">Sair</button>
                    </form>
                </div>
            </div>
        </header>
    <?php endif; ?>

    <main id="conteudo" class="admin-conteudo">
        <?php foreach (flashes() as $mensagem): ?>
            <div class="aviso aviso-<?= e($mensagem['tipo']) ?>" role="status"><p><?= e($mensagem['mensagem']) ?></p></div>
        <?php endforeach; ?>
        <?= $conteudo ?>
    </main>
</body>
</html>
