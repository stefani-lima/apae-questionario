# Questionário de Avaliação da APAE de Rio do Sul

Sistema web para a APAE de Rio do Sul receber avaliações sobre o atendimento. Familiares, responsáveis e visitantes leem um QR Code na recepção, respondem pelo celular sem se identificar, e a equipe da instituição acompanha os resultados em uma área restrita.

Projeto de Extensão da disciplina de Programação Web I, curso de Sistemas de Informação da Unidavi.

Site publicado:

- Questionário: https://apae-avaliacao.onrender.com
- Área da equipe: https://apae-avaliacao.onrender.com/admin

<p>
  <img src="docs/img/questionario-celular.png" alt="Questionário aberto no celular" width="240">
  <img src="docs/img/painel.png" alt="Painel de resultados da equipe" width="560">
</p>

## Funcionalidades

- Questionário anônimo com notas de 1 a 5 (carinhas) e campo para comentários
- Login da equipe da APAE
- Cadastro, edição, ordenação e desativação de perguntas
- Painel com a média geral, a distribuição das notas e os comentários, com filtro por período
- Exportação das respostas para planilha
- Cartaz com QR Code pronto para imprimir
- Cadastro de usuários da equipe

Os requisitos completos estão em [docs/requisitos.md](docs/requisitos.md).

## Tecnologias

PHP 8 com PDO, PostgreSQL, HTML, CSS e JavaScript, organizados no padrão MVC.

## Como executar

1. Instale o PostgreSQL e o PHP 8.1 ou mais recente (o PHP do XAMPP serve).
2. No `php.ini`, habilite as extensões `pdo_pgsql` e `pgsql`.
3. Execute o `iniciar.bat`. Na primeira vez ele abre o arquivo `.env`: informe a senha do PostgreSQL em `DB_PASSWORD`, salve e execute o `iniciar.bat` novamente.
4. Acesse `http://localhost:8080` para o questionário e `http://localhost:8080/admin` para a área da equipe.

O banco de dados, as tabelas, as perguntas iniciais e o primeiro usuário são criados automaticamente.

## Estrutura

```text
app/
  Controllers/   recebem as requisições
  Models/        acesso ao banco de dados
  Views/         páginas HTML
  Core/          roteador, conexão e autenticação
config/          configurações e rotas
public/          arquivos acessíveis pelo navegador
database/        script de criação do banco
docs/            documento de requisitos
```

## Equipe

- Bruna
- Ronaldo
- Stéfani

Professor: Sandro

Unidavi, 2026
