<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Roteador simples: associa método HTTP + caminho a um método de controller.
 * Parâmetros no formato {id} aceitam apenas números e são passados ao método.
 */
final class Router
{
    /** @var list<array{0:string,1:string,2:array{0:class-string,1:string}}> */
    private array $rotas = [];

    public function get(string $caminho, array $acao): void
    {
        $this->adicionar('GET', $caminho, $acao);
    }

    public function post(string $caminho, array $acao): void
    {
        $this->adicionar('POST', $caminho, $acao);
    }

    private function adicionar(string $metodo, string $caminho, array $acao): void
    {
        $caminho = '/' . trim($caminho, '/');
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $caminho) . '$#';
        $this->rotas[] = [$metodo, $regex, $acao];
    }

    public function despachar(string $metodo, string $uri): void
    {
        $metodo = $metodo === 'HEAD' ? 'GET' : $metodo;
        $caminho = $this->caminhoRelativo($uri);
        $outroMetodo = false;

        foreach ($this->rotas as [$metodoRota, $regex, [$classe, $funcao]]) {
            if (!preg_match($regex, $caminho, $partes)) {
                continue;
            }
            if ($metodoRota !== $metodo) {
                $outroMetodo = true;
                continue;
            }
            $parametros = array_map('intval', array_filter($partes, 'is_string', ARRAY_FILTER_USE_KEY));
            (new $classe())->$funcao(...$parametros);
            return;
        }

        http_response_code($outroMetodo ? 405 : 404);
        View::render('erro', [
            'titulo' => 'Página não encontrada',
            'mensagem' => 'O endereço acessado não existe. Confira o link ou volte ao início.',
        ], 'publico');
    }

    /** Remove a subpasta da instalação (ex.: /apae/public) e devolve o caminho da rota. */
    private function caminhoRelativo(string $uri): string
    {
        $caminho = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
        $base = base_url();
        if ($base !== '' && str_starts_with($caminho, $base)) {
            $caminho = substr($caminho, strlen($base));
        }
        $caminho = '/' . trim($caminho, '/');
        return $caminho === '/index.php' ? '/' : $caminho;
    }
}
