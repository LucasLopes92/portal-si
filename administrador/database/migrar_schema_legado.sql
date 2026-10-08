-- Migração aditiva do banco legado portal_si para o schema usado pelo Portal SI.
-- As colunas antigas são preservadas para evitar perda de dados e manter
-- compatibilidade com exercícios anteriores.

BEGIN;

CREATE EXTENSION IF NOT EXISTS citext;

-- Usuários -------------------------------------------------------------------

ALTER TABLE usuarios
    ALTER COLUMN email TYPE CITEXT USING email::CITEXT,
    ADD COLUMN IF NOT EXISTS senha_hash VARCHAR(255),
    ADD COLUMN IF NOT EXISTS perfil VARCHAR(20),
    ADD COLUMN IF NOT EXISTS status VARCHAR(20),
    ADD COLUMN IF NOT EXISTS criado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMPTZ;

UPDATE usuarios
SET senha_hash = COALESCE(senha_hash, senha),
    perfil = COALESCE(
        perfil,
        CASE WHEN tipo IN ('admin', 'editor', 'aluno') THEN tipo ELSE 'aluno' END
    ),
    status = COALESCE(status, 'ativo'),
    criado_em = COALESCE(criado_em, created_at, CURRENT_TIMESTAMP),
    atualizado_em = COALESCE(atualizado_em, created_at, CURRENT_TIMESTAMP);

ALTER TABLE usuarios
    ALTER COLUMN perfil SET DEFAULT 'aluno',
    ALTER COLUMN status SET DEFAULT 'ativo',
    ALTER COLUMN criado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN atualizado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN senha_hash SET NOT NULL,
    ALTER COLUMN perfil SET NOT NULL,
    ALTER COLUMN status SET NOT NULL,
    ALTER COLUMN criado_em SET NOT NULL,
    ALTER COLUMN atualizado_em SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_usuarios_nome' AND conrelid = 'usuarios'::regclass
    ) THEN
        ALTER TABLE usuarios
            ADD CONSTRAINT ck_usuarios_nome CHECK (char_length(btrim(nome)) >= 3);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_usuarios_perfil' AND conrelid = 'usuarios'::regclass
    ) THEN
        ALTER TABLE usuarios
            ADD CONSTRAINT ck_usuarios_perfil CHECK (perfil IN ('admin', 'editor', 'aluno'));
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_usuarios_status' AND conrelid = 'usuarios'::regclass
    ) THEN
        ALTER TABLE usuarios
            ADD CONSTRAINT ck_usuarios_status CHECK (status IN ('ativo', 'inativo', 'bloqueado'));
    END IF;
END;
$$;

-- Categorias -----------------------------------------------------------------

ALTER TABLE categorias
    ADD COLUMN IF NOT EXISTS slug VARCHAR(120),
    ADD COLUMN IF NOT EXISTS icone VARCHAR(100),
    ADD COLUMN IF NOT EXISTS ordem INTEGER,
    ADD COLUMN IF NOT EXISTS ativo BOOLEAN,
    ADD COLUMN IF NOT EXISTS criado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMPTZ;

UPDATE categorias
SET slug = COALESCE(
        NULLIF(
            trim(BOTH '-' FROM regexp_replace(
                translate(
                    lower(nome),
                    'áàâãäéèêëíìîïóòôõöúùûüç',
                    'aaaaaeeeeiiiiooooouuuuc'
                ),
                '[^a-z0-9]+',
                '-',
                'g'
            )),
            ''
        ),
        'categoria-' || id
    ),
    ordem = COALESCE(ordem, id),
    ativo = COALESCE(ativo, TRUE),
    criado_em = COALESCE(criado_em, created_at, CURRENT_TIMESTAMP),
    atualizado_em = COALESCE(atualizado_em, created_at, CURRENT_TIMESTAMP)
WHERE slug IS NULL
   OR btrim(slug) = ''
   OR ordem IS NULL
   OR ativo IS NULL
   OR criado_em IS NULL
   OR atualizado_em IS NULL;

