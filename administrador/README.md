# Painel administrativo

Módulo administrativo isolado do Portal SI. Todo o código específico do painel está dentro de `administrador/`.

## Funcionalidades disponíveis

- autenticação exclusiva para usuários com perfil `admin` ou `editor`;
- encerramento de sessão, limite de inatividade e proteção CSRF;
- dashboard com indicadores de conteúdos e categorias;
- listagem paginada de conteúdos;
- cadastro com validação e geração de slug único;
- edição, publicação, arquivamento e destaque;
- exclusão por `POST`, com tela de confirmação;
- integração imediata dos conteúdos publicados com a Home do portal;
- layout responsivo sem dependência de JavaScript.

O envio de imagem de capa foi reservado para uma etapa posterior. A pasta `uploads/` já está isolada e bloqueia a execução de scripts.

## Acesso local

Com Apache e PostgreSQL iniciados no XAMPP, acesse:

```text
http://localhost/portal-si/administrador/login.php
```

Se o projeto estiver em outro caminho, ajuste a variável de ambiente `PORTAL_APP_BASE_URL`. O painel utiliza as mesmas configurações de banco do restante do portal.

## Organização

- `assets/css/`: estilos exclusivos do painel;
- `controllers/`: coordenação das requisições administrativas;
- `database/`: migração, verificação e documentação do banco;
- `includes/`: inicialização, autenticação e componentes compartilhados;
- `models/`: consultas parametrizadas ao PostgreSQL;
- `services/`: validações e regras de negócio;
- `tests/`: teste automatizado de integração;
- `uploads/`: espaço reservado para imagens de capa;
- `views/`: páginas e componentes visuais internos.

## Testes

Na raiz do projeto, execute:

```powershell
C:\xampp\php\php.exe administrador\database\verificar_banco.php
C:\xampp\php\php.exe administrador\tests\executar.php
```

O teste integrado cria registros temporários dentro de uma transação, percorre autenticação e CRUD, confirma a exibição na Home e executa `rollback` ao final. Os casos cobertos estão descritos em `docs/TESTES.md`.
