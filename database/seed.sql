INSERT INTO usuarios (nome,email,senha_hash,perfil) VALUES
('Administrador Portal SI','admin@portalsi.local','$2y$10$68gzQAabwNyF0ySAB681nuf5IS6jZY3aQYuPn.wdyrDpxsB2FNGhK','admin');

INSERT INTO categorias (nome,slug,descricao,icone,ordem) VALUES
('Notícias','noticias','Comunicados e notícias do curso','newspaper',1),
('Extensão','extensao','Projetos de impacto social','groups',2),
('Eventos','eventos','Agenda acadêmica','calendar',3);

INSERT INTO conteudos (titulo,slug,resumo,corpo,categoria_id,autor_id,status,destaque,publicado_em) VALUES
('Portal de Comunicação SI está no ar','portal-de-comunicacao-si-esta-no-ar','Conheça o novo espaço de comunicação do curso de Sistemas de Informação.','Este portal centraliza notícias, eventos, projetos e conteúdos relevantes para a comunidade acadêmica.\n\nAcompanhe as publicações e participe das ações de extensão.',1,1,'publicado',true,NOW());

INSERT INTO eventos (titulo,descricao,data_inicio,local,autor_id,status) VALUES
('Encontro de Projetos de Extensão','Apresentação de iniciativas e integração com a comunidade.',NOW()+INTERVAL '14 days','Auditório ESUCRI',1,'publicado');
