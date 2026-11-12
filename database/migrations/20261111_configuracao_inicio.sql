CREATE TABLE IF NOT EXISTS configuracao_inicio (
    id SMALLINT PRIMARY KEY DEFAULT 1 CHECK (id = 1),
    etiqueta VARCHAR(100) NOT NULL DEFAULT 'Portal de Comunicação SI · ESUCRI',
    titulo VARCHAR(140) NOT NULL DEFAULT 'Sistemas de Informação em pauta.',
    descricao VARCHAR(300) NOT NULL DEFAULT 'Notícias, projetos e encontros que conectam a comunidade acadêmica. Acompanhe o que acontece no curso e participe da vida no campus.',
    botao_texto VARCHAR(50) NOT NULL DEFAULT 'Explorar publicações',
    botao_destino VARCHAR(20) NOT NULL DEFAULT 'publicacoes' CHECK (botao_destino IN ('publicacoes', 'noticias', 'eventos')),
    imagem VARCHAR(255) NOT NULL DEFAULT 'public/assets/images/inicio-campus.jpg',
    imagem_alt VARCHAR(180) NOT NULL DEFAULT 'Estudantes trabalhando em equipe com seus computadores.',
    imagem_posicao SMALLINT NOT NULL DEFAULT 50 CHECK (imagem_posicao BETWEEN 0 AND 100),
    atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_por BIGINT REFERENCES usuarios(id) ON DELETE SET NULL
);
INSERT INTO configuracao_inicio (id) VALUES (1) ON CONFLICT (id) DO NOTHING;
