-- Portal SI - Dados iniciais para homologação
-- Execute este arquivo depois de database/schema.sql.

BEGIN;

-- Categorias básicas do Portal de Comunicação SI.
INSERT INTO categorias (nome, slug, descricao, icone, ordem, ativo)
VALUES
    ('Notícias', 'noticias', 'Comunicados e notícias do curso.', 'newspaper', 1, TRUE),
    ('Eventos', 'eventos', 'Palestras, encontros e atividades acadêmicas.', 'calendar', 2, TRUE),
    ('Projetos de Extensão', 'projetos-extensao', 'Projetos e ações de extensão universitária.', 'users', 3, TRUE),
    ('Pesquisa e Inovação', 'pesquisa-inovacao', 'Iniciativas de pesquisa, tecnologia e inovação.', 'flask', 4, TRUE),
    ('Vida Acadêmica', 'vida-academica', 'Informações relevantes para estudantes e docentes.', 'graduation-cap', 5, TRUE),
    ('Oportunidades', 'oportunidades', 'Bolsas, estágios, empregos e oportunidades.', 'briefcase', 6, TRUE)
ON CONFLICT (slug) DO UPDATE SET
    nome = EXCLUDED.nome,
    descricao = EXCLUDED.descricao,
    icone = EXCLUDED.icone,
    ordem = EXCLUDED.ordem,
    ativo = EXCLUDED.ativo;

-- Usuário administrador inicial.
-- O valor abaixo é um hash Bcrypt com custo 10. A senha temporária deve ser
-- alterada antes da apresentação ou da publicação do sistema.
INSERT INTO usuarios (nome, email, senha_hash, perfil, status)
VALUES (
    'Administrador Portal SI',
    'admin@esucri.com.br',
    '$2y$10$lHA7DM/MITRdlceuZjiHxeJJs8lWUl1lGzOkpiO04Ql0mQOLX6.l.',
    'admin',
    'ativo'
)
ON CONFLICT (email) DO UPDATE SET
    nome = EXCLUDED.nome,
    senha_hash = EXCLUDED.senha_hash,
    perfil = EXCLUDED.perfil,
    status = EXCLUDED.status;

COMMIT;
