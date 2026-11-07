-- Questionário de Avaliação APAE Rio do Sul
-- Script idempotente: pode ser executado mais de uma vez sem erro.

CREATE TABLE IF NOT EXISTS usuarios (
    id             SERIAL PRIMARY KEY,
    nome           VARCHAR(120) NOT NULL,
    email          VARCHAR(180) NOT NULL,
    senha_hash     VARCHAR(255) NOT NULL,
    ativo          BOOLEAN      NOT NULL DEFAULT TRUE,
    ultimo_acesso  TIMESTAMPTZ,
    criado_em      TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE UNIQUE INDEX IF NOT EXISTS usuarios_email_unico ON usuarios (LOWER(email));

CREATE TABLE IF NOT EXISTS perguntas (
    id             SERIAL PRIMARY KEY,
    texto          TEXT         NOT NULL,
    tipo           VARCHAR(10)  NOT NULL CHECK (tipo IN ('escala', 'texto')),
    obrigatoria    BOOLEAN      NOT NULL DEFAULT FALSE,
    ativa          BOOLEAN      NOT NULL DEFAULT TRUE,
    ordem          INTEGER      NOT NULL DEFAULT 0,
    criado_em      TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    atualizado_em  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- Um "envio" é uma avaliação completa. Não guarda IP nem dados pessoais (LGPD).
CREATE TABLE IF NOT EXISTS envios (
    id         SERIAL PRIMARY KEY,
    origem     VARCHAR(10)  NOT NULL DEFAULT 'link' CHECK (origem IN ('qrcode', 'link', 'totem')),
    criado_em  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS envios_criado_em ON envios (criado_em);

CREATE TABLE IF NOT EXISTS respostas (
    id           SERIAL PRIMARY KEY,
    envio_id     INTEGER  NOT NULL REFERENCES envios (id)    ON DELETE CASCADE,
    pergunta_id  INTEGER  NOT NULL REFERENCES perguntas (id) ON DELETE RESTRICT,
    nota         SMALLINT CHECK (nota BETWEEN 1 AND 5),
    texto        TEXT,
    CONSTRAINT respostas_valor CHECK (nota IS NOT NULL OR texto IS NOT NULL),
    CONSTRAINT respostas_unica UNIQUE (envio_id, pergunta_id)
);
CREATE INDEX IF NOT EXISTS respostas_pergunta ON respostas (pergunta_id);

CREATE TABLE IF NOT EXISTS tentativas_login (
    id         SERIAL PRIMARY KEY,
    ip         VARCHAR(64)  NOT NULL,
    criado_em  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS tentativas_login_ip ON tentativas_login (ip, criado_em);
