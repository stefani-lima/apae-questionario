<?php
declare(strict_types=1);

/*
 * US1 — Questionário público (acessado pelo QR Code da recepção).
 * Padrão POST/Redirect/GET: depois de gravar, redireciona para obrigado.php.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

use App\AvaliacaoRepositorio;
use App\PerguntaRepositorio;

// Origem do acesso: ?origem=qrcode (cartaz) ou ?modo=totem (tablet na recepção).
if (($_GET['modo'] ?? '') === 'totem') {
    $_SESSION['totem'] = true;
}
$origem = !empty($_SESSION['totem']) ? 'totem' : ((($_GET['origem'] ?? $_POST['origem'] ?? '') === 'qrcode') ? 'qrcode' : 'link');

$perguntas = (new PerguntaRepositorio())->listarAtivas();
$erros = [];
$valores = [];

if (metodo_post()) {
    csrf_validar();

    // Campo invisível preenchido = robô. Finge que deu certo e descarta.
    if (!empty($_POST['site'])) {
        redirecionar('obrigado.php');
    }

    [$respostas, $erros, $valores] = AvaliacaoRepositorio::validarRespostas($perguntas, $_POST['resp'] ?? []);

    if (!$erros) {
        // Token de uso único: um segundo clique em "Enviar" não grava de novo.
        $token = (string) ($_POST['envio_token'] ?? '');
        $tokensValidos = $_SESSION['envio_tokens'] ?? [];
        if ($token !== '' && isset($tokensValidos[$token])) {
            unset($_SESSION['envio_tokens'][$token]);
            (new AvaliacaoRepositorio())->registrar($respostas, $origem);
        }
        redirecionar('obrigado.php');
    }
}

// Gera um novo token de envio (mantém só os 5 mais recentes para abas abertas).
$tokenEnvio = bin2hex(random_bytes(16));
$_SESSION['envio_tokens'] = array_slice(($_SESSION['envio_tokens'] ?? []) + [$tokenEnvio => time()], -5, null, true);

render('publico/questionario', [
    'perguntas' => $perguntas,
    'erros' => $erros,
    'valores' => $valores,
    'tokenEnvio' => $tokenEnvio,
    'origem' => $origem === 'totem' ? 'link' : $origem,
], 'publico');
