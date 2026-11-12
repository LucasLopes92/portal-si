# Verificação da galeria de publicações

Rodada em 11/11/2026, no PostgreSQL local e Apache do XAMPP, com Chrome
automatizado em 1366 × 900 e 390 × 844.

O teste utilizou uma conta administrativa e uma publicação temporárias,
removidas ao final. Os arquivos enviados no teste também foram excluídos.

Verificações concluídas:

- Login administrativo e abertura do formulário multipart.
- Seleção de duas imagens PNG e prévia no formulário.
- Persistência das referências no banco e primeira imagem como capa.
- Carregamento da capa na Home e das duas imagens pelo Apache.
- Cabeçalho `X-Content-Type-Options: nosniff` nos arquivos enviados.
- Navegação no detalhe pelos botões anterior/próxima, miniaturas e teclado.
- Layout em celular sem transbordamento horizontal da página.
- Remoção de uma imagem na edição, atualização da capa e exclusão do arquivo.
- Rejeição de arquivo PHP apresentado como JPG, preservando a publicação.
- Rejeição do décimo primeiro arquivo, preservando as imagens anteriores.
- Rejeição de arquivo acima de 2 MB, preservando as imagens anteriores.
- Exclusão da publicação e remoção do arquivo restante.
- Ausência de erros de JavaScript durante o fluxo.

Os 13 testes anteriores de `tests/painel_admin.php` também passaram após a
migração do banco. O upload valida MIME e dimensões no servidor; o campo
`accept` do navegador é apenas uma orientação na seleção.

Para conferir manualmente, crie uma publicação com duas imagens, salve como
publicada e abra seu detalhe. Navegue pelas imagens, depois edite a publicação
para adicionar ou remover arquivos. Confirme a alteração da capa ao remover
a primeira imagem.
