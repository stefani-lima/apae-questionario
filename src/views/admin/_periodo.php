<?php
/** @var array $periodo  @var string $acao página que recebe o filtro */
use App\AvaliacaoRepositorio;
?>
<form method="get" action="<?= e(url($acao)) ?>" class="periodo" aria-label="Filtrar por período">
    <div class="periodo-opcoes" role="group" aria-label="Período">
        <?php foreach (AvaliacaoRepositorio::PERIODOS as $chave => $rotulo): ?>
            <label class="pilula">
                <input type="radio" name="periodo" value="<?= e($chave) ?>" <?= $periodo['chave'] === $chave ? 'checked' : '' ?>
                       onchange="this.form.dataset.mudou='1'; if (this.value !== 'personalizado') this.form.submit(); else this.form.classList.add('periodo-aberto');">
                <span><?= e($rotulo) ?></span>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="periodo-datas<?= $periodo['chave'] === 'personalizado' ? ' visivel' : '' ?>">
        <label>De <input type="date" name="inicio" value="<?= e($periodo['inicio'] ?? '') ?>"></label>
        <label>até <input type="date" name="fim" value="<?= e($periodo['fim'] ?? '') ?>"></label>
        <button type="submit" class="botao botao-pequeno">Aplicar</button>
    </div>
    <noscript><button type="submit" class="botao botao-pequeno">Filtrar</button></noscript>
</form>
