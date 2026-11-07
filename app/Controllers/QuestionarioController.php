<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Avaliacao;
use App\Models\Pergunta;

/**
 * Questionário público (acessado pelo QR Code da recepção).
 * Padrão POST/Redirect/GET: depois de gravar, redireciona para /obrigado.
 */
final class QuestionarioController extends Controller
{
    public function exibir(): void
    {
        // ?modo=totem: tablet na recepção, volta ao início após cada resposta.
        if (($_GET['modo'] ?? '') === 'totem') {
            $_SESSION['totem'] = true;
        }
        $this->mostrarFormulario((new Pergunta())->listarAtivas());
    }

    public function enviar(): void
    {
        csrf_validar();

        // Campo invisível preenchido = robô. Finge que deu certo e descarta.
        if (!empty($_POST['site'])) {
            $this->redirecionar('obrigado');
        }

        $perguntas = (new Pergunta())->listarAtivas();
        [$respostas, $erros, $valores] = Avaliacao::validarRespostas($perguntas, $_POST['resp'] ?? []);

        if ($erros) {
            $this->mostrarFormulario($perguntas, $erros, $valores);
            return;
        }

        // Token de uso único: um segundo clique em "Enviar" não grava de novo.
        $token = (string) ($_POST['envio_token'] ?? '');
        if ($token !== '' && isset($_SESSION['envio_tokens'][$token])) {
            unset($_SESSION['envio_tokens'][$token]);
            (new Avaliacao())->registrar($respostas, $this->origem());
        }
        $this->redirecionar('obrigado');
    }

    public function obrigado(): void
    {
        $this->view('questionario/obrigado', [
            'titulo' => 'Obrigado!',
            'totem' => !empty($_SESSION['totem']),
        ], 'publico');
    }

    private function mostrarFormulario(array $perguntas, array $erros = [], array $valores = []): void
    {
        // Novo token de envio (guarda só os 5 mais recentes, para abas abertas).
        $token = bin2hex(random_bytes(16));
        $_SESSION['envio_tokens'] = array_slice(($_SESSION['envio_tokens'] ?? []) + [$token => time()], -5, null, true);

        $origem = $this->origem();
        $this->view('questionario/formulario', [
            'perguntas' => $perguntas,
            'erros' => $erros,
            'valores' => $valores,
            'tokenEnvio' => $token,
            'origem' => $origem === 'totem' ? 'link' : $origem,
        ], 'publico');
    }

    /** De onde veio a pessoa: cartaz (qrcode), tablet (totem) ou link comum. */
    private function origem(): string
    {
        if (!empty($_SESSION['totem'])) {
            return 'totem';
        }
        return (($_GET['origem'] ?? $_POST['origem'] ?? '') === 'qrcode') ? 'qrcode' : 'link';
    }
}