WITH slugs_repetidos AS (
    SELECT id, slug, row_number() OVER (PARTITION BY slug ORDER BY id) AS ocorrencia
    FROM categorias
)
UPDATE categorias AS categoria
SET slug = categoria.slug || '-' || categoria.id
FROM slugs_repetidos
WHERE categoria.id = slugs_repetidos.id
  AND slugs_repetidos.ocorrencia > 1;

ALTER TABLE categorias
    ALTER COLUMN ordem SET DEFAULT 0,
    ALTER COLUMN ativo SET DEFAULT TRUE,
    ALTER COLUMN criado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN atualizado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN slug SET NOT NULL,
    ALTER COLUMN ordem SET NOT NULL,
    ALTER COLUMN ativo SET NOT NULL,
    ALTER COLUMN criado_em SET NOT NULL,
    ALTER COLUMN atualizado_em SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'uq_categorias_slug' AND conrelid = 'categorias'::regclass
    ) THEN
        ALTER TABLE categorias ADD CONSTRAINT uq_categorias_slug UNIQUE (slug);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_categorias_nome' AND conrelid = 'categorias'::regclass
    ) THEN
        ALTER TABLE categorias
            ADD CONSTRAINT ck_categorias_nome CHECK (char_length(btrim(nome)) > 0);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_categorias_ordem' AND conrelid = 'categorias'::regclass
    ) THEN
        ALTER TABLE categorias ADD CONSTRAINT ck_categorias_ordem CHECK (ordem >= 0);
    END IF;
END;
$$;

-- Conteúdos ------------------------------------------------------------------

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM usuarios) THEN
        RAISE EXCEPTION 'A migração exige ao menos um usuário para definir o autor dos conteúdos legados.';
    END IF;
END;
$$;

ALTER TABLE conteudos
    ADD COLUMN IF NOT EXISTS slug VARCHAR(220),
    ADD COLUMN IF NOT EXISTS resumo VARCHAR(500),
    ADD COLUMN IF NOT EXISTS corpo TEXT,
    ADD COLUMN IF NOT EXISTS imagem_capa VARCHAR(255),
    ADD COLUMN IF NOT EXISTS autor_id INTEGER,
    ADD COLUMN IF NOT EXISTS status VARCHAR(20),
    ADD COLUMN IF NOT EXISTS destaque BOOLEAN,
    ADD COLUMN IF NOT EXISTS publicado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS criado_em TIMESTAMPTZ,
    ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMPTZ;

UPDATE conteudos
SET slug = COALESCE(
        NULLIF(
            trim(BOTH '-' FROM regexp_replace(
                translate(
                    lower(titulo),
                    'áàâãäéèêëíìîïóòôõöúùûüç',
                    'aaaaaeeeeiiiiooooouuuuc'
                ),
                '[^a-z0-9]+',
                '-',
                'g'
            )),
            ''
        ),
        'conteudo-' || id
    ),
    resumo = COALESCE(
        resumo,
        left(regexp_replace(COALESCE(texto, ''), '<[^>]*>', '', 'g'), 500)
    ),
    corpo = COALESCE(corpo, texto, ''),
    imagem_capa = COALESCE(imagem_capa, imagem_url),
    autor_id = COALESCE(autor_id, (SELECT min(id) FROM usuarios)),
    status = COALESCE(status, CASE WHEN COALESCE(ativo, TRUE) THEN 'publicado' ELSE 'rascunho' END),
    destaque = COALESCE(destaque, FALSE),
    publicado_em = CASE
        WHEN COALESCE(status, CASE WHEN COALESCE(ativo, TRUE) THEN 'publicado' ELSE 'rascunho' END) = 'publicado'
            THEN COALESCE(publicado_em, created_at, CURRENT_TIMESTAMP)
        ELSE publicado_em
    END,
    criado_em = COALESCE(criado_em, created_at, CURRENT_TIMESTAMP),
    atualizado_em = COALESCE(atualizado_em, updated_at, created_at, CURRENT_TIMESTAMP);

