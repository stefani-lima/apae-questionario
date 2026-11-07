<?php
declare(strict_types=1);

/*
 * Rotas da aplicação: [Controller::class, 'método'].
 * {id} aceita apenas números e é passado como parâmetro ao método.
 *
 * @var App\Core\Router $router
 */

use App\Controllers\AuthController;
use App\Controllers\AvaliacaoController;
use App\Controllers\ContaController;
use App\Controllers\PainelController;
use App\Controllers\PerguntaController;
use App\Controllers\QrCodeController;
use App\Controllers\QuestionarioController;
use App\Controllers\SaudeController;
use App\Controllers\UsuarioController;

// Público
$router->get('/', [QuestionarioController::class, 'exibir']);
$router->post('/', [QuestionarioController::class, 'enviar']);
$router->get('/obrigado', [QuestionarioController::class, 'obrigado']);
$router->get('/saude', [SaudeController::class, 'verificar']);

// Autenticação
$router->get('/admin/login', [AuthController::class, 'formulario']);
$router->post('/admin/login', [AuthController::class, 'entrar']);
$router->post('/admin/sair', [AuthController::class, 'sair']);

// Resultados e avaliações
$router->get('/admin', [PainelController::class, 'index']);
$router->get('/admin/avaliacoes', [AvaliacaoController::class, 'index']);
$router->get('/admin/avaliacoes/exportar', [AvaliacaoController::class, 'exportar']);
$router->get('/admin/avaliacoes/{id}', [AvaliacaoController::class, 'mostrar']);
$router->post('/admin/avaliacoes/{id}/excluir', [AvaliacaoController::class, 'excluir']);

// Perguntas
$router->get('/admin/perguntas', [PerguntaController::class, 'index']);
$router->get('/admin/perguntas/nova', [PerguntaController::class, 'criar']);
$router->post('/admin/perguntas', [PerguntaController::class, 'salvar']);
$router->get('/admin/perguntas/{id}/editar', [PerguntaController::class, 'editar']);
$router->post('/admin/perguntas/{id}', [PerguntaController::class, 'atualizar']);
$router->post('/admin/perguntas/{id}/mover', [PerguntaController::class, 'mover']);
$router->post('/admin/perguntas/{id}/alternar', [PerguntaController::class, 'alternar']);
$router->post('/admin/perguntas/{id}/excluir', [PerguntaController::class, 'excluir']);

// QR Code, usuários e conta
$router->get('/admin/qrcode', [QrCodeController::class, 'index']);
$router->get('/admin/usuarios', [UsuarioController::class, 'index']);
$router->post('/admin/usuarios', [UsuarioController::class, 'salvar']);
$router->post('/admin/usuarios/{id}/alternar', [UsuarioController::class, 'alternar']);
$router->get('/admin/conta', [ContaController::class, 'index']);
$router->post('/admin/conta', [ContaController::class, 'alterarSenha']);
