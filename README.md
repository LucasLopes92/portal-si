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
mostra os conteúdos publicados, do mais recente para o mais antigo, e um estado vazio
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
A interface pública usa PHP, HTML e CSS. A galeria utiliza JavaScript para
navegação por setas, miniaturas e teclado; também permite deslizar as imagens
horizontalmente em telas de toque.

As consultas usam os nomes de colunas do schema deste repositório: `ativo`
em `categorias` e `autor_id` em `conteudos`. Alguns exemplos da apostila usam
nomes diferentes. Para executar localmente, configure o PostgreSQL pela
`config/database.php` ou pelas variáveis `PORTAL_DB_*` e aplique
`database/schema.sql` e `database/seed.sql` se o banco ainda não estiver
preparado.

## Painel administrativo

### Prioridade dos destaques

As editorias exibem até nove publicações por página, contando os destaques.
Publicações comuns aparecem em linhas compactas, com imagem à esquerda e
texto à direita. A paginação mantém os filtros e a ordem de leitura.

A Home e as editorias priorizam os conteúdos marcados como destaque. A última
marcação ocupa a manchete principal, mesmo que a publicação seja antiga. Os
outros destaques aparecem em blocos menores, com fundo suave na cor da categoria.
Ao continuar a paginação ou pesquisar, o destaque recebe uma identificação
discreta. Editar apenas o texto mantém a prioridade; desmarcar e salvar retira
o destaque, e marcar novamente registra uma nova prioridade.

O instante é mantido pelo banco em `conteudos.destaque_em`, sem depender do
relógio do navegador. A ordenação ocorre antes da paginação para preservar
todos os registros. A busca continua ordenada por data de publicação.
Em bancos anteriores, aplique `database/migrations/20261111_destaques.sql`.
Os destaques existentes usam inicialmente a última atualização conhecida,
pois não havia histórico da marcação. Verifique com `php tests/destaques.php`.

### Seleção e galeria de imagens

No cadastro e na edição, o campo **Imagens da publicação** permite selecionar
vários arquivos do computador, visualizar a seleção e remover itens antes de
salvar. A primeira imagem é usada como capa. No detalhe da publicação, a
galeria oferece botões anterior/próxima, miniaturas, teclas de seta e rolagem
horizontal em telas de toque.

São aceitos até dez arquivos JPG, PNG, WebP ou GIF por publicação, com até
2 MB por imagem, até 25 megapixels e até 6 MB em cada envio. A validação do
servidor confere o tipo real do arquivo. Os arquivos ficam em
`public/uploads/publicacoes/`, com nomes aleatórios e proteção do Apache.
Na edição é possível manter, adicionar ou marcar imagens para remoção.
A exclusão de uma imagem ou publicação também remove os arquivos gerados
pelo upload correspondente.

O banco local já recebeu a nova coluna `conteudos.imagens`. Para atualizar
outro banco existente, execute `database/migrations/20261111_galeria.sql` antes
de usar esta versão. Novas instalações usam o `database/schema.sql` atualizado.
Capas cadastradas anteriormente continuam disponíveis.

O painel utiliza a mesma autenticação do portal e permite acesso somente ao
perfil `admin`. Os pontos de entrada HTTP ficam em `administrador/`; a lógica
segue a estrutura MVC existente em `app/`.

Para validar o banco e o fluxo administrativo:

```powershell
C:\xampp\php\php.exe database\verificar_banco.php
C:\xampp\php\php.exe tests\painel_admin.php
```

## Banco remoto

### Editar a abertura da página inicial

No painel, abra **Página inicial** (`administrador/inicio.php`). É possível
alterar a chamada, o título, a apresentação, a foto, seu enquadramento vertical
e o texto e destino do botão. A prévia utiliza o mesmo componente e os mesmos
estilos da Home, com visualização de computador ou celular.

Digitar e selecionar uma foto atualiza somente a prévia. O botão **Salvar e
publicar** valida o formulário e aplica a configuração ao portal. Sem um novo
arquivo, a imagem atual é preservada. O upload aceita uma imagem de até 2 MB,
com validação do tipo real, armazenada em `public/uploads/inicio/`.

A configuração é mantida na tabela `configuracao_inicio`. Em bancos existentes,
aplique `database/migrations/20261111_configuracao_inicio.sql`; o banco local já
foi atualizado. Tanto a edição quanto a rota de prévia exigem perfil admin.
Verificação: `php tests/configuracao_inicio.php`.

A fotografia inicial é ilustrativa, obtida no Unsplash:
https://images.unsplash.com/photo-1522071820081-009f0129c71c
Licença: https://unsplash.com/license

### Conexão local atual

O ambiente local utiliza PostgreSQL em `127.0.0.1:5432`, no banco `portal_si`.
A conexão privada está em `config/database-uri.local.txt`, ignorado pelo Git.
Esse arquivo tem prioridade sobre `config/aiven-uri.local.txt`, mantendo a
configuração remota disponível. `PORTAL_DATABASE_URL` e as variáveis
`PORTAL_DB_*`, quando definidas, continuam tendo prioridade.

Para retornar ao Aiven, remova ou renomeie o arquivo privado
`config/database-uri.local.txt`. O banco local existente é preservado;
não é necessário executar novamente o schema ou o seed.

O menu público exibe, por enquanto, apenas **Notícias** e **Eventos**. Cada aba
tem sua apresentação e consulta as publicações cadastradas no painel
administrativo, exibindo título, resumo, categoria, autor e data do banco.
Somente conteúdos com status `publicado` e data de publicação preenchida
aparecem no portal. As alterações feitas pelo painel são refletidas ao
recarregar a página.

As publicações são listadas integralmente em fileiras de três no desktop,
da mais recente para a mais antiga. O botão **Buscar** abre um formulário
recolhível abaixo do cabeçalho, com palavra-chave e data de publicação.
Os filtros podem ser combinados e permanecem na URL; **Limpar** restaura a
lista completa da categoria. A data considera o fuso de São Paulo.

A conexao suporta PostgreSQL no Aiven com SSL e credenciais privadas, usando
o mesmo arquivo no Apache e no PHP CLI. Veja [configuracao do Aiven](docs/AIVEN.md).
