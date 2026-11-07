<?php /** @var string $linkQr @var string $linkTotem @var bool $urlDefinida */ ?>
<div class="cabecalho-pagina sem-impressao">
    <div>
        <h1>QR Code da recepção</h1>
        <p class="subtitulo">Imprima o cartaz e deixe em um lugar visível. Quem ler o código cai direto no questionário.</p>
    </div>
    <div class="acoes-pagina">
        <button type="button" class="botao botao-secundario" id="baixar-png">Baixar imagem</button>
        <button type="button" class="botao" onclick="window.print()">Imprimir cartaz</button>
    </div>
</div>

<?php if (!$urlDefinida): ?>
    <div class="aviso aviso-info sem-impressao" role="status">
        <p>O código abaixo usa o endereço que você está acessando agora. Se o site for publicado em outro endereço,
           defina a variável <code>APP_URL</code> para o QR Code apontar sempre para o endereço oficial.</p>
    </div>
<?php endif; ?>

<div class="cartaz-moldura">
    <article class="cartaz" aria-label="Prévia do cartaz">
        <p class="cartaz-entidade"><?= e(APP_NOME) ?></p>
        <h2 class="cartaz-titulo">Conte pra gente como foi a sua visita</h2>
        <div class="cartaz-qr" id="qr" data-link="<?= e($linkQr) ?>" role="img" aria-label="QR Code do questionário"></div>
        <ol class="cartaz-passos">
            <li>Abra a câmera do celular</li>
            <li>Aponte para o código</li>
            <li>Toque no link e responda</li>
        </ol>
        <p class="cartaz-rodape">
            <span class="cartaz-rostos" aria-hidden="true"><?php for ($n = 1; $n <= 5; $n++) echo carinha($n); ?></span>
            É anônimo e leva menos de 2 minutos.
        </p>
    </article>
</div>

<section class="bloco sem-impressao">
    <h2>Links</h2>
    <dl class="links">
        <div>
            <dt>Link do QR Code</dt>
            <dd><code><?= e($linkQr) ?></code></dd>
        </div>
        <div>
            <dt>Link para tablet na recepção (modo totem)</dt>
            <dd><code><?= e($linkTotem) ?></code>
                <p class="dica">Depois de cada resposta, a tela volta sozinha ao início em 10 segundos.</p></dd>
        </div>
    </dl>
</section>

<script src="<?= asset('vendor/qrcode.js') ?>"></script>
<script src="<?= asset('js/qrcode-pagina.js') ?>"></script>
