-- Preserva publicações e capas já existentes.
ALTER TABLE conteudos
    ADD COLUMN IF NOT EXISTS imagens JSONB NOT NULL DEFAULT '[]'::jsonb
    CHECK (jsonb_typeof(imagens) = 'array');
