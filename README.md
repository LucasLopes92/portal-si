# Portal SI

Projeto de comunicação do curso de Sistemas de Informação, desenvolvido em PHP nativo com PostgreSQL e arquitetura Mini-MVC.

## Organização da aplicação

O fluxo principal segue esta ordem:

```text
Visitante abre /portal-si/ e vê a Home pública
          ↓
Seleciona "Fazer login"
          ↓
Usuário envia e-mail e senha
          ↓
AuthController
Recebe os campos e chama o serviço
          ↓
AuthService
Solicita a busca do usuário
          ↓
Usuario (Model)
Executa SELECT no PostgreSQL e retorna o usuário
          ↓
AuthService
Confere a senha e se a conta pode entrar
          ↓
AuthController
Estabelece a sessão com os helpers e redireciona conforme o perfil
          ↓
Conta admin: painel administrativo completo
Outras contas: Home autenticada, sem acesso administrativo
```

### Responsabilidades

- **Controller:** recebe as requisições do navegador, chama os serviços e decide respostas e redirecionamentos.
- **Service:** concentra as regras de negócio, como validação, autenticação e geração de hash.
- **Model:** concentra as operações de dados e os comandos SQL parametrizados via PDO.
- **View:** exibe formulários, mensagens e páginas HTML para o usuário.
- **Helpers:** reúnem funções compartilhadas de segurança, sessão, validação e escape de HTML.

Assim, uma alteração na consulta fica no Model, uma alteração na regra fica no Service e uma alteração na apresentação fica na View.

## Estrutura inicial

```text
portal-si/
├── public/                 # Única pasta exposta pelo servidor web
│   └── assets/             # CSS e imagens públicas
├── app/
│   ├── controllers/        # Orquestração das requisições
│   ├── services/           # Regras de negócio
│   ├── models/             # Acesso aos dados
│   ├── helpers/            # Funções auxiliares e segurança
│   └── views/              # Telas e templates
├── config/                 # Configurações da aplicação
├── database/               # Scripts schema.sql e seed.sql
└── docs/                   # Documentação complementar
```

## Home pública (Aula 09)

A página inicial está em `public/index.php`. No XAMPP, abra
`http://localhost/portal-si/`; o `index.php` da raiz redireciona para a Home.
O menu consulta as categorias
ativas do PostgreSQL e cada link filtra as publicações dessa categoria. A Home
mostra até nove conteúdos publicados, em ordem de data, e um estado vazio
quando ainda não há publicações. O cabeçalho e o rodapé ficam em
`app/views/layout/`, incluídos pela view `app/views/home/index.php`.
A Home é pública e mostra apenas as categorias acessíveis sem autenticação.
O botão "Fazer login" leva ao formulário de acesso. Uma conta com perfil
`admin` abre diretamente o painel administrativo completo; os demais perfis
retornam à Home sem acesso às rotas administrativas. Na tela do painel, a logo ESUCRI e o botão
"Ir para a Home" levam à Home mantendo a sessão ativa. Na Home, o botão
"Painel" permite retornar ao painel.

O CSS continua concentrado em `public/assets/css/style.css`, com adaptações
em 1024px e 768px. Ao rolar a Home, a faixa institucional e a marca saem da
área visível, enquanto o menu permanece fixo no topo em formato compacto.
A interface pública usa PHP, HTML e CSS, sem JavaScript.

As consultas usam os nomes de colunas do schema deste repositório: `ativo`
em `categorias` e `autor_id` em `conteudos`. Alguns exemplos da apostila usam
nomes diferentes. Para executar localmente, configure o PostgreSQL pela
`config/database.php` ou pelas variáveis `PORTAL_DB_*` e aplique
`database/schema.sql` e `database/seed.sql` se o banco ainda não estiver
preparado.

## Painel administrativo

O painel utiliza a mesma autenticação do portal e permite acesso somente ao
perfil `admin`. Os pontos de entrada HTTP ficam em `administrador/`; a lógica
segue a estrutura MVC existente em `app/`.

Para validar o banco e o fluxo administrativo:

```powershell
C:\xampp\php\php.exe database\verificar_banco.php
C:\xampp\php\php.exe tests\painel_admin.php
```

## Banco remoto

A conexao suporta PostgreSQL no Aiven com SSL e credenciais privadas, usando
o mesmo arquivo no Apache e no PHP CLI. Veja [configuracao do Aiven](docs/AIVEN.md).
