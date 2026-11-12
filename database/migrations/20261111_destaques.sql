BEGIN;
ALTER TABLE conteudos ADD COLUMN IF NOT EXISTS destaque_em TIMESTAMPTZ;

-- Registros antigos não têm o instante da marcação; preserva-se a última
-- atualização conhecida como referência inicial, sem alterar seu conteúdo.
UPDATE conteudos SET destaque_em = COALESCE(atualizado_em, publicado_em, criado_em)
WHERE destaque = TRUE AND destaque_em IS NULL;

CREATE OR REPLACE FUNCTION registrar_destaque_conteudo()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NOT NEW.destaque THEN
        NEW.destaque_em = NULL;
    ELSIF TG_OP = 'INSERT' THEN
        NEW.destaque_em = clock_timestamp();
    ELSIF NOT OLD.destaque THEN
        NEW.destaque_em = clock_timestamp();
    ELSE
        NEW.destaque_em = OLD.destaque_em;
    END IF;
    RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS trg_conteudos_destaque_em ON conteudos;
CREATE TRIGGER trg_conteudos_destaque_em
BEFORE INSERT OR UPDATE ON conteudos
FOR EACH ROW EXECUTE FUNCTION registrar_destaque_conteudo();

CREATE INDEX IF NOT EXISTS idx_conteudos_destaque_em
ON conteudos (destaque_em DESC, id DESC) WHERE destaque = TRUE;
COMMIT;
