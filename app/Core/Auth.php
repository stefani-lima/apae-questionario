<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

/** Autenticação dos administradores (sessão + limite de tentativas). */
final class Auth
{
    private const MAX_TENTATIVAS = 5;
    private const JANELA_MINUTOS = 15;

    /** Dados do administrador logado, ou null. */
    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function exigir(): array
    {
        $usuario = self::usuario();
        if ($usuario === null) {
            flash('info', 'Entre com seu e-mail e senha para continuar.');
            redirecionar('admin/login');
        }
        // Se o usuário foi desativado por outro administrador, encerra a sessão.
        $atual = (new Usuario())->buscar($usuario['id']);
        if (!$atual || !$atual['ativo']) {
            self::sair();
            iniciar_sessao();
            flash('erro', 'Seu acesso foi desativado. Fale com outro administrador.');
            redirecionar('admin/login');
        }
        return $usuario;
    }

    /** Tenta autenticar. Retorna null em caso de sucesso ou a mensagem de erro. */
    public static function tentar(string $email, string $senha): ?string
    {
        $ip = ip_cliente();
        if (self::bloqueado($ip)) {
            return 'Muitas tentativas sem sucesso. Aguarde ' . self::JANELA_MINUTOS . ' minutos e tente de novo.';
        }

        $usuarios = new Usuario();
        $usuario = $usuarios->buscarPorEmail($email);

        // password_verify roda mesmo sem usuário, para o tempo de resposta não revelar se o e-mail existe.
        $hash = $usuario['senha_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
        $senhaConfere = password_verify($senha, $hash);

        if (!$usuario || !$senhaConfere || !$usuario['ativo']) {
            self::registrarFalha($ip);
            return 'E-mail ou senha incorretos.';
        }

        $usuarios->atualizarHashSeNecessario($usuario, $senha);
        $usuarios->registrarAcesso((int) $usuario['id']);

        session_regenerate_id(true); // evita fixação de sessão
        $_SESSION['usuario'] = [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
        ];
        return null;
    }

    public static function sair(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    private static function bloqueado(string $ip): bool
    {
        $stmt = Database::conexao()->prepare(
            "SELECT COUNT(*) FROM tentativas_login
              WHERE ip = :ip AND criado_em > NOW() - (:minutos || ' minutes')::interval"
        );
        $stmt->execute(['ip' => $ip, 'minutos' => (string) self::JANELA_MINUTOS]);
        return (int) $stmt->fetchColumn() >= self::MAX_TENTATIVAS;
    }

    private static function registrarFalha(string $ip): void
    {
        $db = Database::conexao();
        $db->prepare('INSERT INTO tentativas_login (ip) VALUES (:ip)')->execute(['ip' => $ip]);
        $db->exec("DELETE FROM tentativas_login WHERE criado_em < NOW() - INTERVAL '1 day'");
    }
}
