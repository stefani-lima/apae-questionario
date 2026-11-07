<?php
declare(strict_types=1);

/**
 * Lê um arquivo .env simples (CHAVE=valor) e coloca os valores no ambiente.
 * Variáveis já definidas no servidor têm prioridade sobre o arquivo.
 */
function carregar_env(string $arquivo): void
{
    if (!is_file($arquivo)) {
        return;
    }
    foreach (file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || !str_contains($linha, '=')) {
            continue;
        }
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim(trim($valor), "\"'");
        if (getenv($chave) === false) {
            putenv("$chave=$valor");
            $_ENV[$chave] = $valor;
        }
    }
}

/** Retorna uma variável de ambiente ou o valor padrão. */
function env(string $chave, ?string $padrao = null): ?string
{
    $valor = getenv($chave);
    if ($valor === false || $valor === '') {
        $valor = $_ENV[$chave] ?? $_SERVER[$chave] ?? null;
    }
    return ($valor === null || $valor === '') ? $padrao : (string) $valor;
}

carregar_env(BASE_PATH . '/.env');

define('APP_NOME', env('APP_NOME', 'APAE de Rio do Sul'));
define('APP_PRODUCAO', env('APP_ENV', 'production') === 'production');

ini_set('display_errors', APP_PRODUCAO ? '0' : '1');
error_reporting(E_ALL);
