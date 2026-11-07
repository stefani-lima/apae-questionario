<?php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Saída segura
// ---------------------------------------------------------------------------

/** Escapa texto para HTML (proteção contra XSS). Use em TODA saída. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------------------------------------------------------------------------
// URLs (funciona na raiz do domínio ou em subpasta, ex.: XAMPP htdocs/apae/public)
// Todas as requisições passam por public/index.php (front controller).
// ---------------------------------------------------------------------------

function base_url(): string
{
    static $base = null;
    if ($base === null) {
        $configurada = env('APP_BASE_PATH');
        $base = $configurada !== null
            ? rtrim($configurada, '/')
            : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    }
    return $base;
}

function url(string $caminho = ''): string
{
    return base_url() . '/' . ltrim($caminho, '/');
}

/** URL completa (com domínio) — usada no QR Code. */
function url_absoluta(string $caminho = ''): string
{
    $appUrl = env('APP_URL');
    if ($appUrl !== null) {
        return rtrim($appUrl, '/') . '/' . ltrim($caminho, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($https ? 'https' : 'http') . '://' . $host . url($caminho);
}

function asset(string $caminho): string
{
    $arquivo = BASE_PATH . '/public/assets/' . $caminho;
    $versao = is_file($arquivo) ? (string) filemtime($arquivo) : '1';
    return url('assets/' . $caminho) . '?v=' . $versao;
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

// ---------------------------------------------------------------------------
// Requisição
// ---------------------------------------------------------------------------

function metodo_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function ip_cliente(): string
{
    if (env('TRUST_PROXY') === '1' && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// ---------------------------------------------------------------------------
// Sessão, CSRF e mensagens
// ---------------------------------------------------------------------------

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('apae_sessao');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => base_url() . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $https,
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Interrompe a requisição se o token CSRF não conferir. */
function csrf_validar(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(400);
        App\Core\View::render('erro', [
            'titulo' => 'Formulário expirado',
            'mensagem' => 'A página ficou aberta por muito tempo. Volte, atualize a página e tente de novo.',
        ], 'publico');
        exit;
    }
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/** @return list<array{tipo:string,mensagem:string}> */
function flashes(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

// ---------------------------------------------------------------------------
// Formatação e domínio
// ---------------------------------------------------------------------------

const ROTULOS_NOTA = [1 => 'Muito ruim', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Ótimo'];
const ROTULOS_ORIGEM = ['qrcode' => 'QR Code', 'link' => 'Link', 'totem' => 'Tablet'];

function data_br(?string $data, bool $comHora = true): string
{
    if (!$data) {
        return '—';
    }
    return (new DateTimeImmutable($data))->format($comHora ? 'd/m/Y H:i' : 'd/m/Y');
}

function numero_br(float|int|null $valor, int $casas = 1): string
{
    return $valor === null ? '—' : number_format((float) $valor, $casas, ',', '.');
}

function resumir(string $texto, int $limite = 90): string
{
    $texto = trim(preg_replace('/\s+/', ' ', $texto) ?? '');
    return mb_strlen($texto) > $limite ? mb_substr($texto, 0, $limite - 1) . '…' : $texto;
}

/** Nota média (1–5) arredondada para escolher a carinha. */
function nota_arredondada(?float $media): int
{
    return $media === null ? 3 : max(1, min(5, (int) round($media)));
}

/**
 * Desenha uma carinha em SVG para a nota de 1 a 5.
 * A boca vai de triste (1) a muito feliz (5); as cores vêm do CSS (classe .rosto-N).
 */
function carinha(int $nota, string $classe = ''): string
{
    $bocas = [
        1 => 'M15 34 Q24 25 33 34',
        2 => 'M16 33 Q24 29 32 33',
        3 => 'M16 31 L32 31',
        4 => 'M16 29 Q24 35 32 29',
        5 => 'M14 27 Q24 40 34 27 Z',
    ];
    $sobrancelhas = $nota === 1
        ? '<path d="M12 16 Q15 13 19 14 M36 16 Q33 13 29 14" class="rosto-traco rosto-fino"/>'
        : '';
    $preenchimento = $nota === 5 ? ' class="rosto-traco rosto-boca-aberta"' : ' class="rosto-traco"';
    return sprintf(
        '<svg class="rosto rosto-%1$d %2$s" viewBox="0 0 48 48" aria-hidden="true" focusable="false">'
        . '<circle cx="24" cy="24" r="21" class="rosto-fundo"/>'
        . '%3$s<circle cx="17" cy="20" r="2.6" class="rosto-olho"/><circle cx="31" cy="20" r="2.6" class="rosto-olho"/>'
        . '<path d="%4$s"%5$s/></svg>',
        $nota,
        e($classe),
        $sobrancelhas,
        $bocas[$nota],
        $preenchimento
    );
}

// ---------------------------------------------------------------------------
// Erros inesperados (ex.: banco fora do ar)
// ---------------------------------------------------------------------------

set_exception_handler(function (Throwable $erro): void {
    error_log((string) $erro);
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $erro->getMessage() . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    $detalhe = APP_PRODUCAO ? '' : ' Detalhe técnico: ' . $erro->getMessage();
    App\Core\View::render('erro', [
        'titulo' => 'Não foi possível carregar a página',
        'mensagem' => 'O sistema está com uma instabilidade momentânea. Tente de novo em alguns minutos.' . $detalhe,
    ], 'publico');
});
