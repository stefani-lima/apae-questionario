<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

use PDO;

/** Model dos administradores (tabela `usuarios`). */
final class Usuario
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::conexao();
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE LOWER(email) = LOWER(:email)');
        $stmt->execute(['email' => trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function listar(): array
    {
        return $this->db->query(
            'SELECT id, nome, email, ativo, ultimo_acesso, criado_em FROM usuarios ORDER BY ativo DESC, nome'
        )->fetchAll();
    }

    public function total(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    public function emailEmUso(string $email): bool
    {
        return $this->buscarPorEmail($email) !== null;
    }

    public function criar(string $nome, string $email, string $senha): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash) RETURNING id'
        );
        $stmt->execute([
            'nome' => trim($nome),
            'email' => mb_strtolower(trim($email)),
            'hash' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function alterarSenha(int $id, string $novaSenha): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET senha_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => password_hash($novaSenha, PASSWORD_DEFAULT), 'id' => $id]);
    }

    /** Atualiza o hash quando o PHP passa a usar um algoritmo mais forte. */
    public function atualizarHashSeNecessario(array $usuario, string $senha): void
    {
        if (password_needs_rehash($usuario['senha_hash'], PASSWORD_DEFAULT)) {
            $this->alterarSenha((int) $usuario['id'], $senha);
        }
    }

    public function alternarAtivo(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET ativo = NOT ativo WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function registrarAcesso(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
