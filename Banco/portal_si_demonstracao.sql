-- Portal SI - cópia de demonstração para avaliação em 12/11/2026
-- Restaurar em uma base VAZIA. Não executar sobre banco em uso.
-- Usuários anonimizados; auditoria original não exportada.
--
-- PostgreSQL database dump
--

\restrict 2o2zFR9c9OC3aXQjrhhoMsn3NKUuxAUg2aLGASx1Ka5TO3KmBwUt2X0kRR8aYY2

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: atualizar_data_alteracao(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.atualizar_data_alteracao() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


--
-- Name: registrar_destaque_conteudo(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.registrar_destaque_conteudo() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: auditoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.auditoria (
    id bigint NOT NULL,
    usuario_id bigint,
    acao character varying(100) NOT NULL,
    tabela_afetada character varying(100),
    registro_id bigint,
    detalhes jsonb,
    ip_origem inet,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.auditoria_id_seq OWNED BY public.auditoria.id;


--
-- Name: categorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorias (
    id bigint NOT NULL,
    nome character varying(100) NOT NULL,
    slug character varying(120) NOT NULL,
    descricao text,
    icone character varying(100),
    ordem integer DEFAULT 0 NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_categorias_nome CHECK ((char_length(btrim((nome)::text)) > 0)),
    CONSTRAINT ck_categorias_ordem CHECK ((ordem >= 0))
);


--
-- Name: categorias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: categorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;


--
-- Name: configuracao_inicio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.configuracao_inicio (
    id smallint DEFAULT 1 NOT NULL,
    etiqueta character varying(100) DEFAULT 'Portal de Comunicação SI · ESUCRI'::character varying NOT NULL,
    titulo character varying(140) DEFAULT 'Sistemas de Informação em pauta.'::character varying NOT NULL,
    descricao character varying(300) DEFAULT 'Notícias, projetos e encontros que conectam a comunidade acadêmica. Acompanhe o que acontece no curso e participe da vida no campus.'::character varying NOT NULL,
    botao_texto character varying(50) DEFAULT 'Explorar publicações'::character varying NOT NULL,
    botao_destino character varying(20) DEFAULT 'publicacoes'::character varying NOT NULL,
    imagem character varying(255) DEFAULT 'public/assets/images/inicio-campus.jpg'::character varying NOT NULL,
    imagem_alt character varying(180) DEFAULT 'Estudantes trabalhando em equipe com seus computadores.'::character varying NOT NULL,
    imagem_posicao smallint DEFAULT 50 NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_por bigint,
    CONSTRAINT configuracao_inicio_botao_destino_check CHECK (((botao_destino)::text = ANY ((ARRAY['publicacoes'::character varying, 'noticias'::character varying, 'eventos'::character varying])::text[]))),
    CONSTRAINT configuracao_inicio_id_check CHECK ((id = 1)),
    CONSTRAINT configuracao_inicio_imagem_posicao_check CHECK (((imagem_posicao >= 0) AND (imagem_posicao <= 100)))
);


--
-- Name: conteudo_tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.conteudo_tags (
    conteudo_id bigint NOT NULL,
    tag_id bigint NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: conteudos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.conteudos (
    id bigint NOT NULL,
    titulo character varying(200) NOT NULL,
    slug character varying(220) NOT NULL,
    resumo character varying(500),
    corpo text NOT NULL,
    imagem_capa character varying(255),
    link_youtube character varying(500),
    categoria_id bigint NOT NULL,
    autor_id bigint NOT NULL,
    status character varying(20) DEFAULT 'rascunho'::character varying NOT NULL,
    destaque boolean DEFAULT false NOT NULL,
    publicado_em timestamp with time zone,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    imagens jsonb DEFAULT '[]'::jsonb NOT NULL,
    destaque_em timestamp with time zone,
    CONSTRAINT ck_conteudos_status CHECK (((status)::text = ANY ((ARRAY['rascunho'::character varying, 'publicado'::character varying, 'arquivado'::character varying])::text[]))),
    CONSTRAINT conteudos_imagens_check CHECK ((jsonb_typeof(imagens) = 'array'::text))
);


--
-- Name: conteudos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.conteudos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: conteudos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.conteudos_id_seq OWNED BY public.conteudos.id;


--
-- Name: eventos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.eventos (
    id bigint NOT NULL,
    titulo character varying(200) NOT NULL,
    descricao text,
    data_inicio timestamp with time zone NOT NULL,
    data_fim timestamp with time zone,
    local character varying(200),
    link_inscricao character varying(500),
    autor_id bigint NOT NULL,
    status character varying(20) DEFAULT 'agendado'::character varying NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_eventos_periodo CHECK (((data_fim IS NULL) OR (data_fim >= data_inicio))),
    CONSTRAINT ck_eventos_status CHECK (((status)::text = ANY ((ARRAY['agendado'::character varying, 'realizado'::character varying, 'cancelado'::character varying])::text[])))
);


--
-- Name: eventos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.eventos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: eventos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.eventos_id_seq OWNED BY public.eventos.id;


--
-- Name: midias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.midias (
    id bigint NOT NULL,
    nome_arquivo character varying(255) NOT NULL,
    caminho character varying(500) NOT NULL,
    tipo_mime character varying(100) NOT NULL,
    tamanho_bytes bigint NOT NULL,
    enviado_por bigint NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_midias_tamanho CHECK ((tamanho_bytes >= 0))
);


--
-- Name: midias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.midias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: midias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.midias_id_seq OWNED BY public.midias.id;


--
-- Name: tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tags (
    id bigint NOT NULL,
    nome character varying(80) NOT NULL,
    slug character varying(100) NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_tags_nome CHECK ((char_length(btrim((nome)::text)) > 0))
);


--
-- Name: tags_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tags_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tags_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tags_id_seq OWNED BY public.tags.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios (
    id bigint NOT NULL,
    nome character varying(150) NOT NULL,
    email public.citext NOT NULL,
    senha_hash character varying(255) NOT NULL,
    perfil character varying(20) DEFAULT 'aluno'::character varying NOT NULL,
    status character varying(20) DEFAULT 'ativo'::character varying NOT NULL,
    criado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    atualizado_em timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT ck_usuarios_nome CHECK ((char_length(btrim((nome)::text)) >= 3)),
    CONSTRAINT ck_usuarios_perfil CHECK (((perfil)::text = ANY ((ARRAY['admin'::character varying, 'editor'::character varying, 'aluno'::character varying])::text[]))),
    CONSTRAINT ck_usuarios_status CHECK (((status)::text = ANY ((ARRAY['ativo'::character varying, 'inativo'::character varying, 'bloqueado'::character varying])::text[])))
);


--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: auditoria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditoria ALTER COLUMN id SET DEFAULT nextval('public.auditoria_id_seq'::regclass);


--
-- Name: categorias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);


--
-- Name: conteudos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudos ALTER COLUMN id SET DEFAULT nextval('public.conteudos_id_seq'::regclass);


--
-- Name: eventos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.eventos ALTER COLUMN id SET DEFAULT nextval('public.eventos_id_seq'::regclass);


--
-- Name: midias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.midias ALTER COLUMN id SET DEFAULT nextval('public.midias_id_seq'::regclass);


--
-- Name: tags id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags ALTER COLUMN id SET DEFAULT nextval('public.tags_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Name: auditoria auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT auditoria_pkey PRIMARY KEY (id);


--
-- Name: categorias categorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);


--
-- Name: categorias categorias_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_slug_key UNIQUE (slug);


--
-- Name: configuracao_inicio configuracao_inicio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracao_inicio
    ADD CONSTRAINT configuracao_inicio_pkey PRIMARY KEY (id);


--
-- Name: conteudo_tags conteudo_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudo_tags
    ADD CONSTRAINT conteudo_tags_pkey PRIMARY KEY (conteudo_id, tag_id);


--
-- Name: conteudos conteudos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudos
    ADD CONSTRAINT conteudos_pkey PRIMARY KEY (id);


--
-- Name: conteudos conteudos_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudos
    ADD CONSTRAINT conteudos_slug_key UNIQUE (slug);


--
-- Name: eventos eventos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.eventos
    ADD CONSTRAINT eventos_pkey PRIMARY KEY (id);


--
-- Name: midias midias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.midias
    ADD CONSTRAINT midias_pkey PRIMARY KEY (id);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: tags tags_slug_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_slug_key UNIQUE (slug);


--
-- Name: usuarios usuarios_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: idx_auditoria_criado_em; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_auditoria_criado_em ON public.auditoria USING btree (criado_em);


--
-- Name: idx_auditoria_usuario_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_auditoria_usuario_id ON public.auditoria USING btree (usuario_id);


--
-- Name: idx_categorias_ordem; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categorias_ordem ON public.categorias USING btree (ordem);


--
-- Name: idx_conteudos_autor_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_autor_id ON public.conteudos USING btree (autor_id);


--
-- Name: idx_conteudos_categoria_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_categoria_id ON public.conteudos USING btree (categoria_id);


--
-- Name: idx_conteudos_destaque; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_destaque ON public.conteudos USING btree (destaque);


--
-- Name: idx_conteudos_destaque_em; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_destaque_em ON public.conteudos USING btree (destaque_em DESC, id DESC) WHERE (destaque = true);


--
-- Name: idx_conteudos_publicado_em; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_publicado_em ON public.conteudos USING btree (publicado_em);


--
-- Name: idx_conteudos_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_conteudos_status ON public.conteudos USING btree (status);


--
-- Name: idx_eventos_autor_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_eventos_autor_id ON public.eventos USING btree (autor_id);


--
-- Name: idx_eventos_data_inicio; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_eventos_data_inicio ON public.eventos USING btree (data_inicio);


--
-- Name: idx_eventos_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_eventos_status ON public.eventos USING btree (status);


--
-- Name: idx_midias_enviado_por; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_midias_enviado_por ON public.midias USING btree (enviado_por);


--
-- Name: categorias trg_categorias_atualizado_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_categorias_atualizado_em BEFORE UPDATE ON public.categorias FOR EACH ROW EXECUTE FUNCTION public.atualizar_data_alteracao();


--
-- Name: conteudos trg_conteudos_atualizado_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_conteudos_atualizado_em BEFORE UPDATE ON public.conteudos FOR EACH ROW EXECUTE FUNCTION public.atualizar_data_alteracao();


--
-- Name: conteudos trg_conteudos_destaque_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_conteudos_destaque_em BEFORE INSERT OR UPDATE ON public.conteudos FOR EACH ROW EXECUTE FUNCTION public.registrar_destaque_conteudo();


--
-- Name: eventos trg_eventos_atualizado_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_eventos_atualizado_em BEFORE UPDATE ON public.eventos FOR EACH ROW EXECUTE FUNCTION public.atualizar_data_alteracao();


--
-- Name: tags trg_tags_atualizado_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_tags_atualizado_em BEFORE UPDATE ON public.tags FOR EACH ROW EXECUTE FUNCTION public.atualizar_data_alteracao();


--
-- Name: usuarios trg_usuarios_atualizado_em; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_usuarios_atualizado_em BEFORE UPDATE ON public.usuarios FOR EACH ROW EXECUTE FUNCTION public.atualizar_data_alteracao();


--
-- Name: configuracao_inicio configuracao_inicio_atualizado_por_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracao_inicio
    ADD CONSTRAINT configuracao_inicio_atualizado_por_fkey FOREIGN KEY (atualizado_por) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- Name: auditoria fk_auditoria_usuario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditoria
    ADD CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;


--
-- Name: conteudo_tags fk_conteudo_tags_conteudo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudo_tags
    ADD CONSTRAINT fk_conteudo_tags_conteudo FOREIGN KEY (conteudo_id) REFERENCES public.conteudos(id) ON DELETE CASCADE;


--
-- Name: conteudo_tags fk_conteudo_tags_tag; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudo_tags
    ADD CONSTRAINT fk_conteudo_tags_tag FOREIGN KEY (tag_id) REFERENCES public.tags(id) ON DELETE CASCADE;


--
-- Name: conteudos fk_conteudos_autor; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudos
    ADD CONSTRAINT fk_conteudos_autor FOREIGN KEY (autor_id) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- Name: conteudos fk_conteudos_categoria; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conteudos
    ADD CONSTRAINT fk_conteudos_categoria FOREIGN KEY (categoria_id) REFERENCES public.categorias(id) ON DELETE RESTRICT;


--
-- Name: eventos fk_eventos_autor; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.eventos
    ADD CONSTRAINT fk_eventos_autor FOREIGN KEY (autor_id) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- Name: midias fk_midias_usuario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.midias
    ADD CONSTRAINT fk_midias_usuario FOREIGN KEY (enviado_por) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- PostgreSQL database dump complete
--

\unrestrict 2o2zFR9c9OC3aXQjrhhoMsn3NKUuxAUg2aLGASx1Ka5TO3KmBwUt2X0kRR8aYY2


-- Contas fictícias para avaliação; dados pessoais e hashes originais removidos.
INSERT INTO public.usuarios (id,nome,email,senha_hash,perfil,status) VALUES (1,'Administrador Portal SI','avaliador@portal-si.test','$2y$10$/kyeN94Klqe8vHRPTRCVWuLFB9R/rplL3bsJTFErBiCIKtET.XY5a','admin','ativo');
INSERT INTO public.usuarios (id,nome,email,senha_hash,perfil,status) VALUES (2,'Conta de demonstração 2','conta2@portal-si.test','$2y$10$/kyeN94Klqe8vHRPTRCVWuLFB9R/rplL3bsJTFErBiCIKtET.XY5a','aluno','ativo');
INSERT INTO public.usuarios (id,nome,email,senha_hash,perfil,status) VALUES (3,'Conta de demonstração 3','conta3@portal-si.test','$2y$10$/kyeN94Klqe8vHRPTRCVWuLFB9R/rplL3bsJTFErBiCIKtET.XY5a','aluno','ativo');
INSERT INTO public.usuarios (id,nome,email,senha_hash,perfil,status) VALUES (6,'Conta de demonstração 4','conta4@portal-si.test','$2y$10$/kyeN94Klqe8vHRPTRCVWuLFB9R/rplL3bsJTFErBiCIKtET.XY5a','aluno','ativo');
INSERT INTO public.usuarios (id,nome,email,senha_hash,perfil,status) VALUES (46,'Conta de demonstração 5','conta5@portal-si.test','$2y$10$/kyeN94Klqe8vHRPTRCVWuLFB9R/rplL3bsJTFErBiCIKtET.XY5a','aluno','ativo');
--
-- PostgreSQL database dump
--

\restrict kpIZvaAHuoH36zzTnUAlWWUoVhFZ9HJMHcBMGdz28mr3eyWKnTu7XVvJNOJ6TjN

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: categorias; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categorias (id, nome, slug, descricao, icone, ordem, ativo, criado_em, atualizado_em) FROM stdin;
1	Notícias	noticias	Comunicados e notícias do curso.	newspaper	1	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
2	Eventos	eventos	Palestras, encontros e atividades acadêmicas.	calendar	2	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
3	Projetos de Extensão	projetos-extensao	Projetos e ações de extensão universitária.	users	3	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
4	Pesquisa e Inovação	pesquisa-inovacao	Iniciativas de pesquisa, tecnologia e inovação.	flask	4	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
5	Vida Acadêmica	vida-academica	Informações relevantes para estudantes e docentes.	graduation-cap	5	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
6	Oportunidades	oportunidades	Bolsas, estágios, empregos e oportunidades.	briefcase	6	t	2026-09-24 20:15:51.130723-03	2026-09-24 20:15:51.130723-03
\.


--
-- Data for Name: configuracao_inicio; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.configuracao_inicio (id, etiqueta, titulo, descricao, botao_texto, botao_destino, imagem, imagem_alt, imagem_posicao, atualizado_em, atualizado_por) FROM stdin;
1	Portal de Comunicação SI · ESUCRI	Sistemas de Informação em pauta.	Notícias, projetos e encontros que conectam a comunidade acadêmica. Acompanhe o que acontece no curso e participe da vida no campus.	Explorar publicações	publicacoes	public/assets/images/inicio-campus.jpg	Estudantes trabalhando em equipe com seus computadores.	50	2026-11-11 21:12:54.475233-03	\N
\.


--
-- Data for Name: conteudos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.conteudos (id, titulo, slug, resumo, corpo, imagem_capa, link_youtube, categoria_id, autor_id, status, destaque, publicado_em, criado_em, atualizado_em, imagens, destaque_em) FROM stdin;
90	Projetos integradores conectam banco de dados, interface e programação	projetos-integradores-conectam-banco-de-dados-interface-e-programac-ao	Uma mesma aplicação reúne conhecimentos de diferentes disciplinas e evidencia a importância de documentar decisões.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\r\n\r\nA construção de um sistema completo exige que formulários, regras de negócio e persistência de dados funcionem em conjunto. No exercício proposto, cada equipe acompanha um fluxo desde a entrada do usuário até a consulta ao banco.\r\n\r\nAo final, os grupos demonstram o projeto, apresentam o modelo de dados e explicam como lidam com entradas inválidas. A documentação registra tanto as funcionalidades concluídas quanto os pontos que ainda precisam evoluir.\r\n\r\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/95d3d4edbe66ad4274ea97862227c50b.jpg	\N	1	1	publicado	t	2026-11-08 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 21:25:39.336876-03	["public/uploads/publicacoes/95d3d4edbe66ad4274ea97862227c50b.jpg", "public/uploads/publicacoes/baf094289587571528667ba8f01951c9.jpg"]	2026-11-11 21:25:39.338706-03
91	Internet das coisas aproxima sensores e aplicações web	demo-noticia-05	Experimentos exploram a coleta de dados e a apresentação de informações em painéis acessíveis pelo navegador.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nSensores simulados enviam leituras que podem ser acompanhadas em uma interface web. Os exemplos ajudam a visualizar como eventos do mundo físico podem ser representados, armazenados e interpretados por um sistema.\n\nA atividade percorre o fluxo de coleta, transmissão, validação e exibição. O grupo também discute interrupções de conexão, valores inesperados e a importância de indicar quando uma leitura foi recebida.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/e06e5924ea6d198b4d7a33a683ad3e1a.jpg	\N	1	1	publicado	f	2026-11-07 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/e06e5924ea6d198b4d7a33a683ad3e1a.jpg", "public/uploads/publicacoes/1ed1dfbf25ca3860e005dd8da07839d8.jpg"]	\N
92	Segurança da informação começa nas pequenas decisões do projeto	demo-noticia-06	Senhas protegidas, permissões e validação de entrada fazem parte de uma revisão dedicada às aplicações desenvolvidas no curso.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nA revisão organiza perguntas simples: quem pode acessar cada recurso, quais dados podem ser enviados e como o sistema responde a uma tentativa inválida. A análise considera tanto a interface quanto as verificações realizadas no servidor.\n\nOs exemplos incluem consultas parametrizadas, controle de sessão e cuidados com arquivos enviados. A proposta é reconhecer que a proteção das informações depende de várias decisões combinadas ao longo do desenvolvimento.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/ed9d76c854deae9d834f299570b80012.jpg	\N	1	1	publicado	f	2026-11-06 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/ed9d76c854deae9d834f299570b80012.jpg", "public/uploads/publicacoes/631159e51d648d7fea26133be6f4eeef.jpg"]	\N
88	Laboratório de desenvolvimento ganha uma nova rotina de colaboração	demo-noticia-02	Revisões de código e pequenos encontros de acompanhamento ajudam a organizar a evolução dos projetos acadêmicos.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nA proposta de trabalho combina tarefas curtas, acompanhamento visual e revisão entre colegas. Antes de integrar uma funcionalidade, o grupo verifica a clareza do código, o comportamento esperado e as possíveis situações de erro.\n\nAlém da implementação, os estudantes registram decisões técnicas e compartilham o que aprenderam. O objetivo do exercício é tornar a colaboração parte da rotina, da primeira ideia à entrega de uma versão funcional.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/9bb34c8a3413da820b0f6fda9b0361e3.jpg	\N	1	1	publicado	t	2026-11-10 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:16:49.867301-03	["public/uploads/publicacoes/9bb34c8a3413da820b0f6fda9b0361e3.jpg", "public/uploads/publicacoes/7a4ef0a299b95ed867c94eb91f240988.jpg"]	2026-11-11 20:16:49.871152-03
93	Visualização de dados transforma números em perguntas melhores	demo-noticia-07	Gráficos e painéis de demonstração ajudam a comparar cenários e a comunicar resultados com mais clareza.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nAs equipes trabalham com conjuntos de dados fictícios para escolher indicadores, identificar tendências e comparar períodos. Antes de construir um gráfico, cada grupo define qual pergunta precisa ser respondida.\n\nA apresentação considera escala, contraste, legenda e contexto. O exercício mostra como uma escolha visual pode facilitar a leitura ou criar uma interpretação equivocada dos números.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/ee0bbf8379973b65fffc3f4f9d604502.jpg	\N	1	1	publicado	f	2026-11-05 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/ee0bbf8379973b65fffc3f4f9d604502.jpg", "public/uploads/publicacoes/b31d863beeb730fa6916f1139de7fab9.jpg"]	\N
94	Extensão universitária abre espaço para compartilhar conhecimento digital	demo-noticia-08	Oficinas de demonstração aproximam temas de tecnologia de situações comuns de estudo, trabalho e comunicação.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nAs propostas incluem organização de arquivos, proteção de contas e uso de ferramentas de colaboração. O planejamento das oficinas prioriza exemplos práticos e linguagem acessível a pessoas com diferentes níveis de familiaridade com tecnologia.\n\nCada atividade prevê uma breve explicação, um exercício acompanhado e tempo para perguntas. Os estudantes também discutem como avaliar se o conteúdo apresentado realmente ajudou o público a realizar uma tarefa.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/e0b833e5498f1ad46890aadaad4d7e62.jpg	\N	1	1	publicado	f	2026-11-04 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/e0b833e5498f1ad46890aadaad4d7e62.jpg", "public/uploads/publicacoes/cafe28d0a4a1ebe2782f35a699801c67.jpg"]	\N
95	Experiência do usuário orienta a revisão das telas dos projetos	demo-noticia-09	Testes de navegação revelam oportunidades para simplificar formulários e tornar as informações mais fáceis de encontrar.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nOs participantes recebem uma tarefa e tentam concluí-la sem instruções detalhadas. Enquanto isso, a equipe observa dúvidas, cliques e caminhos percorridos para entender onde a interface exige esforço desnecessário.\n\nAs revisões incluem rótulos mais claros, mensagens de erro específicas e melhor organização do conteúdo. O objetivo é melhorar a compreensão das telas sem depender apenas de novos elementos visuais.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/41034c21005c1bdbcee04215fa94e2b4.jpg	\N	1	1	publicado	f	2026-11-03 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/41034c21005c1bdbcee04215fa94e2b4.jpg", "public/uploads/publicacoes/33a54750d66e36b6fdf0abb58d1e2b6b.jpg"]	\N
96	Portfólios reúnem trajetórias, projetos e aprendizados do semestre	demo-noticia-10	Uma seleção de trabalhos permite apresentar resultados e explicar as decisões por trás de cada solução.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nOs estudantes organizam projetos, registros de evolução e pequenas demonstrações em um portfólio. Cada trabalho reúne contexto, responsabilidades da equipe, tecnologias utilizadas e uma descrição do resultado alcançado.\n\nA proposta valoriza a capacidade de explicar escolhas e reconhecer limitações. Mais do que reunir imagens de telas, o portfólio mostra como cada pessoa participa da construção e da melhoria de um projeto.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/b73295ba2c96958a799d4a182c00668d.jpg	\N	1	1	publicado	f	2026-11-02 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/b73295ba2c96958a799d4a182c00668d.jpg", "public/uploads/publicacoes/28f3ba74ff548685ad52f1bca2dc7ff7.jpg"]	\N
98	Mostra de projetos abre as portas para a comunidade acadêmica	demo-evento-02	Protótipos e demonstrações aproximam visitantes das soluções desenvolvidas pelas equipes.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 23 de novembro de 2026, das 19h às 21h30.\nLocal de exemplo: Espaço de convivência do campus.\n\nOs grupos apresentam o problema escolhido, demonstram a aplicação e conversam sobre o processo de desenvolvimento. O público pode testar as telas e compartilhar sugestões de melhoria.\n\nComo participar\nA circulação é livre nesta programação fictícia. Reserve tempo para conhecer mais de um projeto e conversar com as equipes.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/affb94661aca2f0e3b5ec11d17563dac.jpg	\N	2	1	publicado	t	2026-11-10 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:16:49.867301-03	["public/uploads/publicacoes/affb94661aca2f0e3b5ec11d17563dac.jpg", "public/uploads/publicacoes/c87f0df9394a33a7ed4cf612fa16beab.jpg"]	2026-11-11 20:16:49.871956-03
100	Maratona de programação desafia equipes a resolver problemas em conjunto	demo-evento-04	Uma noite de desafios combina raciocínio lógico, organização do tempo e colaboração.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 25 de novembro de 2026, das 18h30 às 22h.\nLocal de exemplo: Laboratório de desenvolvimento.\n\nAs equipes recebem problemas de dificuldade progressiva e podem discutir estratégias antes de implementar uma solução. A proposta de exemplo valoriza a clareza do raciocínio e a colaboração entre participantes.\n\nComo participar\nForme uma equipe ou participe da organização de grupos no início da atividade. Leve notebook e prepare seu ambiente de programação.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/1370c309ad86fdb2a701d50d8d6659c0.jpg	\N	2	1	publicado	f	2026-11-08 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/1370c309ad86fdb2a701d50d8d6659c0.jpg", "public/uploads/publicacoes/9c2db412b539bf696826c023cdf17a34.jpg"]	\N
101	Encontro sobre carreira em tecnologia aproxima estudantes e profissionais	demo-evento-05	Uma roda de conversa explora áreas de atuação, primeiros projetos e preparação para processos seletivos.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 26 de novembro de 2026, das 19h30 às 21h.\nLocal de exemplo: Auditório do campus.\n\nA programação fictícia prevê uma conversa aberta sobre desenvolvimento, dados, infraestrutura e análise de sistemas. O encontro também aborda como apresentar experiências acadêmicas em entrevistas.\n\nComo participar\nPrepare perguntas e, se desejar, leve uma versão do seu currículo ou portfólio para acompanhar os exemplos discutidos.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/b7f61a2614734c4f8fdc7b47da7996a5.jpg	\N	2	1	publicado	f	2026-11-07 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/b7f61a2614734c4f8fdc7b47da7996a5.jpg", "public/uploads/publicacoes/686ae2657d976d4aece62bea78676cef.jpg"]	\N
102	Workshop de dados apresenta o caminho da planilha ao painel	demo-evento-06	Atividade prática mostra como organizar informações e comunicar indicadores em uma visualização.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 27 de novembro de 2026, das 19h às 21h30.\nLocal de exemplo: Laboratório de informática.\n\nO exercício utiliza dados fictícios para demonstrar etapas de limpeza, organização e exploração. Os participantes constroem um pequeno painel e discutem quais perguntas cada indicador pode responder.\n\nComo participar\nLeve notebook. Não é necessário utilizar dados pessoais ou informações de empresas para acompanhar a atividade.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/ebf955b430ffd1cef96f5f7e4b629572.jpg	\N	2	1	publicado	f	2026-11-06 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/ebf955b430ffd1cef96f5f7e4b629572.jpg", "public/uploads/publicacoes/1c61dd830fbc5b815206e4d5986651a6.jpg"]	\N
103	Café com pesquisa abre conversa sobre iniciação científica	demo-evento-07	Estudantes podem conhecer formatos de projeto e discutir como uma boa pergunta se transforma em investigação.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 30 de novembro de 2026, das 18h30 às 20h.\nLocal de exemplo: Sala de atividades acadêmicas.\n\nO encontro de exemplo apresenta temas de pesquisa relacionados a software, educação, dados e interação com tecnologia. A conversa percorre pergunta de pesquisa, objetivos e planejamento de pequenas etapas.\n\nComo participar\nA atividade é indicada a quem deseja entender melhor o processo de pesquisa. Não é necessário ter uma proposta pronta.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/36f4fefc728a316eae9f89f333840a03.jpg	\N	2	1	publicado	f	2026-11-05 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/36f4fefc728a316eae9f89f333840a03.jpg", "public/uploads/publicacoes/5c158ab56babc215416fea4e8291189c.jpg"]	\N
104	Conexão com a comunidade inspira uma noite de extensão	demo-evento-08	Experiências e propostas mostram como projetos de tecnologia podem dialogar com necessidades do entorno.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 1º de dezembro de 2026, das 19h às 21h.\nLocal de exemplo: Espaço de convivência do campus.\n\nA programação de demonstração reúne relatos fictícios de oficinas e iniciativas digitais. As equipes apresentam ideias e discutem como ouvir o público antes de escolher uma solução.\n\nComo participar\nParticipe com perguntas e sugestões. O encontro prioriza troca de experiências e construção coletiva de propostas.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/5d7ac09f84672b49f3c09b26420f9822.jpg	\N	2	1	publicado	f	2026-11-04 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/5d7ac09f84672b49f3c09b26420f9822.jpg", "public/uploads/publicacoes/0bbb250e26127490a4263b1335fdf127.jpg"]	\N
105	Encontro de equipes debate colaboração no desenvolvimento de software	demo-evento-09	Revisão de código, divisão de tarefas e comunicação entram na pauta de uma atividade prática.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 2 de dezembro de 2026, das 19h às 21h.\nLocal de exemplo: Laboratório de desenvolvimento.\n\nA oficina utiliza situações simuladas de um projeto para discutir conflitos de integração, mudanças de requisitos e acompanhamento de tarefas. Cada grupo propõe uma rotina de colaboração e apresenta suas escolhas.\n\nComo participar\nLeve notebook e tenha uma conta de teste em uma ferramenta de versionamento, caso queira acompanhar os exemplos práticos.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/55c3527a11c53552a5e3188d504762a2.jpg	\N	2	1	publicado	f	2026-11-03 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/55c3527a11c53552a5e3188d504762a2.jpg", "public/uploads/publicacoes/4bff6b07196840c55e5dd40564c1094d.jpg"]	\N
106	Feira de ideias encerra a programação com apresentações e troca de experiências	demo-evento-10	Projetos em diferentes etapas dividem o espaço com conversas sobre os próximos passos do aprendizado.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 3 de dezembro de 2026, das 19h às 22h.\nLocal de exemplo: Auditório e espaço de convivência.\n\nA feira fictícia combina apresentações curtas e demonstrações abertas. A proposta permite que cada equipe apresente o que aprendeu, os desafios encontrados e as possibilidades de continuidade.\n\nComo participar\nA programação de exemplo é voltada à comunidade acadêmica. Leve suas perguntas e aproveite para conhecer projetos de outras equipes.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/e43e65b51abb232788f5f8c02d832231.jpg	\N	2	1	publicado	f	2026-11-02 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:02:24.522392-03	["public/uploads/publicacoes/e43e65b51abb232788f5f8c02d832231.jpg", "public/uploads/publicacoes/57d795c45e81e37dd831afe00fbe6e8b.jpg"]	\N
87	Estudantes de SI transformam desafios da comunidade em soluções digitais	demo-noticia-01	Projetos desenvolvidos em equipe mostram como a tecnologia pode aproximar o aprendizado das necessidades do dia a dia.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nOs grupos apresentam protótipos de serviços digitais voltados à organização de atividades, ao acesso à informação e à colaboração entre pessoas. Cada proposta parte de um problema cotidiano e descreve quem será beneficiado pela solução.\n\nO processo passa por entrevistas simuladas, definição de requisitos, desenho das telas e implementação de uma primeira versão. A apresentação reúne demonstrações práticas e uma conversa sobre o que mudou durante o desenvolvimento.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/c2dbadb2eb3eb7be4720db3652c24a2c.jpg	\N	1	1	publicado	t	2026-11-11 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:14:18.205632-03	["public/uploads/publicacoes/c2dbadb2eb3eb7be4720db3652c24a2c.jpg", "public/uploads/publicacoes/a554b3a160a30e2b25b790a3585f3957.jpg", "public/uploads/publicacoes/824e6750f033e9191b5cf80d0344542c.jpg"]	2026-11-11 20:02:24.522392-03
97	Semana de Tecnologia reúne ideias, projetos e novas conexões	demo-evento-01	Uma programação de palestras, oficinas e apresentações convida a explorar diferentes caminhos em Sistemas de Informação.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 18 a 20 de novembro de 2026, das 19h às 22h.\nLocal de exemplo: Auditório e laboratórios do campus.\n\nA programação de exemplo combina conversas com profissionais, demonstrações de projetos e atividades práticas. Os participantes podem circular entre os temas e conhecer trabalhos em diferentes estágios de desenvolvimento.\n\nComo participar\nLeve um caderno ou dispositivo para anotações. Nas oficinas, um notebook pode facilitar o acompanhamento dos exercícios.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/40030634c2ec07c00f1b4513fb95bcc3.jpg	\N	2	1	publicado	t	2026-11-11 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:14:18.205632-03	["public/uploads/publicacoes/40030634c2ec07c00f1b4513fb95bcc3.jpg", "public/uploads/publicacoes/465f1ddf4587ea81e6fa950829d94fa1.jpg", "public/uploads/publicacoes/60a19a7a3c97ed3f6b1ff970ca36ce3a.jpg"]	2026-11-11 20:02:24.522392-03
89	Inteligência artificial entra no debate sobre formação e responsabilidade	demo-noticia-03	Atividades de estudo exploram aplicações da IA e discutem os cuidados necessários ao interpretar seus resultados.	Publicação de demonstração. O texto é fictício e foi criado para testar o portal; não representa uma notícia oficial da instituição.\n\nOs exemplos de aplicação incluem organização de informações, apoio à escrita e classificação de dados. As atividades destacam que uma resposta automatizada precisa ser conferida antes de orientar decisões ou ser incorporada a um produto.\n\nO debate também aborda privacidade, qualidade dos dados e limites das ferramentas. As equipes são convidadas a explicar quando a automação ajuda e quando é necessário manter uma avaliação humana mais cuidadosa.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/b6dc66cf29e244c29d610c8eca72fc85.jpg	\N	1	1	publicado	t	2026-11-09 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:16:49.867301-03	["public/uploads/publicacoes/b6dc66cf29e244c29d610c8eca72fc85.jpg", "public/uploads/publicacoes/1de06f8ae388053f8074d3f5d620ad0b.jpg"]	2026-11-11 20:16:49.871616-03
99	Oficina de interfaces acessíveis propõe olhar além da aparência	demo-evento-03	Exercícios de contraste, navegação por teclado e organização de conteúdo ajudam a revisar interfaces web.	Evento de demonstração. A programação abaixo é fictícia e serve apenas para testar o portal; não há inscrição ou atividade oficial associada a esta publicação.\n\nQuando: 24 de novembro de 2026, das 19h às 21h.\nLocal de exemplo: Laboratório de informática.\n\nA oficina de demonstração parte de uma página simples e analisa como diferentes pessoas acessam seus recursos. Os exercícios incluem foco visível, rótulos de formulário e leitura de informações sem depender apenas de cor.\n\nComo participar\nÉ recomendado levar notebook e ter familiaridade básica com HTML e CSS. Os exemplos são apresentados passo a passo.\n\nFotografias ilustrativas: Unsplash.	public/uploads/publicacoes/11c3be6bb3596a8e37489c26d3e34157.jpg	\N	2	1	publicado	t	2026-11-09 20:02:24-03	2026-11-11 20:02:24.522392-03	2026-11-11 20:16:49.867301-03	["public/uploads/publicacoes/11c3be6bb3596a8e37489c26d3e34157.jpg", "public/uploads/publicacoes/aa9ab3569c46ed91ac996c284487d951.jpg"]	2026-11-11 20:16:49.87218-03
\.


--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tags (id, nome, slug, criado_em, atualizado_em) FROM stdin;
\.


--
-- Data for Name: conteudo_tags; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.conteudo_tags (conteudo_id, tag_id, criado_em) FROM stdin;
\.


--
-- Data for Name: eventos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.eventos (id, titulo, descricao, data_inicio, data_fim, local, link_inscricao, autor_id, status, criado_em, atualizado_em) FROM stdin;
\.


--
-- Data for Name: midias; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.midias (id, nome_arquivo, caminho, tipo_mime, tamanho_bytes, enviado_por, criado_em) FROM stdin;
\.


--
-- Name: auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.auditoria_id_seq', 1, false);


--
-- Name: categorias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorias_id_seq', 6, true);


--
-- Name: conteudos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.conteudos_id_seq', 250, true);


--
-- Name: eventos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.eventos_id_seq', 1, false);


--
-- Name: midias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.midias_id_seq', 1, false);


--
-- Name: tags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tags_id_seq', 1, false);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 51, true);


--
-- PostgreSQL database dump complete
--

\unrestrict kpIZvaAHuoH36zzTnUAlWWUoVhFZ9HJMHcBMGdz28mr3eyWKnTu7XVvJNOJ6TjN
