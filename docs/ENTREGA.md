# Entrega da primeira fase - Equipe DSL

Data da consolidação: 19/11/2026. Disciplina: Projeto de Extensão IV, Sistemas de Informação, Faculdades ESUCRI. Professor: Me. Jucemar Formigoni Candido.

## Material

- [Slides em PDF](../apresentacao/DSL_Slides_Portal-SI.pdf): arquivo fornecido pela equipe, com 16 slides, preservado integralmente.
- [Projeto compactado](../entrega/DSL_Projeto_Portal-SI.zip): código, fotos de demonstração, banco, instruções, apresentação e documentação. Extraia a pasta `portal-si` em `C:\xampp\htdocs`.
- [Instalação e conexão do banco](../README.md).
- [Acesso de avaliador](../ACESSO_AVALIADOR.txt).
- [SQL completo da demonstração](../Banco/portal_si_demonstracao.sql): estrutura e dados anonimizados, para restaurar em banco vazio próprio.
- [Documentação técnica, apêndices, manual e diagrama](entrega/Documentacao/).
- [Evidências da entrega anterior, de 12/11/2026](entrega/Evidencias/). São registros históricos; não representam uma nova execução de todos os casos.
- [Verificações desta consolidação](entrega/VALIDACAO_2026-11-19.md).

O banco remoto atual contém 20 publicações. O SQL local também contém 20 publicações e uma conta própria de avaliador. As imagens necessárias acompanham o Git e o ZIP. Novos uploads continuam ignorados pelo Git.

## Roteiro da demonstração ao vivo

1. Inicie Apache e confira a conexão com o PostgreSQL antes da aula. Para apresentar sem depender da rede, restaure o SQL em banco local vazio seguindo o README.
2. Abra a Home e navegue pelas editorias Notícias e Eventos.
3. Demonstre a busca, a paginação e o detalhe de uma publicação com imagens.
4. Entre com o acesso de avaliador e mostre o painel e a lista de conteúdos.
5. Cadastre uma publicação identificada como demonstração, confira sua exibição na área pública, edite, arquive e exclua apenas essa publicação.
6. Mostre a configuração da Home e use a prévia antes de publicar. Encerre a sessão.

A equipe deve apresentar ao vivo e distribuir a fala entre seus integrantes. O material e a preparação técnica não substituem essa atividade.

## Planilha e destino

Linha DSL: GitHub `https://github.com/LucasLopes92/portal-si`, site publicado em branco (opcional), README `S` e BD no Aiven `N`.

- [Planilha dos portais](https://docs.google.com/spreadsheets/d/13P0sHwBYpXxLZv_verx8KLt3GSplRpYdwJQyy3PS6nE/edit).
- [Pasta de slides do professor](https://drive.google.com/drive/folders/18S4A8j0KVmVM8qXXWV_0r3_5lo_mopR5).
- [Pasta de projetos compactados do professor](https://drive.google.com/drive/folders/1oNYCevU09GhpnteI__i_qJCuREljfhVH).

O repositório foi mantido na conta pessoal do Lucas por decisão da equipe em 19/11/2026. O professor solicitou conta da equipe; essa diferença permanece para alinhamento com ele. Não foi criada ou presumida uma conta alternativa.

O PDF preserva a data da apresentação original, 12/11/2026, e seus resultados históricos. A demonstração da Home usa envio de formulário para atualizar a prévia. O ambiente atual também permite usar o banco remoto temporário descrito no README.
