# Portal de Comunicação SI

Portal do curso de Sistemas de Informação das Faculdades ESUCRI, desenvolvido em Projeto de Extensão IV, 6ª fase, 2026/2. Entrega em 12/11/2026. Professor: Me. Jucemar Formigoni Candido.

| Integrante | Responsabilidade |
| --- | --- |
| Diogo Leonardo Arcenio | Back-end |
| Luis Henrique Serafim | Front-end |
| Lucas Lopes | Banco de dados |

## Funcionalidades

- Home pública e editorias Notícias e Eventos, com autoria, categoria, data, resumo e detalhe.
- Busca por palavra e data, filtros combinados e paginação de até nove publicações nas editorias.
- Manchete definida pela marcação de destaque mais recente; demais destaques em blocos menores.
- Cadastro de contas comuns, login, logout e painel exclusivo para perfil `admin`.
- CRUD de publicações com rascunho, publicação, arquivamento, destaque e exclusão.
- Filtro de categoria no gerenciamento: Todos, Notícias e Eventos.
- Seleção de várias imagens do computador e galeria com navegação. A primeira imagem é a capa.
- Configuração dos textos e da foto da Home, com prévia antes de salvar.
- Link opcional de vídeo do YouTube no detalhe.

A versão atual utiliza PHP, HTML e CSS, sem JavaScript próprio. Para visualizar imagens selecionadas antes de salvar, use **Enviar e ver imagens**. No editor da Home, use **Atualizar prévia**; **Salvar e publicar** aplica a configuração ao portal. A busca pública é aplicada pelo envio do formulário.

## Requisitos

Ambiente verificado: Windows, Apache/XAMPP, PHP 8.2.12 e PostgreSQL 18.4 local na porta 5432. No PHP, habilite `pdo_pgsql`, `pgsql`, `mbstring` e `fileinfo`. O PostgreSQL precisa disponibilizar a extensão `citext`. O Apache deve permitir as regras `.htaccess` (`AllowOverride All`) e escrita em `public/uploads/`.

## Conexão atual do projeto

O portal roda no XAMPP em `http://localhost/portal-si/` e utiliza o PostgreSQL do servidor `painel.formasoftware.cloud:5455`, na base `porta-si`. As 20 publicações, categorias e configuração da Home já estão nessa base. As fotos continuam em `public/uploads/` no computador que executa o portal.

A conexão está no arquivo privado `config/database-uri.local.txt`, ignorado pelo Git. O administrador do portal é `admin@esucri.com.br`, com a senha definida para a demonstração. A configuração anterior do banco local foi preservada em um backup privado dentro de `config/`. Não é necessário importar novamente o SQL no banco remoto para usar esta instalação.

## Instalação com os dados de demonstração

1. Copie a pasta do código para `C:\xampp\htdocs\portal-si` e inicie o Apache.
2. No PostgreSQL, crie uma base **vazia** chamada `portal_si`.
3. Restaure `Banco/portal_si_demonstracao.sql`, incluído no pacote de entrega, nessa base. O arquivo reúne estrutura e dados; não aplique o seed depois dele.
4. Crie `config/database-uri.local.txt`, usando sua própria senha do PostgreSQL:

```text
postgresql://postgres:SUA_SENHA@127.0.0.1:5432/portal_si?sslmode=disable
```

Se a senha contiver caracteres reservados de URL, codifique-os (`@` vira `%40`, por exemplo). Não publique esse arquivo. Alternativamente, configure as variáveis `PORTAL_DB_HOST`, `PORTAL_DB_PORT`, `PORTAL_DB_NAME`, `PORTAL_DB_USER`, `PORTAL_DB_PASSWORD` e `PORTAL_DB_SSLMODE` no ambiente do Apache e do PHP CLI.

5. Preserve as pastas `public/uploads/publicacoes/` e `public/uploads/inicio/` do pacote; o SQL referencia os arquivos pelos caminhos relativos.
6. Abra `http://localhost/portal-si/`.
7. Para entrar no painel, use o acesso de demonstração indicado em `ACESSO_AVALIADOR.txt`, entregue separadamente do repositório.

Exemplo de restauração no PowerShell, ajustando o caminho do pacote:

```powershell
& 'C:\Program Files\PostgreSQL\18\bin\createdb.exe' -U postgres portal_si
& 'C:\Program Files\PostgreSQL\18\bin\psql.exe' -U postgres -d portal_si -v ON_ERROR_STOP=1 -f 'C:\caminho\Banco\portal_si_demonstracao.sql'
```

