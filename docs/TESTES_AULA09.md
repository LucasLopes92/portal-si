# Validação da Aula 09

Data: 01/10/2026. Ambiente local: PHP 8.2 do XAMPP, PostgreSQL `portal_si`
e Microsoft Edge em modo headless.

- Os arquivos PHP novos ou alterados passaram em `php -l`.
- `GET /portal-si/` redireciona para `public/index.php`, a Home pública.
- `GET /portal-si/public/index.php` retornou HTTP 200, exibiu as seis categorias
  do banco e o estado vazio, pois a tabela `conteudos` está sem publicações.
- O filtro `?categoria=noticias` retornou HTTP 200; uma categoria inexistente
  retornou HTTP 404 com mensagem amigável.
- Um teste em transação inseriu uma publicação temporária e um rascunho.
  Confirmou o `INNER JOIN` com categoria e autor, a filtragem por categoria,
  a exclusão do rascunho e o escape HTML do título. A transação foi revertida.
  A Home sem sessão mostra o botão "Fazer login" e não oferece acesso ao painel
  entre os links de categorias públicas.
- A interface foi capturada em viewports de 1440px, 1024px, 768px e 390px.
  Em 390px, não houve rolagem horizontal; o menu de categorias abre em coluna
  por meio do elemento HTML `details`.
- Com 500px de rolagem, o menu permaneceu em `top: 0` nas larguras de 1440px,
  1024px e 390px. A linha da marca saiu da tela e não houve overflow horizontal.
- A busca no código de `public/` e `app/` não encontrou `<script>`, links
  `javascript:` ou arquivos `.js`.
- Um login HTTP real com conta temporária confirmou o redirecionamento para
  o painel administrativo. A logo ESUCRI e o botão "Ir para a Home" apontam
  para a Home; nela, a sessão permanece ativa e o botão "Painel" retorna ao
  dashboard protegido. A conta temporária foi removida.
