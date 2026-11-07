/* Gera o QR Code do cartaz (SVG) e a imagem PNG para download. */
(function () {
  'use strict';
  var alvo = document.getElementById('qr');
  if (!alvo || typeof qrcode !== 'function') return;

  var link = alvo.getAttribute('data-link');
  var qr = qrcode(0, 'M'); // tamanho automático, correção de erro média (resiste a pequenos amassados)
  qr.addData(link);
  qr.make();

  alvo.innerHTML = qr.createSvgTag({ cellSize: 8, margin: 2, scalable: true });

  var botao = document.getElementById('baixar-png');
  if (!botao) return;
  botao.addEventListener('click', function () {
    var modulos = qr.getModuleCount();
    var margem = 4;
    var celula = 20;
    var lado = (modulos + margem * 2) * celula;
    var canvas = document.createElement('canvas');
    canvas.width = canvas.height = lado;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, lado, lado);
    ctx.fillStyle = '#000000';
    for (var linha = 0; linha < modulos; linha++) {
      for (var coluna = 0; coluna < modulos; coluna++) {
        if (qr.isDark(linha, coluna)) {
          ctx.fillRect((coluna + margem) * celula, (linha + margem) * celula, celula, celula);
        }
      }
    }
    var a = document.createElement('a');
    a.download = 'qrcode-avaliacao-apae.png';
    a.href = canvas.toDataURL('image/png');
    document.body.appendChild(a);
    a.click();
    a.remove();
  });
})();