O dump contém 20 publicações fictícias (10 notícias e 10 eventos), seis categorias, configuração da Home e contas anonimizadas para avaliação. As contas e senhas do ambiente original não integram o dump. A conta do avaliador funciona no banco restaurado; não altera a conta administrativa já utilizada pela equipe.

## Instalação somente da estrutura

Para começar sem publicações, crie uma base vazia e aplique `database/schema.sql`. Ele cria nove tabelas, índices, restrições, gatilhos e a configuração padrão da Home. `database/seed.sql` inclui seis categorias e um administrador inicial; use-o somente em instalação controlada, pois ele atualiza a conta com o mesmo e-mail. Para avaliação, prefira o dump de demonstração e suas credenciais conhecidas.

`CREATE TABLE IF NOT EXISTS` não atualiza colunas de bases antigas. Para atualizar uma instalação anterior, faça backup e aplique as três migrações em `database/migrations/`: galeria, destaques e configuração da Home. Não execute novamente o schema sobre o banco em uso como substituto dessas migrações.

## Organização

| Pasta ou arquivo | Função |
| --- | --- |
| `index.php` | Redireciona para a Home pública |
| `public/` | Entrada pública, estilos e imagens |
| `administrador/` | Rotas administrativas protegidas |
| `app/controllers/` | Recebe requisições e define respostas |
| `app/services/` | Validação e regras de negócio |
| `app/models/` | Consultas parametrizadas por PDO |
| `app/views/` | Interface pública e administrativa |
| `app/helpers/` | Escape, sessão, autorização e utilitários |
| `config/` | Configuração de conexão e sessão |
| `database/` | Schema, seed, migrações e verificação |
| `tests/` | Suítes de integração e regressão |
| `docs/` | Roteiros e registros técnicos |

Fluxo: navegador → rota PHP → Controller → Service → Model/PDO → PostgreSQL → View. As rotas do painel conferem sessão e perfil `admin`; perfis `aluno` e `editor` utilizam a área pública autenticada.

## Validação

Execute na raiz do projeto:

```powershell
& 'C:\xampp\php\php.exe' database\verificar_banco.php
& 'C:\xampp\php\php.exe' tests\painel_admin.php
& 'C:\xampp\php\php.exe' tests\destaques.php
& 'C:\xampp\php\php.exe' tests\configuracao_inicio.php
```

As suítes criam dados temporários e fazem rollback ou restauração ao terminar. O relatório da entrega identifica os testes efetivamente executados; aprovação automatizada não equivale à aceitação pessoal da equipe.

## Segurança e arquivos

Senhas usam `password_hash` e `password_verify`; formulários mutáveis exigem CSRF, sessões são regeneradas após login, saídas textuais recebem escape e consultas usam parâmetros. Uploads aceitam JPG, PNG, WebP e GIF: até dez imagens por publicação, 2 MB por arquivo, 6 MB por envio e 25 megapixels por imagem, com conferência de MIME e dimensões. Arquivos recebem nomes aleatórios e proteção do Apache. Ao remover imagens ou excluir uma publicação, os arquivos gerenciados correspondentes são removidos.

O código é servido no caminho XAMPP indicado acima; não se deve afirmar que apenas `public/` fica exposta nessa instalação. As regras `.htaccess` precisam permanecer no pacote. Credenciais `config/*.local.*`, backups e uploads não são versionados. O pacote completo inclui as fotos necessárias à demonstração; um clone do Git sozinho não as contém.

## Limitações e continuidade

O painel ainda não oferece gestão própria de categorias, tags, agenda estruturada, mídias ou auditoria automática. A categoria pública Eventos usa registros em `conteudos`; a tabela `eventos` é uma estrutura para continuidade. A galeria atual persiste em `conteudos.imagens` (JSONB), sem utilizar a tabela `midias`. O portal depende de Apache/PHP e PostgreSQL em execução; a configuração remota Aiven não é necessária para a demonstração local.

Os recursos de mídia são adicionais ao escopo textual exigido na Etapa 2. Permanecem como continuidade a ampliação dos testes de usabilidade, hospedagem, monitoramento e as interfaces auxiliares.

Fotografias de demonstração: Unsplash, com origens em [docs/DEMO_PUBLICACOES.md](docs/DEMO_PUBLICACOES.md). Foto inicial: `https://images.unsplash.com/photo-1522071820081-009f0129c71c`. Licença: `https://unsplash.com/license`.
