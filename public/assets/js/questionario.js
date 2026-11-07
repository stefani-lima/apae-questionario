/*
 * Melhorias do questionário no navegador. Tudo funciona sem JavaScript;
 * aqui só antecipamos a validação (o servidor sempre valida de novo).
 */
(function () {
  'use strict';
  var form = document.getElementById('questionario');
  if (!form) return;

  var resumo = document.getElementById('resumo-erros');
  if (resumo) resumo.focus();

  function mostrarErro(fieldset, mensagem) {
    var p = fieldset.querySelector('.pergunta-erro');
    fieldset.classList.add('pergunta-com-erro');
    fieldset.setAttribute('aria-describedby', p.id);
    p.textContent = mensagem;
    p.hidden = false;
  }

  function limparErro(fieldset) {
    var p = fieldset.querySelector('.pergunta-erro');
    fieldset.classList.remove('pergunta-com-erro');
    fieldset.removeAttribute('aria-describedby');
    p.hidden = true;
  }

  function respondida(fieldset) {
    if (fieldset.dataset.tipo === 'escala') {
      return !!fieldset.querySelector('input[type=radio]:checked');
    }
    var area = fieldset.querySelector('textarea');
    return area && area.value.trim() !== '';
  }

  // Ao escolher uma carinha, some a mensagem de erro daquela pergunta.
  form.addEventListener('change', function (ev) {
    var fieldset = ev.target.closest('.pergunta');
    if (fieldset && respondida(fieldset)) limparErro(fieldset);
  });

  // Contador de caracteres do texto livre.
  form.querySelectorAll('textarea[data-contador]').forEach(function (area) {
    var saida = document.getElementById(area.dataset.contador);
    var max = parseInt(area.getAttribute('maxlength'), 10);
    function atualizar() {
      var restam = max - area.value.length;
      saida.textContent = restam < 200 ? 'Restam ' + restam + ' caracteres.' : '';
    }
    area.addEventListener('input', function () {
      atualizar();
      var fieldset = area.closest('.pergunta');
      if (respondida(fieldset)) limparErro(fieldset);
    });
    atualizar();
  });

  form.addEventListener('submit', function (ev) {
    var primeira = null;
    var algumaResposta = false;
    form.querySelectorAll('.pergunta').forEach(function (fieldset) {
      var ok = respondida(fieldset);
      if (ok) algumaResposta = true;
      if (!ok && fieldset.dataset.obrigatoria === '1') {
        mostrarErro(fieldset, fieldset.dataset.tipo === 'escala'
          ? 'Escolha uma das carinhas para responder.'
          : 'Escreva sua resposta.');
        primeira = primeira || fieldset;
      }
    });

    if (primeira || !algumaResposta) {
      ev.preventDefault();
      var alvo = primeira || form.querySelector('.pergunta');
      if (!primeira) mostrarErro(alvo, 'Responda pelo menos uma pergunta antes de enviar.');
      alvo.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
      var foco = alvo.querySelector('input, textarea');
      if (foco) foco.focus({ preventScroll: true });
      return;
    }

    // Evita envio duplicado por toque duplo.
    var botao = form.querySelector('.botao-enviar');
    botao.disabled = true;
    botao.textContent = botao.dataset.enviando;
  });
})();
