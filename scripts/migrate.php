<?php
declare(strict_types=1);

/*
 * Instalação / atualização do banco.
 *   php scripts/migrate.php
 *
 * 1. Cria as tabelas (database/schema.sql, idempotente).
 * 2. Insere as perguntas padrão se não houver nenhuma (database/seed.sql).
 * 3. Cria o primeiro administrador se não houver usuários.
 *    Usa ADMIN_NOME / ADMIN_EMAIL / ADMIN_SENHA do ambiente; sem elas, pergunta no terminal.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Database;
use App\UsuarioRepositorio;

$db = null;
for ($tentativa = 1; $tentativa <= 10; $tentativa++) {
    try {
        if (Database::criarBancoSeNaoExistir()) {
            echo "Banco de dados criado.\n";
        }
        $db = Database::conexao();
        break;
    } catch (PDOException $erro) {
        if (preg_match('/password authentication failed|senha falhou|autentica/ui', $erro->getMessage())) {
            fwrite(STDERR, "Usuário ou senha do PostgreSQL incorretos. Confira DB_USER e DB_PASSWORD no arquivo .env.\n");
            exit(1);
        }
        fwrite(STDERR, "Banco indisponível (tentativa $tentativa/10): {$erro->getMessage()}\n");
        sleep(3);
    }
}
if ($db === null) {
    fwrite(STDERR, "Não foi possível conectar ao banco.\n");
    exit(1);
}

$db->exec(file_get_contents(BASE_PATH . '/database/schema.sql'));
echo "Tabelas verificadas.\n";

if ((int) $db->query('SELECT COUNT(*) FROM perguntas')->fetchColumn() === 0) {
    $db->exec(file_get_contents(BASE_PATH . '/database/seed.sql'));
    echo "Perguntas padrão inseridas.\n";
}

$usuarios = new UsuarioRepositorio($db);
if ($usuarios->total() === 0) {
    $nome = env('ADMIN_NOME', 'Administrador');
    $email = env('ADMIN_EMAIL');
    $senha = env('ADMIN_SENHA');

    if (($email === null || $senha === null) && stream_isatty(STDIN)) {
        $email ??= trim((string) readline('E-mail do administrador: '));
        $senha ??= trim((string) readline('Senha (mín. 8 caracteres): '));
    }

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$senha || mb_strlen($senha) < 8) {
        echo "Aviso: Nenhum administrador criado. Defina ADMIN_EMAIL e ADMIN_SENHA (mín. 8 caracteres) e rode novamente.\n";
    } else {
        $usuarios->criar($nome, $email, $senha);
        echo "Administrador criado: $email\n";
    }
}

echo "Pronto.\n";
