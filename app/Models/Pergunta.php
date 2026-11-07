<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

use PDO;

/** Model das perguntas do questionário (tabela `perguntas`). */
final class Pergunta
{
    public const TIPOS = ['escala' => 'Escala de 1 a 5 (carinhas)', 'texto' => 'Texto livre'];
    public const TEXTO_MAX = 300;

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::conexao();
    }

    /** Todas as perguntas, com a quantidade de respostas de cada uma. */
    public function listarTodas(): array
    {
        return $this->db->query(
            'SELECT p.*, (SELECT COUNT(*) FROM respostas r WHERE r.pergunta_id = p.id) AS total_respostas
               FROM perguntas p
              ORDER BY p.ordem, p.id'
        )->fetchAll();
    }

    public function listarAtivas(): array
    {
        return $this->db->query(
            'SELECT id, texto, tipo, obrigatoria FROM perguntas WHERE ativa ORDER BY ordem, id'
        )->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, (SELECT COUNT(*) FROM respostas r WHERE r.pergunta_id = p.id) AS total_respostas
               FROM perguntas p WHERE p.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Valida os dados do formulário de pergunta.
     * @return array<string,string> erros por campo (vazio = válido)
     */
    public static function validar(array $dados): array
    {
        $erros = [];
        $texto = trim((string) ($dados['texto'] ?? ''));
        if ($texto === '') {
            $erros['texto'] = 'Escreva o texto da pergunta.';
        } elseif (mb_strlen($texto) > self::TEXTO_MAX) {
            $erros['texto'] = 'Use no máximo ' . self::TEXTO_MAX . ' caracteres.';
        }
        if (!array_key_exists($dados['tipo'] ?? '', self::TIPOS)) {
            $erros['tipo'] = 'Escolha o tipo de resposta.';
        }
        return $erros;
    }

    public function criar(string $texto, string $tipo, bool $obrigatoria, bool $ativa): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO perguntas (texto, tipo, obrigatoria, ativa, ordem)
             VALUES (:texto, :tipo, :obrigatoria, :ativa, (SELECT COALESCE(MAX(ordem), 0) + 1 FROM perguntas))
             RETURNING id'
        );
        $stmt->bindValue('texto', trim($texto));
        $stmt->bindValue('tipo', $tipo);
        $stmt->bindValue('obrigatoria', $obrigatoria, PDO::PARAM_BOOL);
        $stmt->bindValue('ativa', $ativa, PDO::PARAM_BOOL);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function atualizar(int $id, string $texto, string $tipo, bool $obrigatoria, bool $ativa): void
    {
        $stmt = $this->db->prepare(
            'UPDATE perguntas
                SET texto = :texto, tipo = :tipo, obrigatoria = :obrigatoria, ativa = :ativa, atualizado_em = NOW()
              WHERE id = :id'
        );
        $stmt->bindValue('texto', trim($texto));
        $stmt->bindValue('tipo', $tipo);
        $stmt->bindValue('obrigatoria', $obrigatoria, PDO::PARAM_BOOL);
        $stmt->bindValue('ativa', $ativa, PDO::PARAM_BOOL);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function alternarAtiva(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE perguntas SET ativa = NOT ativa, atualizado_em = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** Troca a posição da pergunta com a vizinha de cima ('subir') ou de baixo ('descer'). */
    public function mover(int $id, string $direcao): void
    {
        $lista = array_column($this->db->query('SELECT id FROM perguntas ORDER BY ordem, id')->fetchAll(), 'id');
        $posicao = array_search($id, array_map('intval', $lista), true);
        if ($posicao === false) {
            return;
        }
        $alvo = $direcao === 'subir' ? $posicao - 1 : $posicao + 1;
        if ($alvo < 0 || $alvo >= count($lista)) {
            return;
        }
        [$lista[$posicao], $lista[$alvo]] = [$lista[$alvo], $lista[$posicao]];

        // Regrava a ordem de todas (1, 2, 3…) dentro de uma transação.
        $this->db->beginTransaction();
        $stmt = $this->db->prepare('UPDATE perguntas SET ordem = :ordem WHERE id = :id');
        foreach ($lista as $indice => $perguntaId) {
            $stmt->execute(['ordem' => $indice + 1, 'id' => $perguntaId]);
        }
        $this->db->commit();
    }

    /** Exclui somente se não houver respostas. Retorna false se não pôde excluir. */
    public function excluir(int $id): bool
    {
        $pergunta = $this->buscar($id);
        if ($pergunta === null || (int) $pergunta['total_respostas'] > 0) {
            return false;
        }
        $stmt = $this->db->prepare('DELETE FROM perguntas WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }
}
