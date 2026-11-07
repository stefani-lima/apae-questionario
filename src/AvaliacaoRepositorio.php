<?php
declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use PDO;

/**
 * Avaliações = tabelas `envios` + `respostas`.
 * Concentra a gravação do questionário e as consultas do painel.
 */
final class AvaliacaoRepositorio
{
    public const TEXTO_MAX = 1000;
    public const POR_PAGINA = 20;
    public const PERIODOS = ['7d' => 'Últimos 7 dias', '30d' => 'Últimos 30 dias', 'tudo' => 'Tudo', 'personalizado' => 'Escolher datas'];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::conexao();
    }

    // ------------------------------------------------------------------
    // Questionário público
    // ------------------------------------------------------------------

    /**
     * Valida as respostas enviadas pelo formulário.
     *
     * @param array $perguntas perguntas ativas (id, tipo, obrigatoria)
     * @param mixed $entrada   $_POST['resp'] no formato [pergunta_id => valor]
     * @return array{0: list<array{pergunta_id:int,nota:?int,texto:?string}>, 1: array<int|string,string>, 2: array<int,string>}
     *         [respostas válidas, erros por pergunta, valores digitados para reexibir]
     */
    public static function validarRespostas(array $perguntas, mixed $entrada): array
    {
        $entrada = is_array($entrada) ? $entrada : [];
        $respostas = [];
        $erros = [];
        $valores = [];

        foreach ($perguntas as $pergunta) {
            $id = (int) $pergunta['id'];
            $bruto = $entrada[$id] ?? '';
            $bruto = is_string($bruto) ? trim($bruto) : '';
            $valores[$id] = $bruto;

            if ($bruto === '') {
                if ($pergunta['obrigatoria']) {
                    $erros[$id] = $pergunta['tipo'] === 'escala'
                        ? 'Escolha uma das carinhas para responder.'
                        : 'Escreva sua resposta.';
                }
                continue;
            }

            if ($pergunta['tipo'] === 'escala') {
                $nota = filter_var($bruto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
                if ($nota === false) {
                    $erros[$id] = 'Escolha uma das carinhas para responder.';
                    $valores[$id] = '';
                    continue;
                }
                $respostas[] = ['pergunta_id' => $id, 'nota' => $nota, 'texto' => null];
            } else {
                if (mb_strlen($bruto) > self::TEXTO_MAX) {
                    $erros[$id] = 'Seu texto passou de ' . self::TEXTO_MAX . ' caracteres. Resuma um pouco, por favor.';
                    continue;
                }
                $respostas[] = ['pergunta_id' => $id, 'nota' => null, 'texto' => $bruto];
            }
        }

        if (!$erros && !$respostas) {
            $erros['geral'] = 'Responda pelo menos uma pergunta antes de enviar.';
        }

        return [$respostas, $erros, $valores];
    }

    /** Grava a avaliação inteira numa transação (tudo ou nada). */
    public function registrar(array $respostas, string $origem): int
    {
        $origem = array_key_exists($origem, ROTULOS_ORIGEM) ? $origem : 'link';
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('INSERT INTO envios (origem) VALUES (:origem) RETURNING id');
            $stmt->execute(['origem' => $origem]);
            $envioId = (int) $stmt->fetchColumn();

            $stmt = $this->db->prepare(
                'INSERT INTO respostas (envio_id, pergunta_id, nota, texto) VALUES (:envio, :pergunta, :nota, :texto)'
            );
            foreach ($respostas as $resposta) {
                $stmt->bindValue('envio', $envioId, PDO::PARAM_INT);
                $stmt->bindValue('pergunta', $resposta['pergunta_id'], PDO::PARAM_INT);
                $stmt->bindValue('nota', $resposta['nota'], $resposta['nota'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmt->bindValue('texto', $resposta['texto'], $resposta['texto'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                $stmt->execute();
            }
            $this->db->commit();
            return $envioId;
        } catch (\Throwable $erro) {
            $this->db->rollBack();
            throw $erro;
        }
    }

    // ------------------------------------------------------------------
    // Filtro de período
    // ------------------------------------------------------------------

    /** @return array{chave:string,inicio:?string,fim:?string} datas no formato Y-m-d */
    public static function periodoDaRequisicao(array $get): array
    {
        $chave = array_key_exists($get['periodo'] ?? '', self::PERIODOS) ? $get['periodo'] : '30d';
        $hoje = new DateTimeImmutable('today');
        return match ($chave) {
            '7d' => ['chave' => $chave, 'inicio' => $hoje->modify('-6 days')->format('Y-m-d'), 'fim' => $hoje->format('Y-m-d')],
            '30d' => ['chave' => $chave, 'inicio' => $hoje->modify('-29 days')->format('Y-m-d'), 'fim' => $hoje->format('Y-m-d')],
            'tudo' => ['chave' => $chave, 'inicio' => null, 'fim' => null],
            default => [
                'chave' => $chave,
                'inicio' => self::dataValida($get['inicio'] ?? null),
                'fim' => self::dataValida($get['fim'] ?? null),
            ],
        };
    }

    private static function dataValida(mixed $valor): ?string
    {
        if (!is_string($valor)) {
            return null;
        }
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $data && $data->format('Y-m-d') === $valor ? $valor : null;
    }

    /** Monta o trecho SQL do filtro e os parâmetros. */
    private function filtro(array $periodo, string $coluna = 'e.criado_em'): array
    {
        $condicoes = [];
        $parametros = [];
        if (!empty($periodo['inicio'])) {
            $condicoes[] = "$coluna >= CAST(:inicio AS date)";
            $parametros['inicio'] = $periodo['inicio'];
        }
        if (!empty($periodo['fim'])) {
            $condicoes[] = "$coluna < CAST(:fim AS date) + 1";
            $parametros['fim'] = $periodo['fim'];
        }
        return [$condicoes ? implode(' AND ', $condicoes) : 'TRUE', $parametros];
    }

    // ------------------------------------------------------------------
    // Painel
    // ------------------------------------------------------------------

    /** Números gerais do período. */
    public function indicadores(array $periodo): array
    {
        [$where, $params] = $this->filtro($periodo);
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT e.id)                                   AS avaliacoes,
                    ROUND(AVG(r.nota), 2)                                  AS media,
                    COUNT(r.nota)                                          AS notas,
                    COUNT(r.nota) FILTER (WHERE r.nota >= 4)               AS positivas,
                    COUNT(r.texto) FILTER (WHERE BTRIM(r.texto) <> '')     AS comentarios
               FROM envios e
               LEFT JOIN respostas r ON r.envio_id = e.id
              WHERE $where"
        );
        $stmt->execute($params);
        $linha = $stmt->fetch();
        $notas = (int) $linha['notas'];
        return [
            'avaliacoes' => (int) $linha['avaliacoes'],
            'media' => $linha['media'] === null ? null : (float) $linha['media'],
            'positivas' => $notas > 0 ? round((int) $linha['positivas'] * 100 / $notas) : null,
            'comentarios' => (int) $linha['comentarios'],
        ];
    }

    /** Média e distribuição de notas por pergunta de escala. */
    public function resumoPorPergunta(array $periodo): array
    {
        [$where, $params] = $this->filtro($periodo);
        $stmt = $this->db->prepare(
            "SELECT p.id, p.texto, p.ativa,
                    COUNT(r.nota)          AS total,
                    ROUND(AVG(r.nota), 2)  AS media,
                    COUNT(r.nota) FILTER (WHERE r.nota = 1) AS n1,
                    COUNT(r.nota) FILTER (WHERE r.nota = 2) AS n2,
                    COUNT(r.nota) FILTER (WHERE r.nota = 3) AS n3,
                    COUNT(r.nota) FILTER (WHERE r.nota = 4) AS n4,
                    COUNT(r.nota) FILTER (WHERE r.nota = 5) AS n5
               FROM perguntas p
               LEFT JOIN (respostas r JOIN envios e ON e.id = r.envio_id AND $where)
                      ON r.pergunta_id = p.id
              WHERE p.tipo = 'escala'
              GROUP BY p.id
             HAVING p.ativa OR COUNT(r.nota) > 0
              ORDER BY p.ordem, p.id"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Comentários (respostas de texto) mais recentes do período. */
    public function comentariosRecentes(array $periodo, int $limite = 5): array
    {
        [$where, $params] = $this->filtro($periodo);
        $stmt = $this->db->prepare(
            "SELECT e.id AS envio_id, e.criado_em, r.texto, p.texto AS pergunta
               FROM respostas r
               JOIN envios e    ON e.id = r.envio_id
               JOIN perguntas p ON p.id = r.pergunta_id
              WHERE r.texto IS NOT NULL AND BTRIM(r.texto) <> '' AND $where
              ORDER BY e.criado_em DESC
              LIMIT $limite"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // Lista e detalhe
    // ------------------------------------------------------------------

    public function contar(array $periodo): int
    {
        [$where, $params] = $this->filtro($periodo);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM envios e WHERE $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function listar(array $periodo, int $pagina): array
    {
        [$where, $params] = $this->filtro($periodo);
        $deslocamento = max(0, ($pagina - 1) * self::POR_PAGINA);
        $stmt = $this->db->prepare(
            "SELECT e.id, e.origem, e.criado_em,
                    ROUND(AVG(r.nota), 1) AS media,
                    STRING_AGG(NULLIF(BTRIM(r.texto), ''), ' / ') AS comentario
               FROM envios e
               LEFT JOIN respostas r ON r.envio_id = e.id
              WHERE $where
              GROUP BY e.id
              ORDER BY e.criado_em DESC, e.id DESC
              LIMIT " . self::POR_PAGINA . " OFFSET $deslocamento"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM envios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $envio = $stmt->fetch();
        if (!$envio) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT p.texto AS pergunta, p.tipo, r.nota, r.texto
               FROM respostas r JOIN perguntas p ON p.id = r.pergunta_id
              WHERE r.envio_id = :id
              ORDER BY p.ordem, p.id'
        );
        $stmt->execute(['id' => $id]);
        $envio['respostas'] = $stmt->fetchAll();
        return $envio;
    }

    public function excluir(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM envios WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    // ------------------------------------------------------------------
    // Exportação
    // ------------------------------------------------------------------

    /**
     * Uma linha por avaliação, uma coluna por pergunta.
     * @return array{0: list<string>, 1: list<list<string>>} [cabeçalho, linhas]
     */
    public function planilha(array $periodo): array
    {
        $perguntas = $this->db->query(
            'SELECT DISTINCT p.id, p.texto, p.ordem FROM perguntas p
              WHERE p.ativa OR EXISTS (SELECT 1 FROM respostas r WHERE r.pergunta_id = p.id)
              ORDER BY p.ordem, p.id'
        )->fetchAll();

        [$where, $params] = $this->filtro($periodo);
        $stmt = $this->db->prepare(
            "SELECT e.id, e.criado_em, e.origem, r.pergunta_id, r.nota, r.texto
               FROM envios e LEFT JOIN respostas r ON r.envio_id = e.id
              WHERE $where
              ORDER BY e.criado_em, e.id"
        );
        $stmt->execute($params);

        $porEnvio = [];
        foreach ($stmt as $linha) {
            $id = (int) $linha['id'];
            $porEnvio[$id] ??= ['criado_em' => $linha['criado_em'], 'origem' => $linha['origem'], 'valores' => []];
            if ($linha['pergunta_id'] !== null) {
                $porEnvio[$id]['valores'][(int) $linha['pergunta_id']] = $linha['nota'] ?? $linha['texto'];
            }
        }

        $cabecalho = array_merge(['Avaliação', 'Data e hora', 'Origem'], array_column($perguntas, 'texto'));
        $linhas = [];
        foreach ($porEnvio as $id => $envio) {
            $linha = [(string) $id, data_br($envio['criado_em']), ROTULOS_ORIGEM[$envio['origem']] ?? $envio['origem']];
            foreach ($perguntas as $pergunta) {
                $linha[] = (string) ($envio['valores'][(int) $pergunta['id']] ?? '');
            }
            $linhas[] = $linha;
        }
        return [$cabecalho, $linhas];
    }
}
