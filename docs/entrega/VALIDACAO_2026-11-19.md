# Verificação da entrega - 19/11/2026

Este registro complementa as evidências históricas de 12/11/2026. As verificações abaixo foram executadas sobre o ZIP extraído em uma pasta temporária e restaurado em um banco PostgreSQL local isolado. O banco de teste foi removido ao concluir; o banco da equipe foi preservado.

| Verificação | Resultado |
| --- | --- |
| Integridade do ZIP e manifesto SHA-256 de cada arquivo | Aprovado |
| Restauração de `Banco/portal_si_demonstracao.sql` com `ON_ERROR_STOP=1` | Aprovado |
| Estrutura obrigatória do banco (`database/verificar_banco.php`) | Aprovado: nove tabelas |
| Publicações da demonstração | 20 presentes |
| Imagens de publicações e Home | 43 referências encontradas no pacote |
| Sintaxe PHP | 56 arquivos aprovados |
| `tests/painel_admin.php` | 13 testes aprovados |
| `tests/destaques.php` | Aprovado |
| `tests/configuracao_inicio.php` | Aprovado |
| HTTP: Home, Notícias, Eventos, busca, paginação, detalhe, CSS e imagem padrão | Aprovado no servidor temporário com a cópia restaurada |
| HTTP: bloqueio do painel sem login, login de avaliador, lista de conteúdos, editor da Home e logout | Aprovado no servidor temporário com a cópia restaurada |
| Conta de avaliador no banco remoto temporário | Criada; senha verificada e autenticação pelo `AuthService` aprovada; contas anteriores preservadas |
| Slides | 16 páginas fornecidas pela equipe, renderizadas e inspecionadas; arquivo preservado |
| Planilha, linha DSL | GitHub existente, site opcional vazio, README `S`, Aiven `N`; valores conferidos após a correção |

Os [logs desta execução](Verificacao_2026-11-19/) acompanham a entrega. Os registros em `Evidencias/` continuam identificados como históricos.

O Apache do XAMPP não estava ouvindo na porta 80 durante esta conferência. A inicialização automática foi bloqueada pela política de execução. O fluxo HTTP foi validado em um servidor PHP temporário com a cópia restaurada; isso não é uma inspeção visual do portal no Apache. Antes da demonstração, inicie o Apache pelo XAMPP.

O navegador de inspeção estava indisponível. A planilha foi conferida pelos valores e metadados de formatação da API; o campo Aiven ficou com `N`, alinhado ao centro, sem a explicação longa anterior.

A apresentação ao vivo e a divisão de falas cabem à equipe. O repositório pessoal de Lucas foi mantido conforme pedido da equipe, apesar da orientação do professor por uma conta do grupo.
