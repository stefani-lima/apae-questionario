<?php
declare(strict_types=1);

use App\Database;
use App\UsuarioRepositorio;

const LOGIN_MAX_TENTATIVAS = 5;
const LOGIN_JANELA_MINUTOS = 15;

/** Dados do administrador logado, ou null. */
function usuario_logado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

/** Coloque no topo de toda página de /admin. */
function exigir_login(): array
{
    $usuario = usuario_logado();
    if ($usuario === null) {
        flash('info', 'Entre com seu e-mail e senha para continuar.');
        redirecionar('admin/login.php');
    }
    // Se o usuário foi desativado por outro administrador, encerra a sessão.
    $atual = (new UsuarioRepositorio())->buscar($usuario['id']);
    if (!$atual || !$atual['ativo']) {
        sair();
        iniciar_sessao();
        flash('erro', 'Seu acesso foi desativado. Fale com outro administrador.');
        redirecionar('admin/login.php');
    }
    return $usuario;
}

function login_bloqueado(string $ip): bool
{
    $db = Database::conexao();
    $stmt = $db->prepare(
        "SELECT COUNT(*) FROM tentativas_login
          WHERE ip = :ip AND criado_em > NOW() - (:minutos || ' minutes')::interval"
    );
    $stmt->execute(['ip' => $ip, 'minutos' => (string) LOGIN_JANELA_MINUTOS]);
    return (int) $stmt->fetchColumn() >= LOGIN_MAX_TENTATIVAS;
}

function registrar_falha_login(string $ip): void
{
    $db = Database::conexao();
    $db->prepare('INSERT INTO tentativas_login (ip) VALUES (:ip)')->execute(['ip' => $ip]);
    $db->exec("DELETE FROM tentativas_login WHERE criado_em < NOW() - INTERVAL '1 day'");
}

/**
 * Tenta autenticar. Retorna null em caso de sucesso ou a mensagem de erro.
 */
function tentar_login(string $email, string $senha): ?string
{
    $ip = ip_cliente();
    if (login_bloqueado($ip)) {
        return 'Muitas tentativas sem sucesso. Aguarde ' . LOGIN_JANELA_MINUTOS . ' minutos e tente de novo.';
    }

    $repositorio = new UsuarioRepositorio();
    $usuario = $repositorio->buscarPorEmail($email);

    // password_verify é chamado mesmo sem usuário para não revelar, pelo tempo de resposta, se o e-mail existe.
    $hash = $usuario['senha_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
    $senhaConfere = password_verify($senha, $hash);

    if (!$usuario || !$senhaConfere || !$usuario['ativo']) {
        registrar_falha_login($ip);
        return 'E-mail ou senha incorretos.';
    }

    $repositorio->atualizarHashSeNecessario($usuario, $senha);
    $repositorio->registrarAcesso((int) $usuario['id']);

    session_regenerate_id(true); // evita fixação de sessão
    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nome' => $usuario['nome'],
        'email' => $usuario['email'],
    ];
    return null;
}

function sair(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
