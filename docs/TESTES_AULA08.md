# Testes da Aula 08

Data da verificação: 24/09/2026

## Ambiente encontrado

- PostgreSQL local: disponível e em execução.
- Usuário usado na validação: `postgres`.
- Banco `portal_si`: já existia, mas possui uma estrutura antiga com apenas três tabelas e nomes de colunas diferentes do schema atual. Ele não foi alterado para preservar os dados existentes.
- Banco isolado de validação: `portal_si_aula08_test`.
- PHP 8.2.12 do XAMPP com Apache ativo e extensões `pdo_pgsql` e `pgsql` habilitadas.

## Validações executadas

### Estrutura do banco

O `database/schema.sql` foi executado com sucesso no banco isolado `portal_si_aula08_test`.

Resultado confirmado:

- 8 tabelas criadas: `usuarios`, `categorias`, `conteudos`, `tags`, `conteudo_tags`, `eventos`, `midias` e `auditoria`.
- 5 triggers de atualização de `atualizado_em` criados.
- Chaves estrangeiras, restrições e índices foram aceitos pelo PostgreSQL.

O `database/seed.sql` também foi executado com sucesso nesse banco de validação:

- 6 categorias ativas inseridas;
- usuário `admin@esucri.com.br` criado com perfil `admin`;
- hash do administrador confirmado no formato Bcrypt `$2y$10$`.

### Verificação estática do código

Os seguintes pontos foram encontrados nos arquivos da aplicação:

- Conexão PDO com prepared statements reais;
- Validação server-side de nome, e-mail, senha e confirmação;
- `password_hash()` no cadastro;
- `password_verify()` no login;
- `session_regenerate_id(true)` após autenticação;
- Limpeza de `$_SESSION`, expiração do cookie e `session_destroy()` no logout;
- `require_login()` no dashboard;
- Token CSRF nos formulários de cadastro e login;
- Escape HTML com `htmlspecialchars()`.

## Checklist dos 10 testes da aula

| Teste | Cenário | Situação |
|---|---|---|
| 01 | Cadastro válido | **PASSOU** - cadastro redirecionou para o login |
| 02 | E-mail inválido | **PASSOU** - cadastro bloqueado com mensagem de e-mail inválido |
| 03 | Senha menor que 8 caracteres | **PASSOU** - cadastro bloqueado pela validação server-side |
| 04 | E-mail duplicado | **PASSOU** - SQLSTATE `23505` convertido em mensagem amigável |
| 05 | Login com senha errada | **PASSOU** - mensagem genérica exibida |
| 06 | Login correto | **PASSOU** - dashboard exibiu nome do usuário autenticado |
| 07 | Acesso direto sem sessão | **PASSOU** - redirecionamento para login |
| 08 | Hash criptográfico no banco | **PASSOU** - usuário de teste e administrador com hash `$2y$10$` |
| 09 | Mutação do cookie PHPSESSID | **PASSOU** - identificador mudou após o login |
| 10 | Logout e bloqueio de retorno | **PASSOU** - sessão encerrada e dashboard protegido |

## Ambiente usado para os testes

Os testes foram executados em `http://localhost/portal-si` com o Apache do XAMPP e o banco PostgreSQL `portal_si`. Foi usado um e-mail de teste gerado para a execução, sem alterar a conta administrativa inicial.
