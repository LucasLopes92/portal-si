-- Portal SI - Schema inicial do banco PostgreSQL
-- Aula 06/07/08 - Projeto de Extensão IV

BEGIN;

-- Extensão utilizada pelos índices case-insensitive de e-mail.
CREATE EXTENSION IF NOT EXISTS citext;

-- Remove apenas a função do gatilho caso o script seja executado novamente.
DROP FUNCTION IF EXISTS atualizar_data_alteracao() CASCADE;

CREATE TABLE IF NOT EXISTS usuarios (
    id              BIGSERIAL PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    email           CITEXT NOT NULL UNIQUE,
    senha_hash      VARCHAR(255) NOT NULL,
    perfil          VARCHAR(20) NOT NULL DEFAULT 'aluno',
    status          VARCHAR(20) NOT NULL DEFAULT 'ativo',
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT ck_usuarios_nome CHECK (char_length(btrim(nome)) >= 3),
    CONSTRAINT ck_usuarios_perfil CHECK (perfil IN ('admin', 'editor', 'aluno')),
    CONSTRAINT ck_usuarios_status CHECK (status IN ('ativo', 'inativo', 'bloqueado'))
);

CREATE TABLE IF NOT EXISTS categorias (
    id              BIGSERIAL PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL,
    slug            VARCHAR(120) NOT NULL UNIQUE,
    descricao       TEXT,
    icone           VARCHAR(100),
    ordem           INTEGER NOT NULL DEFAULT 0,
    ativo           BOOLEAN NOT NULL DEFAULT TRUE,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT ck_categorias_nome CHECK (char_length(btrim(nome)) > 0),
    CONSTRAINT ck_categorias_ordem CHECK (ordem >= 0)
);

CREATE TABLE IF NOT EXISTS conteudos (
    id              BIGSERIAL PRIMARY KEY,
    titulo          VARCHAR(200) NOT NULL,
    slug            VARCHAR(220) NOT NULL UNIQUE,
    resumo          VARCHAR(500),
    corpo           TEXT NOT NULL,
    imagem_capa     VARCHAR(255),
    link_youtube    VARCHAR(500),
    categoria_id    BIGINT NOT NULL,
    autor_id        BIGINT NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'rascunho',
    destaque        BOOLEAN NOT NULL DEFAULT FALSE,
    publicado_em    TIMESTAMPTZ,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_conteudos_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE RESTRICT,
    CONSTRAINT fk_conteudos_autor
        FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT ck_conteudos_status
        CHECK (status IN ('rascunho', 'publicado', 'arquivado'))
);

CREATE TABLE IF NOT EXISTS tags (
    id              BIGSERIAL PRIMARY KEY,
    nome            VARCHAR(80) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT ck_tags_nome CHECK (char_length(btrim(nome)) > 0)
);

CREATE TABLE IF NOT EXISTS conteudo_tags (
    conteudo_id     BIGINT NOT NULL,
    tag_id          BIGINT NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (conteudo_id, tag_id),
    CONSTRAINT fk_conteudo_tags_conteudo
        FOREIGN KEY (conteudo_id) REFERENCES conteudos (id) ON DELETE CASCADE,
    CONSTRAINT fk_conteudo_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS eventos (
    id              BIGSERIAL PRIMARY KEY,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT,
    data_inicio     TIMESTAMPTZ NOT NULL,
    data_fim        TIMESTAMPTZ,
    local           VARCHAR(200),
    link_inscricao  VARCHAR(500),
    autor_id        BIGINT NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'agendado',
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_eventos_autor
        FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT ck_eventos_status
        CHECK (status IN ('agendado', 'realizado', 'cancelado')),
    CONSTRAINT ck_eventos_periodo
        CHECK (data_fim IS NULL OR data_fim >= data_inicio)
);

CREATE TABLE IF NOT EXISTS midias (
    id              BIGSERIAL PRIMARY KEY,
    nome_arquivo    VARCHAR(255) NOT NULL,
    caminho         VARCHAR(500) NOT NULL,
    tipo_mime       VARCHAR(100) NOT NULL,
    tamanho_bytes   BIGINT NOT NULL,
    enviado_por     BIGINT NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_midias_usuario
        FOREIGN KEY (enviado_por) REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT ck_midias_tamanho CHECK (tamanho_bytes >= 0)
);

CREATE TABLE IF NOT EXISTS auditoria (
    id              BIGSERIAL PRIMARY KEY,
    usuario_id      BIGINT,
    acao            VARCHAR(100) NOT NULL,
    tabela_afetada  VARCHAR(100),
    registro_id     BIGINT,
    detalhes        JSONB,
    ip_origem       INET,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
);

-- Índices para chaves estrangeiras e consultas recorrentes.
CREATE INDEX IF NOT EXISTS idx_conteudos_categoria_id ON conteudos (categoria_id);
CREATE INDEX IF NOT EXISTS idx_conteudos_autor_id ON conteudos (autor_id);
CREATE INDEX IF NOT EXISTS idx_conteudos_status ON conteudos (status);
CREATE INDEX IF NOT EXISTS idx_conteudos_publicado_em ON conteudos (publicado_em);
CREATE INDEX IF NOT EXISTS idx_conteudos_destaque ON conteudos (destaque);
CREATE INDEX IF NOT EXISTS idx_eventos_autor_id ON eventos (autor_id);
CREATE INDEX IF NOT EXISTS idx_eventos_data_inicio ON eventos (data_inicio);
CREATE INDEX IF NOT EXISTS idx_eventos_status ON eventos (status);
CREATE INDEX IF NOT EXISTS idx_midias_enviado_por ON midias (enviado_por);
CREATE INDEX IF NOT EXISTS idx_auditoria_usuario_id ON auditoria (usuario_id);
CREATE INDEX IF NOT EXISTS idx_auditoria_criado_em ON auditoria (criado_em);
CREATE INDEX IF NOT EXISTS idx_categorias_ordem ON categorias (ordem);

-- Mantém atualizado_em consistente em alterações realizadas diretamente no banco.
CREATE OR REPLACE FUNCTION atualizar_data_alteracao()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_usuarios_atualizado_em ON usuarios;
CREATE TRIGGER trg_usuarios_atualizado_em
    BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION atualizar_data_alteracao();

DROP TRIGGER IF EXISTS trg_categorias_atualizado_em ON categorias;
CREATE TRIGGER trg_categorias_atualizado_em
    BEFORE UPDATE ON categorias
    FOR EACH ROW EXECUTE FUNCTION atualizar_data_alteracao();

DROP TRIGGER IF EXISTS trg_conteudos_atualizado_em ON conteudos;
CREATE TRIGGER trg_conteudos_atualizado_em
    BEFORE UPDATE ON conteudos
    FOR EACH ROW EXECUTE FUNCTION atualizar_data_alteracao();

DROP TRIGGER IF EXISTS trg_tags_atualizado_em ON tags;
CREATE TRIGGER trg_tags_atualizado_em
    BEFORE UPDATE ON tags
    FOR EACH ROW EXECUTE FUNCTION atualizar_data_alteracao();

DROP TRIGGER IF EXISTS trg_eventos_atualizado_em ON eventos;
CREATE TRIGGER trg_eventos_atualizado_em
    BEFORE UPDATE ON eventos
    FOR EACH ROW EXECUTE FUNCTION atualizar_data_alteracao();

COMMIT;
