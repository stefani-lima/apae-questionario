<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

/**
 * Conexão única (singleton) com o PostgreSQL via PDO.
 * Aceita DATABASE_URL (padrão de Render, Neon, Supabase…) ou as variáveis DB_*.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function conexao(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $p = self::parametros();
        self::$pdo = self::conectar($p, $p['banco']);
        self::$pdo->exec("SET TIME ZONE 'America/Sao_Paulo'");
        return self::$pdo;
    }

    /**
     * Cria o banco configurado, caso ainda não exista (usado pelo scripts/migrate.php).
     * Retorna true se criou.
     */
    public static function criarBancoSeNaoExistir(): bool
    {
        $p = self::parametros();
        try {
            self::conectar($p, $p['banco']);
            return false;
        } catch (PDOException $erro) {
            // Mensagem em inglês ou português, conforme o idioma do PostgreSQL instalado.
            if (!preg_match('/does not exist|não existe/u', $erro->getMessage())) {
                throw $erro;
            }
        }
        $admin = self::conectar($p, 'postgres');
        $admin->exec('CREATE DATABASE "' . str_replace('"', '""', $p['banco']) . '"');
        return true;
    }

    /** @return array{host:string,porta:string,banco:string,usuario:string,senha:string,extras:array<string,string>} */
    private static function parametros(): array
    {
        $url = env('DATABASE_URL');
        $extras = [];
        if ($url !== null) {
            $partes = parse_url($url);
            parse_str($partes['query'] ?? '', $query);
            // Somente parâmetros conhecidos do libpq são repassados.
            foreach (['sslmode', 'channel_binding', 'options'] as $parametro) {
                if (!empty($query[$parametro])) {
                    $extras[$parametro] = $query[$parametro];
                }
            }
            return [
                'host' => $partes['host'] ?? 'localhost',
                'porta' => (string) ($partes['port'] ?? 5432),
                'banco' => ltrim($partes['path'] ?? '', '/'),
                'usuario' => urldecode($partes['user'] ?? ''),
                'senha' => urldecode($partes['pass'] ?? ''),
                'extras' => $extras,
            ];
        }
        if (env('DB_SSLMODE')) {
            $extras['sslmode'] = env('DB_SSLMODE');
        }
        return [
            'host' => env('DB_HOST', 'localhost'),
            'porta' => env('DB_PORT', '5432'),
            'banco' => env('DB_NAME', 'apae'),
            'usuario' => env('DB_USER', 'postgres'),
            'senha' => env('DB_PASSWORD', ''),
            'extras' => $extras,
        ];
    }

    private static function conectar(array $p, string $banco): PDO
    {
        $dsn = "pgsql:host={$p['host']};port={$p['porta']};dbname={$banco}";
        foreach ($p['extras'] as $chave => $valor) {
            $dsn .= ";{$chave}={$valor}";
        }
        return new PDO($dsn, $p['usuario'], $p['senha'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