WITH slugs_repetidos AS (
    SELECT id, slug, row_number() OVER (PARTITION BY slug ORDER BY id) AS ocorrencia
    FROM conteudos
)
UPDATE conteudos AS conteudo
SET slug = conteudo.slug || '-' || conteudo.id
FROM slugs_repetidos
WHERE conteudo.id = slugs_repetidos.id
  AND slugs_repetidos.ocorrencia > 1;

ALTER TABLE conteudos
    ALTER COLUMN status SET DEFAULT 'rascunho',
    ALTER COLUMN destaque SET DEFAULT FALSE,
    ALTER COLUMN criado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN atualizado_em SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN categoria_id SET NOT NULL,
    ALTER COLUMN slug SET NOT NULL,
    ALTER COLUMN corpo SET NOT NULL,
    ALTER COLUMN autor_id SET NOT NULL,
    ALTER COLUMN status SET NOT NULL,
    ALTER COLUMN destaque SET NOT NULL,
    ALTER COLUMN criado_em SET NOT NULL,
    ALTER COLUMN atualizado_em SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'uq_conteudos_slug' AND conrelid = 'conteudos'::regclass
    ) THEN
        ALTER TABLE conteudos ADD CONSTRAINT uq_conteudos_slug UNIQUE (slug);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'fk_conteudos_autor' AND conrelid = 'conteudos'::regclass
    ) THEN
        ALTER TABLE conteudos
            ADD CONSTRAINT fk_conteudos_autor
            FOREIGN KEY (autor_id) REFERENCES usuarios (id) ON DELETE RESTRICT;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM pg_constraint
        WHERE conname = 'ck_conteudos_status' AND conrelid = 'conteudos'::regclass
    ) THEN
        ALTER TABLE conteudos
            ADD CONSTRAINT ck_conteudos_status
            CHECK (status IN ('rascunho', 'publicado', 'arquivado'));
    END IF;
END;
$$;

-- Tabelas complementares -----------------------------------------------------

CREATE TABLE IF NOT EXISTS tags (
    id              SERIAL PRIMARY KEY,
    nome            VARCHAR(80) NOT NULL,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ck_tags_nome CHECK (char_length(btrim(nome)) > 0)
);

CREATE TABLE IF NOT EXISTS conteudo_tags (
    conteudo_id     INTEGER NOT NULL,
    tag_id          INTEGER NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (conteudo_id, tag_id),
    CONSTRAINT fk_conteudo_tags_conteudo
        FOREIGN KEY (conteudo_id) REFERENCES conteudos (id) ON DELETE CASCADE,
    CONSTRAINT fk_conteudo_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS eventos (
    id              SERIAL PRIMARY KEY,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT,
    data_inicio     TIMESTAMPTZ NOT NULL,
    data_fim        TIMESTAMPTZ,
    local           VARCHAR(200),
    link_inscricao  VARCHAR(500),
    autor_id        INTEGER NOT NULL,
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
    id              SERIAL PRIMARY KEY,
    nome_arquivo    VARCHAR(255) NOT NULL,
    caminho         VARCHAR(500) NOT NULL,
    tipo_mime       VARCHAR(100) NOT NULL,
    tamanho_bytes   BIGINT NOT NULL,
    enviado_por     INTEGER NOT NULL,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_midias_usuario
        FOREIGN KEY (enviado_por) REFERENCES usuarios (id) ON DELETE RESTRICT,
    CONSTRAINT ck_midias_tamanho CHECK (tamanho_bytes >= 0)
);

CREATE TABLE IF NOT EXISTS auditoria (
    id              BIGSERIAL PRIMARY KEY,
    usuario_id      INTEGER,
    acao            VARCHAR(100) NOT NULL,
    tabela_afetada  VARCHAR(100),
    registro_id     INTEGER,
    detalhes        JSONB,
    ip_origem       INET,
    criado_em       TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
);

-- Índices e atualização automática ------------------------------------------

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
