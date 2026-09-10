# Portal de Comunicação SI

Portal acadêmico em PHP nativo, PostgreSQL e arquitetura Mini-MVC, estruturado conforme a Aula 06 de Projeto de Extensão IV.

## Preparação

1. Crie o banco `portal_si` no PostgreSQL.
2. Execute `database/schema.sql` e depois `database/seed.sql`.
3. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASSWORD` no ambiente do Apache/PHP. Os valores padrão atendem ao banco local, exceto a senha, que permanece vazia por segurança.
4. Configure o DocumentRoot do Apache para a pasta `public/`.
5. Habilite `mod_rewrite` e acesse o endereço configurado.

No XAMPP, o VirtualHost deve apontar para `C:/xampp/htdocs/portal-si/public`, e não para a raiz do projeto. Caso use `http://localhost/portal-si/public/`, a aplicação também funciona para homologação local.

Usuário de homologação: `admin@portalsi.local` / `admin123`. Altere a senha antes de qualquer uso real.

## Estrutura

- `app/Controllers`: fluxo HTTP e controle de acesso.
- `app/Models`: consultas PDO parametrizadas e regras de persistência.
- `app/Views`: interfaces públicas e administrativas.
- `config`: configuração sem credenciais expostas no código.
- `database`: DDL, índices, triggers e dados iniciais.
- `public`: única pasta pública do servidor.

## Segurança incluída

Senhas Bcrypt, sessões regeneradas no login, RBAC para admin/editor, token CSRF em formulários e saída HTML escapada contra XSS.
