<?php
/** @var string $conteudo */
$titulo = $titulo ?? 'Avalie nosso atendimento';
$logo = null;
foreach (['logo.svg', 'logo.png', 'logo.jpg', 'logo.webp'] as $arquivoLogo) {
    if (is_file(BASE_PATH . '/public/assets/img/' . $arquivoLogo)) {
        $logo = asset('img/' . $arquivoLogo);
        break;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1D4F91">
    <meta name="robots" content="noindex">
    <title><?= e($titulo) ?> · <?= e(APP_NOME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&display=swap">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <?= $cabecalhoExtra ?? '' ?>
</head>
<body class="publico">
    <a class="pular" href="#conteudo">Pular para o conteúdo</a>
    <header class="marca">
        <div class="marca-interna">
            <?php if ($logo): ?>
                <img class="marca-logo" src="<?= e($logo) ?>" alt="<?= e(APP_NOME) ?>">
            <?php else: ?>
                <span class="marca-flor" aria-hidden="true">
                    <svg viewBox="0 0 40 40"><g class="flor"><circle cx="20" cy="9" r="7"/><circle cx="30.5" cy="16.5" r="7"/><circle cx="26.5" cy="29" r="7"/><circle cx="13.5" cy="29" r="7"/><circle cx="9.5" cy="16.5" r="7"/></g><circle cx="20" cy="20" r="5.5" class="miolo"/></svg>
                </span>
                <span class="marca-nome"><?= e(APP_NOME) ?></span>
            <?php endif; ?>
        </div>
    </header>
    <main id="conteudo" class="publico-conteudo">
        <?= $conteudo ?>
    </main>
    <footer class="rodape-publico">
        <p>Avaliação anônima. Não pedimos seu nome nem seus dados.</p>
    </footer>
</body>
</html>
