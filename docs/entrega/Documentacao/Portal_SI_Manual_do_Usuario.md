# Manual do usuário do Portal SI

Versão da entrega em 12/11/2026.


## Acessar o Portal SI

O Portal SI reúne notícias e eventos do curso de Sistemas de Informação. Este guia mostra como consultar as publicações e como o administrador mantém o conteúdo. A demonstração utiliza publicações fictícias.

1. Abra http://localhost/portal-si/ com o Apache e o PostgreSQL em execução.

2. Use Notícias ou Eventos no menu. No celular, abra Categorias para escolher a seção.

3. Abra Buscar para informar uma palavra ou data e clique em Filtrar. Limpar retorna à listagem completa.

4. Clique no título de uma publicação para ler o texto completo. Use a paginação para ver as demais publicações.


## Entrar e gerenciar conteúdos

Clique em Fazer login e informe o e-mail e a senha. O acesso fornecido em ACESSO_AVALIADOR.txt funciona no banco de demonstração restaurado. Contas comuns não acessam o painel.

1. No painel, abra Conteúdos. Todos, Notícias e Eventos filtram a listagem.

2. Clique em Novo conteúdo para cadastrar, Editar para alterar ou Excluir para abrir a confirmação.

3. A data e a autoria acompanham o registro. A marca Destaque identifica as publicações destacadas.

4. Use Encerrar sessão quando terminar. Acesso ao painel sem sessão volta ao login.


## Cadastrar uma publicação

Preencha título, categoria e conteúdo. O resumo é opcional e ajuda a apresentar a publicação na listagem.

1. Escolha Rascunho para guardar sem exibir ou Publicado para disponibilizar no portal. Arquivado retira o item da área pública.

2. Selecione uma ou mais imagens do computador. Enviar e ver imagens mostra a prévia antes de salvar. A primeira imagem será a capa.

3. Aceitam-se JPG, PNG, WebP e GIF: até dez imagens por publicação, 2 MB por arquivo e 6 MB por envio. Se houver erro, revise a mensagem e selecione os arquivos novamente.

4. O link do YouTube é opcional. Marque Destaque quando necessário e clique em Salvar conteúdo.


## Editar e excluir publicações

Na edição, altere os campos necessários e clique em Salvar alterações. Os arquivos atuais são preservados quando nenhuma imagem é removida.

1. Para substituir imagens, marque as antigas para remoção e selecione as novas. Os arquivos gerenciados removidos deixam de ficar no disco ao salvar.

2. Ao excluir, confira o título e confirme. O registro e seus arquivos gerenciados são removidos; o painel não oferece desfazer.

3. O destaque marcado mais recentemente ocupa a manchete principal. Editar apenas o texto conserva a prioridade; desmarcar, salvar e marcar novamente muda a prioridade.

4. No detalhe público, use as miniaturas ou Anterior e Próxima. O grupo da galeria também permite trocar imagens pelas setas do teclado.


## Alterar a abertura da Home

No painel, abra Página inicial. É possível editar a chamada, o título, a apresentação, a foto, o texto alternativo, o enquadramento e o botão.

1. Preencha os textos e selecione uma foto do computador, se quiser substituir a atual.

2. Clique em Atualizar prévia para conferir a composição. A prévia não publica as alterações.

3. Escolha visualização de computador ou celular para revisar o espaço disponível.

4. Clique em Salvar e publicar para aplicar. Sem uma nova foto, a imagem atual é preservada.


### Não consigo abrir o portal

Confira se Apache e PostgreSQL estão em execução. No computador do avaliador, restaure o SQL e configure a conexão seguindo o README.


### Meu login foi recusado

Confira e-mail e senha. Contas comuns acessam a área pública; o painel exige perfil admin. O acesso do avaliador é específico da cópia restaurada.


### A publicação não apareceu

Confira se o status é Publicado. Veja a categoria, a paginação e os filtros ativos. Rascunhos e arquivados ficam fora do portal.


### A imagem foi recusada

Use um formato permitido e respeite os limites. Trocar a extensão de um arquivo não o transforma em imagem. Se necessário, reduza o arquivo e selecione-o novamente.


### As fotos sumiram depois de copiar o projeto

O SQL guarda caminhos. Copie também as pastas de uploads do pacote; o clone do Git sozinho não contém as fotos de demonstração.


### A prévia não mudou ou as alterações não foram publicadas

Use Atualizar prévia para conferir e Salvar e publicar para aplicar. A versão atual não atualiza a prévia somente pela digitação.


### A sessão expirou

Entre novamente e repita o preenchimento. O painel verifica inatividade e pode solicitar nova autenticação.